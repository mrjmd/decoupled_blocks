<?php

namespace Drupal\pdb_twig\twig_node\TwigNode;

/**
 * Provides custom build steps for the twig-node twig block.
 */
class TwigNode {

  public static function build($build, $config) {
    // Related context node is available on the config.
    // This requires to make the context available on main PdbBlock class.
    $node = $config['contexts']['entity:node'];
    $build['#title'] = $node['title'][0]['value'];

    return $build;
  }

}
