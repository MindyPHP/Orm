<?php

/*
 * This file is part of Mindy Orm.
 * (c) 2017 Maxim Falaleev
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mindy\Orm\Tests\Basic;

use Mindy\Orm\Manager;
use Mindy\Orm\Model;
use Mindy\Orm\Tests\Models\Custom;
use Mindy\Orm\Tests\Models\CustomManager;
use Mindy\Orm\Tests\OrmDatabaseTestCase;

class DefaultManagerModel extends Model
{
}

class ManagerTest extends OrmDatabaseTestCase
{
    public function testDefaultManager()
    {
        $model = new DefaultManagerModel();
        $this->assertInstanceOf(Manager::class, $model->objects());
        $this->assertInstanceOf(Manager::class, DefaultManagerModel::objects());
    }

    public function testCustomManager()
    {
        $model = new Custom();
        $this->assertInstanceOf(CustomManager::class, $model->objects());
        $this->assertInstanceOf(CustomManager::class, Custom::objects());
    }

    public function testMethod()
    {
        $model = new Custom();
        $this->assertInstanceOf(CustomManager::class, $model->objects()->published());
    }
}
