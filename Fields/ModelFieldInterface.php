<?php
/**
 * Created by PhpStorm.
 * User: max
 * Date: 16/09/16
 * Time: 15:15.
 */

namespace Mindy\Orm\Fields;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Mindy\Orm\ModelInterface;

/**
 * Interface ModelFieldInterface.
 */
interface ModelFieldInterface
{
    /**
     * @param string $name
     */
    public function setName($name);

    /**
     * @param ModelInterface $model
     */
    public function setModel(ModelInterface $model);

    /**
     * @param $value
     */
    public function setValue($value);

    /**
     * @return mixed
     */
    public function getValue();

    /**
     * @return string|null|bool
     */
    public function getVerboseName();

    /**
     * @return \Doctrine\Dbal\Types\Type|false|null
     */
    public function getSqlType();

    /**
     * @return array|null
     */
    public function getSqlOptions();

    /**
     * @return \Doctrine\Dbal\Schema\Column|null|false
     */
    public function getColumn();

    /**
     * @return \Doctrine\Dbal\Schema\Index[]|array
     */
    public function getSqlIndexes();

    /**
     * @return string
     */
    public function getAttributeName();

    /**
     * @param $value
     * @param AbstractPlatform $platform
     *
     * @return mixed
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform);

    /**
     * @param $value
     * @param AbstractPlatform $platform
     *
     * @return mixed
     */
    public function convertToPHPValue($value, AbstractPlatform $platform);

    /**
     * @param $value
     * @param AbstractPlatform $platform
     *
     * @return mixed
     */
    public function convertToPHPValueSQL($value, AbstractPlatform $platform);

    /**
     * @param $value
     * @param AbstractPlatform $platform
     *
     * @return mixed
     */
    public function convertToDatabaseValueSQL($value, AbstractPlatform $platform);

    /**
     * internal event.
     *
     * @param ModelInterface $model
     * @param $value
     *
     * @return
     */
    public function afterInsert(ModelInterface $model, $value);

    /**
     * internal event.
     *
     * @param ModelInterface $model
     * @param $value
     *
     * @return
     */
    public function afterUpdate(ModelInterface $model, $value);

    /**
     * internal event.
     *
     * @param ModelInterface $model
     *
     * @return
     */
    public function afterDelete(ModelInterface $model, $value);

    /**
     * internal event.
     *
     * @param ModelInterface $model
     * @param $value
     *
     * @return
     */
    public function beforeInsert(ModelInterface $model, $value);

    /**
     * internal event.
     *
     * @param ModelInterface $model
     * @param $value
     *
     * @return
     */
    public function beforeUpdate(ModelInterface $model, $value);

    /**
     * internal event.
     *
     * @param ModelInterface $model
     * @param $value
     *
     * @return
     */
    public function beforeDelete(ModelInterface $model, $value);
}
