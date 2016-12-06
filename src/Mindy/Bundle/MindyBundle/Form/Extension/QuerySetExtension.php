<?php
/**
 * Created by PhpStorm.
 * User: max
 * Date: 22/11/2016
 * Time: 20:39
 */

namespace Mindy\Bundle\MindyBundle\Form\Extension;

use Mindy\Bundle\MindyBundle\Form\DataTransformer\QuerySetTransformer;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;

class QuerySetExtension extends AbstractTypeExtension
{
    /**
     * Returns the name of the type being extended.
     *
     * @return string The name of the type being extended
     */
    public function getExtendedType()
    {
        return ChoiceType::class;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($options['multiple']) {
            $builder->addModelTransformer(new QuerySetTransformer());
        }
    }
}