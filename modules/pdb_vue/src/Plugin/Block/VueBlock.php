<?php

namespace Drupal\pdb_vue\Plugin\Block;

use Drupal\pdb\Plugin\Block\PdbBlock;

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

    $build = parent::build();

    // User raw HTML if a template is provided
    if (!empty($info['template'])) {
      $template = file_get_contents($info['path'] . '/' . $info['template']);

      $build['#type'] = 'inline_template';
      $build['#template'] = '{% raw %}<div class="' . $machine_name . '">' . $template . '</div>{% endraw %}';
    }
    else {
      $build['#allowed_tags'] = array($machine_name);
      $build['#markup'] = '<' . $machine_name . ' class="' . $machine_name . '"></' . $machine_name . '>';
    }

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
      'pdb_vue/components',
    );

    $libraries = array(
      'library' => array_merge($parent_libraries, $framework_libraries),
    );

    return $libraries;
  }

}
