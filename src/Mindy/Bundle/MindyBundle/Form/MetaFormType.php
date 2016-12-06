<?php
/**
 * Created by PhpStorm.
 * User: max
 * Date: 28/11/2016
 * Time: 23:43
 */

namespace Mindy\Bundle\MindyBundle\Form;

use Mindy\Bundle\MindyBundle\Model\Meta;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MetaFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('domain', TextType::class, [
                'label' => 'Хост'
            ])
            ->add('url', UrlType::class, [
                'label' => 'Адрес страницы'
            ])
            ->add('canonical', UrlType::class, [
                'label' => 'Абсолютный адрес (canonical)',
                'required' => false
            ])
            ->add('title', TextType::class, [
                'label' => 'Заголовок (title)'
            ])
            ->add('keywords', TextType::class, [
                'label' => 'Ключевые слова (keywords)'
            ])
            ->add('description', TextType::class, [
                'label' => 'Описание (description)'
            ]);

        if (!$options['inline']) {
            $builder
                ->add('submit', SubmitType::class, [
                    'label' => 'Сохранить'
                ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'inline' => false,
            'data_class' => Meta::class
        ]);
    }
}