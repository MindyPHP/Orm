<?php
/**
 * Created by PhpStorm.
 * User: max
 * Date: 20/09/16
 * Time: 13:01
 */

namespace Mindy\Orm\Validation;

use Symfony\Component\Validator\Constraints\File as BaseFile;

class File extends BaseFile
{
    public $required = false;

}