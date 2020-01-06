<?php

namespace Drupal\pdb\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * Provides an event to handle user custom discovery paths.
 */
class PdbDiscoveryPathEvent extends Event {

  /**
   * Name of the PDB discovery path event.
   */
  const DISCOVERY_PATH = 'pdb.discovery_path';

  /**
   * Component discovery paths.
   *
   * @var array
   */
  protected $path;

  /**
   * Constructs a discovery path event object.
   *
   * @param array $path
   *   The discovery path.
   */
  public function __construct(array $path) {
    $this->path = $path;
  }

  /**
   * Gets the path.
   *
   * @return array
   *   The stored discovery path.
   */
  public function getPath() {
    return $this->path;
  }

  /**
   * Sets the path.
   *
   * @param array $path
   *   Discovery path to store.
   */
  public function setPath(array $path) {
    return $this->path = $path;
  }

}
