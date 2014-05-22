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


use Exception;
use Mindy\Core\Interfaces\Arrayable;
use Mindy\Helper\Creator;
use Mindy\Helper\Json;
use Mindy\Query\Connection;

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

    /**
     * TODO move to manager
     * Creates an active record object using a row of data.
     * This method is called by [[ActiveQuery]] to populate the query results
     * into Active Records. It is not meant to be used to create new records.
     * @param array $row attribute values (name => value)
     * @return \Mindy\Orm\Model the newly created active record.
     */
    public static function create($row)
    {
        $className = self::className();
        $record = new $className;
        $record->setAttributes($row);
        $record->setOldAttributes($row);
        return $record;
    }
}
