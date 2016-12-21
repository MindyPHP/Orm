<?php

namespace Mindy\Orm\Fields;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Platforms\PostgreSqlPlatform;
use Mindy\QueryBuilder\Expression;

/**
 * Class AutoField.
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
    public function getSqlOptions()
    {
        return [
            'autoincrement' => true,
            'length' => $this->length,
            'notnull' => true,
        ];
    }

    public function convertToDatabaseValueSQL($value, AbstractPlatform $platform)
    {
        if ($value === null && $platform instanceof PostgreSqlPlatform) {
            $value = new Expression('DEFAULT');
        }

        return parent::convertToDatabaseValueSQL($value, $platform);
    }
}
