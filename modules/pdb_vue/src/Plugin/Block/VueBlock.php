<?php

namespace Drupal\pdb_vue\Plugin\Block;

use Drupal\pdb\Plugin\Block\PdbBlock;
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
class VueBlock extends PdbBlock {

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

    return $attached;
  }

  /**
   * {@inheritdoc}
   */
  public function attachLibraries(array $component) {
    $parent_libraries = parent::attachLibraries($component);

    $framework_libraries = array(
      'pdb_vue/vue',
    );

    $libraries = array(
      'library' => array_merge($parent_libraries, $framework_libraries),
    );

    return $libraries;
  }

}
