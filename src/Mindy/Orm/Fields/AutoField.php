<?php

namespace Mindy\Orm\Fields;

use Mindy\Query\ConnectionManager;
use Mindy\Query\Expression;
use Mindy\Query\Pgsql\Schema;

/**
 * Class AutoField
 * @package Mindy\Orm
 */
class AutoField extends IntField
{
    public $primary = true;

    /**
     * @return null|string
     * @throws \Mindy\Query\Exception\UnknownDatabase
     */
    public function getDbPrepValue()
    {
        $db = ConnectionManager::getDb();
        if ($db->getSchema() instanceof Schema) {
            /*
             * Primary key всегда передается по логике Query, а для корректной работы pk в pgsql
             * необходимо передать curval($seq) или nextval($seq) или не экранированный DEFAULT.
             */
            return new Expression("DEFAULT");
        } else {
            return parent::getDbPrepValue();
        }
    }
}
