<?php

namespace Drupal\pdb_twig\Plugin\Derivative;

use Drupal\pdb_twig\Plugin\Context\TwigContextDefinition;
use Drupal\pdb\Plugin\Derivative\PdbBlockDeriver;

/**
 * Derives block plugin definitions for Twig components.
 */
class TwigBlockDeriver extends PdbBlockDeriver {

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition) {
    $definitions = parent::getDerivativeDefinitions($base_plugin_definition);

    $twig_definitions = array_filter($definitions, function(array $definition) {
      $info = $definition['info'];
      // Only keep components that belong to twig presentation.
      $is_twig = $info['presentation'] === 'twig';

      // Only keep components that are not disabled.
      // This piece might need to be moved to parent deriver.
      $is_disabled = isset($info['module_status']) ? $info['module_status'] === 'disabled' : FALSE;

      return $is_twig && !$is_disabled;
    });

    // This works but Drupal is caching class namespaces with apcu and
    // it starts to get some problems for some cases like moving components.
    // $loader = \Drupal::service('class_loader');
    // Clearing the apcu could be a solution but does not seem performant.
    // apcu_clear_cache();

    // Some extra stuff for twig components.
    $components = $this->componentDiscovery->getComponents();
    foreach ($twig_definitions as $block_id => $definition) {
      $component = $components[$block_id];
      // Add some support to hide the block on the UI.
      // This piece might need to be moved to parent deriver.
      // The block will be created but not listed on the block layout UI.
      if ($component->info['module_status'] === 'hidden') {
        $twig_definitions[$block_id]['_block_ui_hidden'] = TRUE;

        // Create a non-existing context so the block will be hidden other UIs.
        if (empty($this->derivatives[$block_id]['context'])) {
          $twig_definitions[$block_id]['context'] = [];
        }
        $twig_definitions[$block_id]['context']['pdb_hidden'] = new TwigContextDefinition('pdb:hidden');
      }

      // Add the path to the component.
      // This might require to go to parent deriver.
      $component_path = $component->getPath();
      $twig_definitions[$block_id]['info']['path'] = $component_path;

      // Add the class to the Psr-4 namespaces.
      // $loader->addPsr4('Drupal\\' . $block_id . '\\', \Drupal::root() . '/' . $component_path, TRUE, TRUE);
    }

    return $twig_definitions;
  }

}
