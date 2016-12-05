<?php
/**
 * Created by PhpStorm.
 * User: max
 * Date: 04/10/16
 * Time: 20:04
 */

namespace Mindy\Component\Application;

/**
 * @param bool $throw
 * @return App
 */
function app($throw = true)
{
    return App::getInstance($throw);
}
