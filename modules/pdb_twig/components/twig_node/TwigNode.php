<?php

namespace Drupal\pdb_twig\twig_node;

/**
 * Provides custom build steps for the twig-node twig block.
 */
class TwigNode {

  public static function build($build, $config) {
    // Related context node is available on the config.
    $node = $config['contexts']['entity:node'];
    $build['#title'] = $node->getTitle();

    return $build;
  }

}
