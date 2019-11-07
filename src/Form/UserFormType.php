<?php

namespace App\Form;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;


class UserFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('firstName', TextType::class)
            ->add('lastName', TextType::class)
            ->add('city', TextType::class)
            ->add('postalCode', TextType::class, [
                'required'   => false,
            ])
            ->add('township', TextType::class)
            ->add('address', TextareaType::class)
            ->add('save', SubmitType::class, [
                'label' => 'Guardar',
                'attr'  => ['class' => 'e-save btn btn-primary'],
            ]);
        ;
    }
}
