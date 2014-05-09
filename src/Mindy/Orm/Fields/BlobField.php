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
 * @date 09/05/14.05.2014 14:04
 */

namespace Mindy\Orm\Fields;


class BlobField extends Field
{
    public function sqlType()
    {
        return 'longblob';
    }
}

