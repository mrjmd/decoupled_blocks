<?php

namespace Drupal\pdb;

/**
 * Defines an interface for component library manager services.
 */
interface ComponentLibraryManagerInterface {

  /**
   * Build dynamic libraries for each available component.
   *
   * @return array
   *   The component libraries.
   */
  public function buildLibraryInfo();

}
