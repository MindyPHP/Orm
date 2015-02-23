<?php

namespace Mindy\Orm\Fields;

/**
 * Class BlobField
 * @package Mindy\Orm
 */
class BlobField extends Field
{
    public function sqlType()
    {
        return 'longblob';
    }

    public function getDbPrepValue()
    {
        // TODO добавить в BlobField
        // if ($db->getDriverName() == 'sqlsrv' || $db->getDriverName() == 'mssql' || $db->getDriverName() == 'dblib')
        //     $select = 'CONVERT(VARCHAR(MAX), data)';
        return parent::getDbPrepValue();
    }
}

