<?php
/**
 * Created by PhpStorm.
 * User: max
 * Date: 22/11/2016
 * Time: 20:39
 */

namespace Mindy\Bundle\MindyBundle\Form\Extension;

use Mindy\Bundle\MindyBundle\Form\DataTransformer\FileDataTransformer;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;

class FileExtension extends AbstractTypeExtension
{
    protected $fileDataTransformer;

    /**
     * FileExtension constructor.
     * @param FileDataTransformer $fileDataTransformer
     */
    public function __construct(FileDataTransformer $fileDataTransformer)
    {
        $this->fileDataTransformer = $fileDataTransformer;
    }

    /**
     * Returns the name of the type being extended.
     *
     * @return string The name of the type being extended
     */
    public function getExtendedType()
    {
        return FileType::class;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addModelTransformer($this->fileDataTransformer);
    }

    /**
     * Add the image_path option
     *
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefined([
            'media_path'
        ]);
    }

    /**
     * Pass the image URL to the view
     *
     * @param FormView $view
     * @param FormInterface $form
     * @param array $options
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        if (isset($options['media_path'])) {
            $parentData = $form->getParent()->getData();

            $fileUrl = null;
            if (null !== $parentData) {
                $accessor = PropertyAccess::createPropertyAccessor();
                $fileUrl = $accessor->getValue($parentData, $view->vars['name']);
            }

            // set an "image_url" variable that will be available when rendering this field
            $view->vars['file_url'] = $fileUrl;
        }
    }

}