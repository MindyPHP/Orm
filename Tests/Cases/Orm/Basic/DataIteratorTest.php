<?php
/**
 *
 *
 * All rights reserved.
 *
 * @author Falaleev Maxim
 * @email max@studio107.ru
 * @version 1.0
 * @company Studio107
 * @site http://studio107.ru
 * @date 18/07/14.07.2014 17:10
 */

namespace Tests\Orm;

use ArrayAccess;
use ArrayIterator;
use IteratorAggregate;
use Mindy\Tests\DatabaseTestCase;
use Modules\Tests\Models\BookCategory;

class Cls implements IteratorAggregate, ArrayAccess
{
    private $_iterator;

    public function __construct(array $data)
    {
        $this->_data = $data;
    }

    public function getIterator()
    {
        if (!$this->_iterator) {
            $this->_iterator = new ArrayIterator($this->_data);
        }
        return $this->_iterator;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Whether a offset exists
     * @link http://php.net/manual/en/arrayaccess.offsetexists.php
     * @param mixed $offset <p>
     * An offset to check for.
     * </p>
     * @return boolean true on success or false on failure.
     * </p>
     * <p>
     * The return value will be casted to boolean if non-boolean was returned.
     */
    public function offsetExists($offset)
    {
        return $this->getIterator()->offsetExists($offset);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to retrieve
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     * @param mixed $offset <p>
     * The offset to retrieve.
     * </p>
     * @return mixed Can return all value types.
     */
    public function offsetGet($offset)
    {
        return $this->getIterator()->offsetGet($offset);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to set
     * @link http://php.net/manual/en/arrayaccess.offsetset.php
     * @param mixed $offset <p>
     * The offset to assign the value to.
     * </p>
     * @param mixed $value <p>
     * The value to set.
     * </p>
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        $this->getIterator()->offsetSet($offset, $value);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to unset
     * @link http://php.net/manual/en/arrayaccess.offsetunset.php
     * @param mixed $offset <p>
     * The offset to unset.
     * </p>
     * @return void
     */
    public function offsetUnset($offset)
    {
        $this->getIterator()->offsetUnset($offset);
    }
}

class DataIteratorTest extends DatabaseTestCase
{
    public function getModels()
    {
        return [new BookCategory];
    }

    public function testDataQuerySet()
    {
        foreach (range(1, 5) as $i) {
            $model = new BookCategory(['id' => $i]);
            $model->save();
        }

        $qs = BookCategory::objects()->filter(['id__gt' => 0]);
        $this->assertEquals(1, $qs[0]->pk);
        $this->assertEquals(2, $qs[1]->pk);

        $this->assertEquals(5, BookCategory::objects()->count());
        $qs = BookCategory::objects()->all();
        $this->assertEquals(5, count($qs));

        $qs = BookCategory::objects()->filter(['id__gt' => 0])->asArray();
        $this->assertEquals(5, $qs->count());
        foreach ($qs as $i => $model) {
            $this->assertEquals($i + 1, $model["id"]);
        }
        $this->assertEquals(5, $qs->count());
        foreach ($qs as $i => $model) {
            $this->assertEquals($i + 1, $model["id"]);
        }

        $qs = BookCategory::objects()->filter(['id__gt' => 0]);
        $this->assertEquals(5, $qs->count());
        foreach ($qs as $i => $model) {
            $this->assertEquals($i + 1, $model->pk);
        }
        $this->assertEquals(5, $qs->count());
        foreach ($qs as $i => $model) {
            $this->assertEquals($i + 1, $model->pk);
        }

        $qs = BookCategory::objects()->filter(['id__gt' => 0]);
        $this->assertEquals(1, $qs[0]->pk);
        $this->assertEquals(2, $qs[1]->pk);
    }

    public function testDataManager()
    {
        foreach (range(1, 5) as $i) {
            $model = new BookCategory(['id' => $i]);
            $model->save();
        }

        $this->assertEquals(5, BookCategory::objects()->count());
        $qs = BookCategory::objects()->all();
        $this->assertEquals(5, count($qs));

        // Test iterate manager
        $qs = BookCategory::objects();
        foreach ($qs as $i => $model) {
            $this->assertEquals($i + 1, $model->pk);
        }
        foreach ($qs as $i => $model) {
            $this->assertEquals($i + 1, $model->pk);
        }

        $qs = BookCategory::objects()->filter(['id__gt' => 0]);
        $this->assertEquals(1, $qs[0]->pk);
        $this->assertEquals(2, $qs[1]->pk);
    }

    public function testGetIterator()
    {
        $cls = new Cls([1, 2, 3]);
        $t = 1;
        foreach ($cls as $i) {
            $this->assertEquals($t, $i);
            $t++;
        }

        $this->assertEquals(1, $cls[0]);
        $this->assertEquals(2, $cls[1]);
        $this->assertEquals(3, $cls[2]);

        $this->assertFalse(isset($cls[3]));
    }
}
