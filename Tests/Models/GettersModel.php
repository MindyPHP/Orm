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
 * @date 04/01/14.01.2014 00:23
 */

namespace Tests\Models;


use Mindy\Db\Fields\CharField;
use Mindy\Db\Model;

class GettersModel extends Model
{
    /**
     * @var string
     */
    public $test = 'test';

    public function getFields()
    {
        return [
            'name' => new CharField(['null' => false, 'default' => 'example'])
        ];
    }
}
