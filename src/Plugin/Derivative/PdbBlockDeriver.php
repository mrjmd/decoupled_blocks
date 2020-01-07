<?php

namespace Drupal\pdb\Plugin\Derivative;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\Plugin\Context\ContextDefinition;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Drupal\pdb\ComponentDiscoveryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a deriver for pdb blocks.
 */
class PdbBlockDeriver extends DeriverBase implements ContainerDeriverInterface {

  /**
   * The component discovery service.
   *
   * @var \Drupal\pdb\ComponentDiscoveryInterface
   */
  protected $componentDiscovery;

  /**
   * PdbBlockDeriver constructor.
   *
   * @param \Drupal\pdb\ComponentDiscoveryInterface $component_discovery
   *   The component discovery service.
   */
  public function __construct(ComponentDiscoveryInterface $component_discovery) {
    $this->componentDiscovery = $component_discovery;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $base_plugin_id) {
    return new static(
      $container->get('pdb.component_discovery')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition) {
    // Get all custom blocks which should be rediscovered.
    $components = $this->componentDiscovery->getComponents();
    foreach ($components as $block_id => $block_info) {
      $this->derivatives[$block_id] = $base_plugin_definition;
      $this->derivatives[$block_id]['info'] = $block_info->info;
      $this->derivatives[$block_id]['admin_label'] = $block_info->info['name'];
      $this->derivatives[$block_id]['cache'] = array('max-age' => 0);

      // Only set category if package is set, defaults to provider if not.
      if (isset($block_info->info['package'])){
        $this->derivatives[$block_id]['category'] =$block_info->info['package'];
      }

      if (isset($block_info->info['contexts'])) {
        $this->derivatives[$block_id]['context'] = $this->createContexts($block_info->info['contexts']);
      }
    }
    return $this->derivatives;
  }

  /**
   * Creates the context definitions required by a block plugin.
   *
   * @param array $contexts
   *   Contexts as defined in component label.
   *
   * @return \Drupal\Core\Plugin\Context\ContextDefinition[]
   *   Array of context to be used by block module
   */
  protected function createContexts(array $contexts) {
    $contexts_definitions = [];

    // Support for old node entity context defintion.
    // "entity: node" should now be defined "entity: entity:node".
    if (isset($contexts['entity']) && $contexts['entity'] === 'node') {
      // For some reason even if context_id is "node" it must be set "entity".
      $contexts['entity'] = 'entity:node';
    }

    foreach ($contexts as $context_id => $context_type) {
      $contexts_definitions[$context_id] = new ContextDefinition($context_type);
    }

    return $contexts_definitions;
  }

}
