<?php
/**
 * Created by PhpStorm.
 * User: max
 * Date: 16/09/16
 * Time: 16:32.
 */

namespace Mindy\Orm;

use Doctrine\DBAL\Connection;

/**
 * Interface ModelInterface.
 *
 * @property int|string $pk
 *
 * @method static \Mindy\Orm\Manager|\Mindy\Orm\TreeManager objects($instance = null)
 */
interface ModelInterface
{
    /**
     * @return array
     */
    public function getAttributes();

    /**
     * @param null $instance
     *
     * @return Manager
     */
    public static function objectsManager($instance = null);

    /**
     * @param bool $value
     */
    public function setIsNewRecord($value);

    /**
     * @return bool
     */
    public function getIsNewRecord();

    /**
     * @return MetaData
     */
    public static function getMeta();

    /**
     * @return array
     */
    public static function getFields();

    /**
     * @return bool
     */
    public function isValid();

    /**
     * @param bool $asArray
     *
     * @return array|string
     */
    public static function getPrimaryKeyName($asArray = false);

    /**
     * @param array $fields
     *
     * @return bool
     */
    public function insert(array $fields = []);

    /**
     * @param array $fields
     *
     * @return bool
     */
    public function update(array $fields = []);

    /**
     * @param array $fields
     *
     * @return bool
     */
    public function save(array $fields = []);

    /**
     * @param array $row
     *
     * @return ModelInterface
     */
    public static function create(array $row);

    /**
     * @return string
     */
    public static function tableName();

    /**
     * @param array $attributes
     */
    public function setAttributes(array $attributes);

    /**
     * @param string $name
     * @param $value
     */
    public function setAttribute($name, $value);

    /**
     * @return Connection
     */
    public function getConnection();

    /**
     * @param Connection $connection
     */
    public function setConnection(Connection $connection);
}
