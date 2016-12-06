<?php
/**
 * Created by PhpStorm.
 * User: max
 * Date: 10/10/2016
 * Time: 00:51
 */

namespace Mindy\Bundle\MindyBundle\Admin;

use Mindy\Bundle\MindyBundle\Form\MetaFormType;
use Mindy\Bundle\MindyBundle\Model\Meta;

class MetaAdmin extends AbstractModelAdmin
{
    public $columns = ['domain', 'url', 'title'];

    /**
     * @return string model class name
     */
    public function getModelClass()
    {
        return Meta::class;
    }

    public function getFormType()
    {
        return MetaFormType::class;
    }
}