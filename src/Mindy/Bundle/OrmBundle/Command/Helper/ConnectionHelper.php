<?php
/**
 * Created by PhpStorm.
 * User: max
 * Date: 10/10/2016
 * Time: 21:36
 */

namespace Mindy\Bundle\OrmBundle\Command\Helper;

use Symfony\Component\Console\Helper\Helper;

class ConnectionHelper extends Helper
{
    /**
     * Returns the canonical name of this helper.
     *
     * @return string The canonical name
     */
    public function getName()
    {
        return 'connection';
    }
}