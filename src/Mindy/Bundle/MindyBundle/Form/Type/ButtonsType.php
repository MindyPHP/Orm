<?php
/**
 * Created by PhpStorm.
 * User: max
 * Date: 30/11/2016
 * Time: 21:05
 */

namespace Mindy\Bundle\MindyBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ButtonsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('save', SubmitType::class, [
                'label' => 'Сохранить'
            ])
            ->add('save_and_continue', SubmitType::class, [
                'label' => 'Сохранить и продолжить редактирование'
            ])
            ->add('save_and_create', SubmitType::class, [
                'label' => 'Сохранить и создать'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'label' => false,
            'mapped' => false
        ]);
    }
}