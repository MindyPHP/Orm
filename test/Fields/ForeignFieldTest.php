<?php
/**
 * Created by PhpStorm.
 * User: max
 * Date: 27/07/16
 * Time: 16:45
 */

namespace Mindy\Orm\Tests\Fields;

use Mindy\Orm\Tests\OrmDatabaseTestCase;
use Modules\Tests\Models\Product;

class ForeignFieldTest extends OrmDatabaseTestCase
{
    protected function getModels()
    {
        return [new Product];
    }

    public function testForeignKey()
    {
        $c = $this->getConnection();
        $schema = $c->getTableSchema(Product::tableName(), true);
        $this->assertArrayHasKey('id', $schema->columns);
        $this->assertArrayHasKey('category_id', $schema->columns);

        $model = new Product();
        $fk = $model->getField("category");
        $this->assertInstanceOf('\Mindy\Orm\Fields\ForeignField', $fk);
        $this->assertNull($model->category);
    }
}