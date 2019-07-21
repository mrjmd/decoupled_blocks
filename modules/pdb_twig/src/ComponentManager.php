<?php

namespace Drupal\pdb_twig;

use Drupal\Core\Block\BlockManagerInterface;
use Drupal\Core\Plugin\Context\Context;
use Symfony\Component\HttpFoundation\RequestStack;

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
   * The request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * The contexts required by plugins being built.
   *
   * @var array
   */
  protected $contexts = [];

  /**
   * Build a ComponentManager object.
   *
   * @param \Drupal\Core\Block\BlockManagerInterface $block_manager
   *   The block manager.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request stack.
   */
  public function __construct(BlockManagerInterface $block_manager, RequestStack $request_stack) {
    $this->blockManager = $block_manager;
    $this->requestStack = $request_stack;
  }

  /**
   * {@inheritdoc}
   */
  public function build($component, array $config = [], $render_block = TRUE) {
    $block_plugin = $this->blockManager->createInstance("twig_component:$component", $config);

    // Check and build block required contexts.
    $context_defs = $block_plugin->getContextDefinitions();
    if ($context_defs) {
      $request = $this->requestStack->getCurrentRequest();

      foreach ($context_defs as $context_key => $context_def) {
        // Do not process pdb:hidden context definitions.
        if ($context_def->getDataType() === 'pdb:hidden') {
          continue;
        }

        if (!isset($this->contexts[$context_key])) {
          $context_name = $context_def->getDataDefinition()->getEntityTypeId();
          if ($request->attributes->has($context_name)) {
            $value = $request->attributes->get($context_name);
          }
          $this->contexts[$context_key] = new Context($context_def, $value);
        }
      }
      \Drupal::service('context.handler')->applyContextMapping($block_plugin, $this->contexts);
    }

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
