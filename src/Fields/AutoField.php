<?php

namespace Mindy\Orm\Fields;

use Mindy\Query\ConnectionManager;
use Mindy\Query\Expression;

/**
 * Class AutoField
 * @package Mindy\Orm
 */
class AutoField extends IntField
{
    public $primary = true;

    /**
     * @return null|string
     */
    public function getDbPrepValue()
    {
        $db = $this->getModel()->getDb();
        if ($db->driverName == 'pgsql') {
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
}
