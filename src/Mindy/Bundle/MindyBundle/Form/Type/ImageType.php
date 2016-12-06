<?php
/**
 * Created by PhpStorm.
 * User: max
 * Date: 22/11/2016
 * Time: 20:30
 */

namespace Mindy\Bundle\MindyBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Extends the File type, upload an image but show a version of the currently uploaded image.
 *
 * @example:
 *     $builder->add('image', 'image', array(
 *         'base_path' => '/var/www/images/',
 *         'base_uri' => 'http://example.com/images/',
 *         'no_image_placeholder_uri' => 'http://example.com/images/noimage.jpg',
 *     ));
 *
 * @author Benjamin Eberlei <eberlei@simplethings.de>
 */
class ImageType extends AbstractType
{
    private $webDir;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if (!isset($options['base_uri']) || !$options['base_uri']) {
            throw new \InvalidArgumentException("Base Uri has to be configured.");
        }
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        if (!isset($options['base_path']) || !$options['base_path']) {
            $options['base_path'] = $this->webDir . $options['base_uri'];
        }
        $data = $form->getData();
        if ($data) {
            /* @var $data SplFileInfo */
            if (strpos(realpath($data->getPath()), realpath($options['base_path'])) === 0) {
                $uri = str_replace(realpath($options['base_path']), $options['base_uri'], $data->getRealPath());
            } else {
                $uri = str_replace($options['base_path'], $options['base_uri'], $data->getPathname());
            }
            if ('/' !== DIRECTORY_SEPARATOR) {
                $uri = str_replace(DIRECTORY_SEPARATOR, '/', $uri);
            }
            $view->vars['image_uri'] = $uri;
        } else if ($uri = $options['no_image_placeholder_uri']) {
            $view->vars['attr']['image_uri'] = $uri;
        }
        $view->vars['image_alt'] = $options['image_alt'];
        $view->vars['image_height'] = $options['image_height'];
        $view->vars['image_width'] = $options['image_width'];
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'web_dir' => null,
            'base_path' => false,
            'base_uri' => '',
            'no_image_placeholder_uri' => '',
            'image_alt' => '',
            'image_width' => false,
            'image_height' => false,
            'type' => 'file',
        ]);
    }

    public function getParent()
    {
        return FileType::class;
    }

    public function getName()
    {
        return 'mindy_form_image';
    }
}