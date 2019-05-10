<?php

namespace Drupal\pdb_twig\twig_lazy_build;

/**
 * Provides custom build steps for thetwig-lazy-build twig block.
 */
class TwigLazyBuild {

  public static function build($build) {
    // Create the template variable to be lazy builded.
    $build['#lazy'] = [
      '#lazy_builder' => [static::class . '::lazyBuilder', []],
      '#create_placeholder' => TRUE,
    ];

    return $build;
  }

  /**
   * Lazy builder callback.
   *
   * This will be executed once "#lazy" variable is rendered in the template.
   */
  public static function lazyBuilder() {
    // Add some sleep time to make it slow.
    sleep(2);

    // Here is the slow processing required by this block.
    $build = [];
    $build['#markup'] = t('This was slow!!');

    return $build;
  }

}
