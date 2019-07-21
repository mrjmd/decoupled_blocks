<?php

namespace Drupal\pdb_twig\Plugin\Block;

use Drupal\pdb\Plugin\Block\PdbBlock;

/**
 * Exposes a Twig component as a block.
 *
 * @Block(
 *   id = "twig_component",
 *   admin_label = @Translation("Twig component"),
 *   deriver = "\Drupal\pdb_twig\Plugin\Derivative\TwigBlockDeriver"
 * )
 */
class TwigBlock extends PdbBlock {

  /**
   * Provides safe namespacing for pdb twig themes.
   */
  const THEME_PREFIX = 'pdb_twig_';

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = parent::build();

    $info = $this->getComponentInfo();
    $machine_name = $info['machine_name'];

    if (isset($info['class'])) {
      // This requires the class to be avialable under Psr-4.
      // This can be done by using composer.json autoload.
      // TODO: check if there is another way to support this.
      $class = $info['class'];
      $build = $class::build($build, $this->configuration);

      // Allow a block do not render by returning an empty array.
      if (empty($build)) {
        return [];
      }
    }

    if (isset($info['theme'])) {
      if (is_array($info['theme'])) {
        $theme = self::THEME_PREFIX . $info['theme']['template'];
      }
      else {
        // This adds support to use existing themes.
        $theme = $info['theme'];
      }

      $build['#theme'] = $theme;
    }

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function attachLibraries(array $component) {
    $parent_libraries = parent::attachLibraries($component);
    // This is required because parent is not adding the "library" property.
    // TODO: This might need to be fixed in the parent and its sub-modules.
    $framework_libraries = [];

    $libraries = array(
      'library' => array_merge($parent_libraries, $framework_libraries),
    );

    return $libraries;
  }

}
