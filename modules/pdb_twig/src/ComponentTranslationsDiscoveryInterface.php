<?php

namespace Drupal\pdb_twig;

/**
 * Defines an interface for a discovery of component locale translations.
 */
interface ComponentTranslationsDiscoveryInterface {

  /**
   * Find all components providing translations.
   *
   * @return array
   *   The components providing translations.
   */
  public function getComponentTranslations();

}
