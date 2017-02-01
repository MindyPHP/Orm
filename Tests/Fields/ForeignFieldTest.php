<?php

/*
 * (c) Studio107 <mail@studio107.ru> http://studio107.ru
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * Author: Maxim Falaleev <max@studio107.ru>
 */

namespace Mindy\Orm\Tests\Fields;

use Mindy\Orm\Tests\Models\Product;
use Mindy\Orm\Tests\OrmDatabaseTestCase;
use Mindy\QueryBuilder\QueryBuilder;

class ForeignFieldTest extends OrmDatabaseTestCase
{
    protected function getModels()
    {
        return [new Product()];
    }

    public function testForeignKey()
    {
        $c = $this->getConnection();
        $schemaManager = $c->getSchemaManager();
        $adapter = QueryBuilder::getInstance($c)->getAdapter();
        $tableName = $adapter->getRawTableName(Product::tableName());
        $columns = $schemaManager->listTableColumns($tableName);
        $this->assertArrayHasKey('id', $columns);
        $this->assertArrayHasKey('category_id', $columns);

        $model = new Product();
        $fk = $model->getField('category');
        $this->assertInstanceOf('\Mindy\Orm\Fields\ForeignField', $fk);
        $this->assertNull($model->category);
    }
}
