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

    public function sql()
    {
        return trim(sprintf('%s %s', $this->sqlType(), $this->sqlDefault()));
    }

    public function sqlType()
    {
        return 'pk';
    }

    /**
     * @return null|string
     * @throws \Mindy\Query\Exception\UnknownDatabase
     */
    public function getDbPrepValue()
    {
        $db = ConnectionManager::getDb();
        if ($db->getSchema() instanceof \Mindy\Query\Pgsql\Schema) {
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
