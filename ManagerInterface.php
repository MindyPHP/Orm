<?php

/*
 * This file is part of Mindy Orm.
 * (c) 2017 Maxim Falaleev
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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
