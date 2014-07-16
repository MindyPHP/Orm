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
 * @date 15/07/14.07.2014 17:39
 */

namespace Tests\Orm;

use Tests\DatabaseTestCase;
use Tests\Models\Book;
use Tests\Models\BookCategory;

class BackwardFieldsTest extends DatabaseTestCase
{
    public function getModels()
    {
        return [
            new Book,
            new BookCategory
        ];
    }

    public function testInitFields()
    {
        $book = new Book;
        $this->assertEquals(3, count($book->getFieldsInit()));

        $bookCategory = new BookCategory;
        $this->assertEquals(3, count($bookCategory->getFieldsInit()));
    }

    public function testSimple()
    {
        $category = new BookCategory();
        $category->save();

        $book = new Book([
            'category' => $category,
            'category_new' => $category,
        ]);
        $book->save();

        $this->assertEquals(1, Book::objects()->count());
        $this->assertEquals(1, BookCategory::objects()->count());
        $this->assertEquals(1, BookCategory::objects()->get()->category_set->count());
        $this->assertEquals(1, BookCategory::objects()->get()->categories->count());
    }
}

