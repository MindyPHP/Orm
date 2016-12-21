<?php
/**
 * Created by PhpStorm.
 * User: max
 * Date: 16/09/16
 * Time: 19:14.
 */

namespace Mindy\Orm;

/**
 * Interface ManagerInterface.
 */
interface ManagerInterface extends QuerySetInterface
{
    /**
     * @return ModelInterface
     */
    public function getModel();

    /**
     * @return \Mindy\Orm\QuerySet
     */
    public function getQuerySet();
}
