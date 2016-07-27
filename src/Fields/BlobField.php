<?php

namespace Mindy\Orm\Fields;
use Mindy\Base\Mindy;
use Mindy\Query\ConnectionManager;

/**
 * Class BlobField
 * @package Mindy\Orm
 */
class BlobField extends Field
{
    public function sqlType()
    {
        return $this->getModel()->getDb()->getDriverName() == 'pgsql' ? 'BYTEA' : 'longblob';
    }

    public function getDbPrepValue()
    {
        // TODO
        // if ($db->getDriverName() == 'sqlsrv' || $db->getDriverName() == 'mssql' || $db->getDriverName() == 'dblib')
        //     $select = 'CONVERT(VARCHAR(MAX), data)';
        return parent::getDbPrepValue();
    }
}

