<?php

namespace Drupal\pdb_vue\Plugin\Block;

use Drupal\pdb\Plugin\Block\PdbBlock;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\pdb_vue\Render\VueMarkup;

/**
 * Exposes a Vue component as a block.
 *
 * @Block(
 *   id = "vue_component",
 *   admin_label = @Translation("Vue component"),
 *   deriver = "\Drupal\pdb_vue\Plugin\Derivative\VueBlockDeriver"
 * )
 */
class VueBlock extends PdbBlock implements ContainerFactoryPluginInterface {

  /**
   * Stores the configuration factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Creates a VueBlock instance.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ConfigFactoryInterface $config_factory) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('config.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $info = $this->getComponentInfo();
    $machine_name = $info['machine_name'];
    $template = '';

    // Use raw HTML if a template is provided
    if (!empty($info['template'])) {
      $template = file_get_contents($info['path'] . '/' . $info['template']);
    }

    $build = parent::build();
    $build['#markup'] = VueMarkup::create('<' . $machine_name . ' class="' . $machine_name . '">' . $template . '</' . $machine_name . '>');

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function attachSettings(array $component) {
    $machine_name = $component['machine_name'];

    $attached = array();
    $attached['drupalSettings']['vue-apps'][$machine_name]['uri'] = '/' . $component['path'];

    $config_settings = $this->configFactory->get('pdb_vue.settings');
    if (isset($config_settings)) {
      $attached['drupalSettings']['vue-apps']['development_mode'] = $config_settings->get('development_mode');
    }
    else {
      $attached['drupalSettings']['vue-apps']['development_mode'] = FALSE;
    }

    return $attached;
  }

  /**
   * {@inheritdoc}
   */
  public function attachLibraries(array $component) {
    $parent_libraries = parent::attachLibraries($component);

    $libraries = array(
      'library' => $parent_libraries,
    );

    return $libraries;
  }

}
