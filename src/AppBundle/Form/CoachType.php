<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;


class CoachType extends AbstractType
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
          ->add('nbLots', TextType::class, ['attr'   => ['maxlength' => '10']])

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
