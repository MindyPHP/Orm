<?php

/*
 * (c) Studio107 <mail@studio107.ru> http://studio107.ru
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * Author: Maxim Falaleev <max@studio107.ru>
 */

namespace Mindy\Orm\Tests\Basic;

use Mindy\Orm\Manager;
use Mindy\Orm\Model;
use Mindy\Orm\Tests\OrmDatabaseTestCase;

class DefaultManagerModel extends Model
{
}

class CustomManager extends Manager
{
    public function published()
    {
        // do something like this
        // $this->getQuerySet()->filter(['published' => 1]);
        return $this;
    }
}

class CustomManagerModel extends Model
{
    public static function objectsManager($instance = null)
    {
        $className = get_called_class();

        return new CustomManager($instance ? $instance : new $className());
    }
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
        $model = new CustomManagerModel();
        $this->assertInstanceOf(CustomManager::class, $model->objects());
        $this->assertInstanceOf(CustomManager::class, CustomManagerModel::objects());
    }

    public function testMethod()
    {
        $model = new CustomManagerModel();
        $this->assertInstanceOf(CustomManager::class, $model->objects()->published());
    }
}
