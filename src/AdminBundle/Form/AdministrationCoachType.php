<?php

namespace AdminBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;


class AdministrationCoachType extends AbstractType
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
          ->add('nom', TextType::class, ['attr'   => ['maxlength' => '255', 'required' => true]])

          ->add('prenom', TextType::class, ['attr'   => ['maxlength' => '255', 'required' => true]])

          ->add('email',           EmailType::class, ['attr'   => ['maxlength' => '255', 'required' => true]])

          ->add('mobile',         TextType::class, ['attr'     => [
            'oninput'           => "this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');",
            'maxlength' => '10',
            'pattern' => '0[1-9]\d{8}',
            'placeholder' => '0600000000']])

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
          'data_class'         => 'AppBundle\Entity\Coach',
          'cascade_validation' => true
        ]);
    }
}
