<?php

namespace Drupal\pdb;

use Drupal\Core\Extension\ModuleHandlerInterface;

/**
 * Library manager service for component library related tasks.
 */
class ComponentLibraryManager implements ComponentLibraryManagerInterface {

  /**
   * The component discovery service.
   *
   * @var \Drupal\pdb\ComponentDiscoveryInterface
   */
  protected $componentDiscovery;

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * Creates a new ComponentLibraryManager object.
   *
   * @param \Drupal\pdb\ComponentDiscoveryInterface $component_discovery
   *   The component discovery service.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   */
  public function __construct(ComponentDiscoveryInterface $component_discovery, ModuleHandlerInterface $module_handler) {
    $this->componentDiscovery = $component_discovery;
    $this->moduleHandler = $module_handler;
  }

  /**
   * {@inheritdoc}
   */
  public function buildLibraryInfo() {
    $libraries = [];

    $components = $this->componentDiscovery->getComponents();
    foreach ($components as $component) {
      $info = $component->info;
      $path = $component->getPath();

      $library = [
        'header' => [],
        'footer' => [],
      ];

      if (isset($info['add_css'])) {
        // Build the css assets, grouping them by header and footer.
        $css_assets = $this->buildLibraryCss($info, $path);
        if (!empty($css_assets)) {
          if (!empty($css_assets['header'])) {
            $library['header'] += $css_assets['header'];
          }

          if (!empty($css_assets['footer'])) {
            $library['footer'] += $css_assets['footer'];
          }
        }
      }

      if (isset($info['add_js'])) {
        // Build the js assets, grouping them by header and footer.
        $js_assets = $this->buildLibraryJs($info, $path);
        if (!empty($js_assets)) {
          if (!empty($js_assets['header'])) {
            $library['header'] += $js_assets['header'];
          }

          if (!empty($js_assets['footer'])) {
            $library['footer'] += $js_assets['footer'];
          }
        }
      }

      // Allow other modules to alter the library.
      // TOOO: Replace this with a event dispatch.
      $this->moduleHandler->alter('component_library_build', $library, $component);

      // Build a library to include assets in header.
      if (!empty($library['header'])) {
        $library['header']['header'] = TRUE;

        $libraries += [$info['machine_name'] . '/header' => $library['header']];
      }

      // Build a library to include assets in footer.
      if (!empty($library['footer'])) {
        $libraries += [$info['machine_name'] . '/footer' => $library['footer']];
      }
    }

    return $libraries;
  }

  /**
   * Helper function to process and build library css assets.
   */
  protected function buildLibraryCss($info, $path) {
    $css_assets = [];

    if (isset($info['add_css']['header'])) {
      // Supports current simplest method to add css assets to the library.
      if (!isset($info['add_css']['header']['css'])) {
        // This assumes add_css -> header contains the assets.
        $info['add_css']['header'] = ['css' => $info['add_css']['header']];
      }

      foreach ($info['add_css']['header']['css'] as $group => $css) {
        $header_css = $this->libraryGetAssets($css, $path, $group);
        $info['add_css']['header']['css'] = $header_css;
        $css_assets['header'] = $info['add_css']['header'];
      }
    }

    if (isset($info['add_css']['footer'])) {
      if (!isset($info['add_css']['footer']['css'])) {
        // This assumes add_css -> footer contains the assets.
        $info['add_css']['footer'] = ['css' => $info['add_css']['footer']];
      }

      foreach ($info['add_css']['footer']['css'] as $group => $css) {
        $footer_css = $this->libraryGetAssets($css, $path, $group);
        $info['add_css']['footer']['css'] = $footer_css;
        $css_assets['footer'] = $info['add_css']['footer'];
      }
    }

    return $css_assets;
  }

  /**
   * Helper function to process and build library js assets.
   */
  protected function buildLibraryJs($info, $path) {
    $js_assets = [];

    if (isset($info['add_js']['header'])) {
      // Supports current simplest method to add js assets to the library.
      if (!isset($info['add_js']['header']['js'])) {
        // This assumes add_js -> header contains the assets.
        $info['add_js']['header'] = ['js' => $info['add_js']['header']];
      }

      $header_js = $this->libraryGetAssets($info['add_js']['header']['js'], $path);
      $info['add_js']['header']['js'] = $header_js;
      $js_assets['header'] = $info['add_js']['header'];
    }

    if (!empty($info['add_js']['footer'])) {
      if (!isset($info['add_js']['footer']['js'])) {
        // This assumes add_js -> footer contains the assets.
        $info['add_js']['footer'] = ['js' => $info['add_js']['footer']];
      }

      $footer_js = $this->libraryGetAssets($info['add_js']['footer']['js'], $path);
      $info['add_js']['footer']['js'] = $footer_js;
      $js_assets['footer'] = $info['add_js']['footer'];
    }
    return $js_assets;
  }

  /**
   * Helper function to process and build library assets.
   */
  protected function libraryGetAssets($assets, $path, $group = FALSE) {
    $processed = [];
    foreach ($assets as $asset_file => $asset_data) {
      // Allow external assets to use absolute path.
      if (!empty($asset_data['type']) && $asset_data['type'] == 'external') {
        $asset_path = $asset_file;
      }
      else {
        $asset_path = '/' . $path . '/' . $asset_file;
      }

      $processed[$asset_path] = $asset_data;
    }

    // Add a group parent if there is one.
    if ($group) {
      $processed = [$group => $processed];
    }
    return $processed;
  }

}
