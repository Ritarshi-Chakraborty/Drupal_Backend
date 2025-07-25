<?php

namespace Drupal\custom_entity\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Validates the price field of the "Movie" entity type.
 *
 * @Constraint(
 *   id = "PriceConstraint",
 *   label = @Translation("Price Constraint", context = "Validation"),
 * )
 */
class PriceFieldConstraint extends Constraint {
  /**
   * Variable which stores the error message.
   *
   * @var string
   */
  public $message = 'Price must be atleast 100.';

}
