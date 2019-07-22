<?php

namespace Drupal\pdb\Discovery;

use Drupal\Core\Extension\Discovery\RecursiveExtensionFilterIterator;

/**
 * {@inheritdoc}
 *
 * Extends to provide custom whitelist and blacklist.
 */
class PdbRecursiveExtensionFilterIterator extends RecursiveExtensionFilterIterator {

  /**
   * {@inheritdoc}
   */
  protected $whitelist = [
    'components',
    'src',
  ];

  /**
   * {@inheritdoc}
   */
  protected $blacklist = [
    // Object-oriented code subdirectories.
    'lib',
    'vendor',
    // Front-end.
    'assets',
    'css',
    'files',
    'images',
    'js',
    'misc',
    'templates',
    // Legacy subdirectories.
    'includes',
    // Test subdirectories.
    'fixtures',
    'Drupal',
  ];

}
