<?php
/**
 * Created by PhpStorm.
 * User: max
 * Date: 13/11/2016
 * Time: 22:32
 */

namespace Mindy\Bundle\MindyBundle\Admin;

use Symfony\Component\HttpKernel\Bundle\BundleInterface;

trait BundleAwareTrait
{
    /**
     * @var BundleInterface
     */
    protected $bundle;

    /**
     * @param BundleInterface $bundle
     */
    public function setBundle(BundleInterface $bundle)
    {
        $this->bundle = $bundle;
    }
}