<?php

namespace Mindy\Orm;

use ReflectionClass;

/**
 * Class Model.
 */
class Model extends AbstractModel
{
    use LegacyMethodsTrait;

    /**
     * @return string
     */
    public static function tableName()
    {
        $bundleName = self::getBundleName();
        if (!empty($bundleName)) {
            $ns = (new ReflectionClass(get_called_class()))->getNamespaceName();
            $prefix = substr($ns, strpos($ns, 'Model') + 6);
            if ($prefix) {
                return sprintf('%s_%s_%s',
                    self::normalizeTableName(str_replace('Bundle', '', $bundleName)),
                    self::normalizeTableName($prefix),
                    parent::tableName()
                );
            } else {
                return sprintf('%s_%s',
                    self::normalizeTableName(str_replace('Bundle', '', $bundleName)),
                    parent::tableName()
                );
            }
        } else {
            return parent::tableName();
        }
    }

    /**
     * Return module name.
     *
     * @return string
     */
    public static function getBundleName()
    {
        $object = new ReflectionClass(get_called_class());
        $path = $object->getFileName();
        $basePath = substr($path, 0, strrpos(strtolower($path), 'bundle') + 7);
        $phpFiles = glob(sprintf('%s/*Bundle.php', $basePath));
        $bundleName = pathinfo(current($phpFiles), PATHINFO_FILENAME);

        return empty($bundleName) ? '' : $bundleName;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string) $this->getShortName();
    }
}
