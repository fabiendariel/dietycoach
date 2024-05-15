<?php

namespace AdminBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AdministrationUserType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('firstname',     TextType::class, ['required' => false])
            ->add('lastname',      TextType::class, ['required'=>false])
            ->add('username',      TextType::class, ['attr' => ['autocomplete' => 'false']])
            ->add('email',         TextType::class, ['attr' => ['autocomplete' => 'false']])
            ->add('password',      PasswordType::class, ['required'=>false,'attr' => ['autocomplete' => 'false']])
        ;


    }

    // -----------------------------------------------------------------------------------------------------------------

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class'         => 'AppBundle\Entity\User',
            'cascade_validation' => true
        ]);
    }
}
