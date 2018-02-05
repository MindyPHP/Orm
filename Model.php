<?php

declare(strict_types=1);

/*
 * Studio 107 (c) 2018 Maxim Falaleev
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
     * @throws \ReflectionException
     */
    public static function tableName()
    {
        $bundleName = self::getBundleName();
        if (!empty($bundleName)) {
            $ns = (new ReflectionClass(get_called_class()))->getNamespaceName();
            $prefix = substr($ns, strpos($ns, 'Model') + 6);
            if ($prefix) {
                return sprintf(
                    '%s_%s_%s',
                    self::normalizeTableName(str_replace('Bundle', '', $bundleName)),
                    self::normalizeTableName($prefix),
                    parent::tableName()
                );
            }

            return sprintf(
                '%s_%s',
                    self::normalizeTableName(str_replace('Bundle', '', $bundleName)),
                    parent::tableName()
                );
        }

        return parent::tableName();
    }

    /**
     * Return module name.
     *
     * @throws \ReflectionException
     *
     * @return string
     */
    public static function getBundleName()
    {
        $path = (new ReflectionClass(get_called_class()))->getFileName();
        $basePath = substr($path, 0, strrpos(strtolower($path), 'bundle') + 7);
        $files = glob(sprintf('%s/*Bundle.php', $basePath));
        $firstBundle = current($files);
        if ($firstBundle) {
            return pathinfo($firstBundle, PATHINFO_FILENAME);
        }

        return '';
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string) $this->getShortName();
    }
}
