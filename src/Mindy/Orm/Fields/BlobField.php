<?php

namespace Mindy\Orm\Fields;
use Mindy\Base\Mindy;

/**
 * Class BlobField
 * @package Mindy\Orm
 */
class BlobField extends Field
{
    public function sqlType()
    {
        $db = Mindy::app()->db->getDb();
        return $db->getDriverName() == 'pgsql' ? 'BYTEA' : 'longblob';
    }

    public function getDbPrepValue()
    {
        // TODO
        // if ($db->getDriverName() == 'sqlsrv' || $db->getDriverName() == 'mssql' || $db->getDriverName() == 'dblib')
        //     $select = 'CONVERT(VARCHAR(MAX), data)';
        return parent::getDbPrepValue();
    }
}

