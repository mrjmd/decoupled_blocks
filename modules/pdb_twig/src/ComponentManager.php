<?php

namespace Drupal\pdb_twig;

use Drupal\Core\Block\BlockManagerInterface;

/**
 * Provides a service with support for twig components.
 */
class ComponentManager implements ComponentManagerInterface {

  /**
   * The block manager.
   *
   * @var \Drupal\Core\Block\BlockManagerInterface
   */
  protected $blockManager;

  /**
   * Build a ComponentManager object.
   *
   * @param \Drupal\Core\Block\BlockManagerInterface $block_manager
   *   The block manager.
   */
  public function __construct(BlockManagerInterface $block_manager) {
    $this->blockManager = $block_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function build($component, array $config = [], $render_block = TRUE) {
    $block_plugin = $this->blockManager->createInstance("twig_component:$component", $config);
    $block_content = $block_plugin->build();

    if ($render_block) {
      // Always render with the base template, full is tricky to support here.
      $block_build = [
        '#theme' => 'twig_block',
        '#id' => str_replace('_', '', $block_plugin->getDerivativeId()),
        '#derivative_plugin_id' => $block_plugin->getDerivativeId(),
        'content' => $block_content,
      ];
    }
    else {
      $block_build = $block_content;
    }

    return $block_build;
  }

}
