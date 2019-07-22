<?php

namespace Drupal\pdb;

use Drupal\pdb\Discovery\PdbRecursiveExtensionFilterIterator;
use Drupal\pdb\Event\PdbDiscoveryPathEvent;

use Drupal\Core\Extension\ExtensionDiscovery;
use Drupal\Core\Extension\InfoParserInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Extension\Extension;
use Drupal\Core\Site\Settings;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Discovery service for front-end components provided by modules and themes.
 *
 * Components (anything whose info file 'type' is 'pdb') are treated as Drupal
 * extensions unto themselves.
 */
class ComponentDiscovery extends ExtensionDiscovery implements ComponentDiscoveryInterface {

  /**
   * The event dispatcher.
   *
   * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
   */
  protected $eventDispatcher;

  /**
   * The info parser.
   *
   * @var \Drupal\Core\Extension\InfoParserInterface
   */
  protected $infoParser;

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * Flag to indicate if the discovery is global or fixed to given dirs.
   *
   * @var boolean
   */
  protected $globalDiscovery = false;

  /**
   * ComponentDiscovery constructor.
   *
   * @param string $root
   *   The root directory of the Drupal installation.
   * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $event_dispatcher
   *   The event dispatcher.
   * @param \Drupal\Core\Extension\InfoParserInterface $info_parser
   *   The info parser.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   */
  public function __construct(
    $root,
    EventDispatcherInterface $event_dispatcher,
    InfoParserInterface $info_parser,
    ModuleHandlerInterface $module_handler
  ) {
    parent::__construct($root);
    $this->eventDispatcher = $event_dispatcher;
    $this->infoParser = $info_parser;
    $this->moduleHandler = $module_handler;
  }

  /**
   * {@inheritdoc}
   */
  public function getComponents() {
    // Find components.
    $components = $this->scan('pdb');

    // Set defaults for module info.
    $defaults = array(
      'dependencies' => array(),
      'description' => '',
      'package' => 'Other',
      'version' => NULL,
    );

    // Read info files for each module.
    foreach ($components as $key => $component) {
      // Look for the info file.
      $component->info = $this->infoParser->parse($component->getPathname());
      // Merge in defaults and save.
      $components[$key]->info = $component->info + $defaults;
    }
    $this->moduleHandler->alter('component_info', $components);

    return $components;
  }

  /**
   * {@inheritdoc}
   *
   * Extends to provide user defined paths to look for components.
   *
   * Copied from Drupal\Core\Extension\ExtensionDiscovery\ExtensionDiscovery.
   */
  public function scan($type, $include_tests = NULL) {
    // Try to get discovery path from settings.php.
    $discovery_path = Settings::get('pdb_discovery_path', []);
    if (is_string($discovery_path)) {
      $discovery_path = [$discovery_path];
    }

    // Try to get discovery path from subscribers.
    // Pass the discovery path from settings if any.
    $event = new PdbDiscoveryPathEvent($discovery_path);
    $this->eventDispatcher->dispatch(PdbDiscoveryPathEvent::DISCOVERY_PATH, $event);

    // Get the updated dicovery path from subscribers.
    $discovery_path = $event->getPath();

    // If user is not defining any custom path, then do a global discovery
    // by following parent's approach.
    if (empty($discovery_path)) {
      $this->globalDiscovery = true;
      return parent::scan($type, $include_tests);
    }

    // Use the user defined paths for the search.
    $search_dirs = $discovery_path;

    // Copied from parent::scan().
    $files = [];
    foreach ($search_dirs as $dir) {
      // Discover all extensions in the directory, unless we did already.
      if (!isset(static::$files[$this->root][$dir][$include_tests])) {
        static::$files[$this->root][$dir][$include_tests] = $this->scanDirectory($dir, $include_tests);
      }
      // Only return extensions of the requested type.
      if (isset(static::$files[$this->root][$dir][$include_tests][$type])) {
        $files += static::$files[$this->root][$dir][$include_tests][$type];
      }
    }

    // If applicable, filter out extensions that do not belong to the current
    // installation profiles.
    $files = $this->filterByProfileDirectories($files);
    // Sort the discovered extensions by their originating directories.
    $origin_weights = array_flip($search_dirs);
    $files = $this->sort($files, $origin_weights);

    // Process and return the list of extensions keyed by extension name.
    return $this->process($files);
  }

