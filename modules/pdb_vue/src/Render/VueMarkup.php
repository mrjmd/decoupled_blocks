<?php

namespace Drupal\pdb_vue\Render;

use Drupal\Component\Render\MarkupInterface;
use Drupal\Component\Render\MarkupTrait;

/**
 * Defines an object that passes safe strings through the render system.
 *
 * This object should only be constructed with a known safe string. If there is
 * any risk that the string contains user-entered data that has not been
 * filtered first, it must not be used.
 *
 * @see \Drupal\Core\Template\TwigExtension::escapeFilter
 * @see \Twig_Markup
 * @see \Drupal\Component\Utility\SafeMarkup
 */
final class VueMarkup implements MarkupInterface, \Countable {
  use MarkupTrait;
}
