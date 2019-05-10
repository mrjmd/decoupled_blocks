<?php

namespace Drupal\pdb_twig\Plugin\Context;

use Drupal\Core\Plugin\Context\ContextDefinition;
use Drupal\Core\TypedData\DataDefinition;

/**
 * Defines a class for a non-existing context definition.
 *
 * This context definition allows to hide twig components from UI.
 * This might require to be controlled on PdbBlock level.
 */
class TwigContextDefinition extends ContextDefinition {

  /**
   * {@inheritdoc}
   */
  public function getDataDefinition() {
    // Some minimum data definition required by the consumer of the definition.
    return DataDefinition::create('string');
  }

}