  /**
   * {@inheritdoc}
   *
   * This is mostly extended to be able to use a different
   * RecursiveExtensionFilterIterator class when searching custom user dirs.
   *
   * Copied from Drupal\Core\Extension\ExtensionDiscovery\ExtensionDiscovery.
   */
  protected function scanDirectory($dir, $include_tests) {
    // If it is a global discovery, then follow parent's approach.
    if ($this->globalDiscovery) {
      return parent::scanDirectory($dir, $include_tests);
    }

    // Copied from parent::scanDirectory() with the exception of line
    // 187 that makes use of own extension filter iterator.

    $files = [];

    // In order to scan top-level directories, absolute directory paths have to
    // be used (which also improves performance, since any configured PHP
    // include_paths will not be consulted). Retain the relative originating
    // directory being scanned, so relative paths can be reconstructed below
    // (all paths are expected to be relative to $this->root).
    $dir_prefix = ($dir == '' ? '' : "$dir/");
    $absolute_dir = ($dir == '' ? $this->root : $this->root . "/$dir");

    if (!is_dir($absolute_dir)) {
      return $files;
    }
    // Use Unix paths regardless of platform, skip dot directories, follow
    // symlinks (to allow extensions to be linked from elsewhere), and return
    // the RecursiveDirectoryIterator instance to have access to getSubPath(),
    // since SplFileInfo does not support relative paths.
    $flags = \FilesystemIterator::UNIX_PATHS;
    $flags |= \FilesystemIterator::SKIP_DOTS;
    $flags |= \FilesystemIterator::FOLLOW_SYMLINKS;
    $flags |= \FilesystemIterator::CURRENT_AS_SELF;
    $directory_iterator = new \RecursiveDirectoryIterator($absolute_dir, $flags);

    // Allow directories specified in settings.php to be ignored. You can use
    // this to not check for files in common special-purpose directories. For
    // example, node_modules and bower_components. Ignoring irrelevant
    // directories is a performance boost.
    $ignore_directories = Settings::get('file_scan_ignore_directories', []);

    // Filter the recursive scan to discover extensions only.
    // Important: Without a RecursiveFilterIterator, RecursiveDirectoryIterator
    // would recurse into the entire filesystem directory tree without any kind
    // of limitations.
    $filter = new PdbRecursiveExtensionFilterIterator($directory_iterator, $ignore_directories);
    $filter->acceptTests($include_tests);

    // The actual recursive filesystem scan is only invoked by instantiating the
    // RecursiveIteratorIterator.
    $iterator = new \RecursiveIteratorIterator($filter,
      \RecursiveIteratorIterator::LEAVES_ONLY,
      // Suppress filesystem errors in case a directory cannot be accessed.
      \RecursiveIteratorIterator::CATCH_GET_CHILD
    );

    foreach ($iterator as $key => $fileinfo) {
      // All extension names in Drupal have to be valid PHP function names due
      // to the module hook architecture.
      if (!preg_match(static::PHP_FUNCTION_PATTERN, $fileinfo->getBasename('.info.yml'))) {
        continue;
      }

      if ($this->fileCache && $cached_extension = $this->fileCache->get($fileinfo->getPathName())) {
        $files[$cached_extension->getType()][$key] = $cached_extension;
        continue;
      }

      // Determine extension type from info file.
      $type = FALSE;
      $file = $fileinfo->openFile('r');
      while (!$type && !$file->eof()) {
        preg_match('@^type:\s*(\'|")?(\w+)\1?\s*$@', $file->fgets(), $matches);
        if (isset($matches[2])) {
          $type = $matches[2];
        }
      }
      if (empty($type)) {
        continue;
      }
      $name = $fileinfo->getBasename('.info.yml');
      $pathname = $dir_prefix . $fileinfo->getSubPathname();

      // Determine whether the extension has a main extension file.
      // For theme engines, the file extension is .engine.
      if ($type == 'theme_engine') {
        $filename = $name . '.engine';
      }
      // For profiles/modules/themes, it is the extension type.
      else {
        $filename = $name . '.' . $type;
      }
      if (!file_exists($this->root . '/' . dirname($pathname) . '/' . $filename)) {
        $filename = NULL;
      }

      $extension = new Extension($this->root, $type, $pathname, $filename);

      // Track the originating directory for sorting purposes.
      $extension->subpath = $fileinfo->getSubPath();
      $extension->origin = $dir;

      $files[$type][$key] = $extension;

      if ($this->fileCache) {
        $this->fileCache->set($fileinfo->getPathName(), $extension);
      }
    }
    return $files;
  }

}
