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
 * @date 03/08/14.08.2014 18:33
 */

namespace Mindy\Component\Template;

class DefaultLibrary extends Library
{
    /**
     * @return array
     */
    public function getHelpers()
    {
        return [];
    }

    /**
     * @return array
     */
    public function getTags()
    {
        return [];
    }
}