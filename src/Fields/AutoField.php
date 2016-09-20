<?php

namespace Mindy\Orm\Fields;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use Mindy\QueryBuilder\Expression;

/**
 * Class AutoField
 * @package Mindy\Orm
 */
class AutoField extends BigIntField
{
    /**
     * @var bool
     */
    public $primary = true;
    /**
     * @var bool
     */
    public $unsigned = true;

    /**
     * @return array
     */
    public function getSqlOptions() : array
    {
        return [
            'autoincrement' => true,
            'length' => $this->length,
            'notnull' => true
        ];
    }

    /*
    public function getDbPrepValue()
    {
        $db = $this->getModel()->getConnection();
        if ($db->getDriver()->getName() == 'pdo_pgsql') {
            // Primary key всегда передается по логике Query, а для корректной работы pk в pgsql
            // необходимо передать curval($seq) или nextval($seq) или не экранированный DEFAULT.
            //
//            $sequenceName = $db->getSchema()->getTableSchema($this->getModel()->tableName())->sequenceName;
//            return new Expression("nextval('" . $sequenceName . "')");

            return new Expression("DEFAULT");
        } else {
            return parent::getDbPrepValue();
        }
    }
    */

    public function convertToDatabaseValueSQL($value, AbstractPlatform $platform)
    {
        if ($value === null && $this->getModel()->getConnection()->getDriver()->getName() === 'pdo_pgsql') {
            $value = new Expression('DEFAULT');
        }
        return parent::convertToDatabaseValueSQL($value, $platform);
    }
}
