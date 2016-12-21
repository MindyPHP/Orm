<?php
/**
 * All rights reserved.
 *
 * @author Falaleev Maxim
 * @email max@studio107.ru
 *
 * @version 1.0
 * @company Studio107
 * @site http://studio107.ru
 * @date 04/01/14.01.2014 02:38
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
