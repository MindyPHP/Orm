<?php
/**
 *
 *
 * All rights reserved.
 *
 * @author Falaleev Maxim
 * @email max@studio107.ru
 * @version 1.0
 * @company Studio107
 * @site http://studio107.ru
 * @date 03/01/14.01.2014 21:52
 */

namespace Mindy\Orm;


use Mindy\Core\Interfaces\Arrayable;

/**
 * Class Orm
 * @package Mindy\Orm
 * @method static \Mindy\Orm\Manager objects($instance = null)
 */
class Orm extends Base implements Arrayable
{
    /**
     * @var string
     */
    public $fileField = '\Mindy\Orm\Fields\FileField';
    /**
     * @var string
     */
    public $autoField = '\Mindy\Orm\Fields\AutoField';
    /**
     * @var string
     */
    public $relatedField = '\Mindy\Orm\Fields\RelatedField';
    /**
     * @var string
     */
    public $foreignField = '\Mindy\Orm\Fields\ForeignField';
    /**
     * TODO
     * @var string
     */
    public $oneToOneField = '\Mindy\Orm\Fields\OneToOneField';
    /**
     * @var string
     */
    public $manyToManyField = '\Mindy\Orm\Fields\ManyToManyField';
    /**
     * @var string
     */
    public $hasManyField = '\Mindy\Orm\Fields\HasManyField';
}
