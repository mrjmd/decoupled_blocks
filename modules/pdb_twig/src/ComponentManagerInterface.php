<?php

namespace Drupal\pdb_twig;

/**
 * Defines an interface for a component manager.
 */
interface ComponentManagerInterface {

  /**
   * Builds a twig component for rendering.
   *
   * @param string $component
   *   The component identifier.
   * @param array $config
   *   The component config.
   * @param bool $render_block
   *   Flag to wrap component content in block template or not.
   *
   * @return array
   *   The component build info.
   */
  public function build($component, array $config, $render_block);

}
