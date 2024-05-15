<?php

namespace AdminBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;



class AdministrationCodeAccesType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $date_options = [
          'widget' => 'single_text',
          'attr'   => [
            'date-format'   => 'dd/mm/yyyy',
          ]
        ];


        $builder
          ->add('nbLots', NumberType::class, ['attr'   => ['maxlength' => '3', 'max'=>350]])

          ->add('datePeremptionDebut', DateType::class, $date_options)

          ->add('datePeremptionFin', DateType::class, $date_options)

          ->add('save', SubmitType::class);
        ;


    }


    // -----------------------------------------------------------------------------------------------------------------

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
          'data_class'         => null,
          'cascade_validation' => true
        ]);
    }
}
