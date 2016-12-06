<?php
/**
 * Created by PhpStorm.
 * User: max
 * Date: 30/09/16
 * Time: 14:20
 */

namespace Mindy\Orm\Validation;

use Symfony\Component\Validator\Constraint;

class Alphanumeric extends Constraint
{
    public $message = 'The string "%string%" contains an illegal character: it can only contain letters or numbers.';
}