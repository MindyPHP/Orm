<?php
/**
 * Created by PhpStorm.
 * User: max
 * Date: 09/10/2016
 * Time: 21:59
 */

namespace Mindy\Component\Template;

/**
 * Interface VariableProviderInterface
 * @package Mindy\Component\Template
 */
interface VariableProviderInterface
{
    /**
     * @return array
     */
    public function getData();
}