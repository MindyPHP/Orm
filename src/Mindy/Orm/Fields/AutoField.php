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
 * @date 03/01/14.01.2014 22:01
 */

namespace Mindy\Orm\Fields;


use Mindy\Query\ConnectionManager;

class AutoField extends IntField
{
    public $primary = true;

    public $null = true;

    public function sql()
    {
        return trim(sprintf('%s %s', $this->sqlType(), $this->sqlDefault()));
    }

    public function sqlType()
    {
        return 'pk';
    }

    /**
     * TODO
     * @return null|string
     * @throws \Mindy\Query\Exception\UnknownDatabase
     */
    /*
    public function getDbPrepValue()
    {
        $db = ConnectionManager::getDb();
        $table = $this->getModel()->tableName();
        $rawTable = $db->schema->getRawTableName($table);
        return "nextval('$rawTable'::regclass)";
    }
    */
}
