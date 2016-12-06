<?php

/**
 * User: max
 * Date: 24/07/15
 * Time: 16:23
 */

namespace Mindy\Orm\Files;

/**
 * Class ResourceFile
 * @package Mindy\Orm\Files
 */
class ResourceFile extends File
{
    public function __construct($content, $name = null)
    {
        $temp = tempnam(sys_get_temp_dir(), 'tmp');
        file_put_contents($temp, $content);

        parent::__construct($temp);
    }
}
