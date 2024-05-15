<?php

namespace GestionBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Validator\Constraints\NotBlank;


class RappelBackType extends AbstractType
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
              'min'=>'1900-01-01'
            ],
            'constraints' => [
                new NotBlank()
            ]
        ];

        $date_options_bis = [
            'widget' => 'single_text',
            'required' => false,
            'attr'   => [
                'date-format'   => 'dd/mm/yyyy',
                'min'=>'1900-01-01'
            ]
        ];

        $builder
            ->add('dateNaissance',             DateType::class, $date_options)
            ->add('nom',              TextType::class, ['attr'   => ['maxlength' => '50']])
            ->add('prenom',           TextType::class, ['attr'   => ['maxlength' => '50']])
            ->add('sexe', ChoiceType::class, [
                'expanded' => true,
                'multiple' => false,
                'choices'  => [
                    'Homme' => 'Homme',
                    'Femme' => 'Femme',
                ],
            ])
            ->add('commentaire',       TextareaType::class, [
                'attr'     => [
                    'maxlength' => '9999'
                 ]])
            ->add('traitant', CheckboxType::class, ['required' => false])
            ->add('nomTraitant',              TextType::class, ['required' => false, 'attr'   => ['maxlength' => '50']])
            ->add('prenomTraitant',           TextType::class, ['required' => false,'attr'   => ['maxlength' => '50']])
            ->add('emailTraitant',            EmailType::class, ['required' => false,'attr'   => ['maxlength' => '255']])
            ->add('codePostalTraitant',       TextType::class, ['required' => false,'attr'   => ['maxlength' => '50']])
            ->add('numeroTraitant',          TextType::class, [
                'required' => false,
                'attr'     => [
                    'oninput'           => "this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');",
                    'maxlength' => '10'
                ]])
            ->add('dateTraitement',             DateType::class, $date_options_bis)

            ->add('commentaireTraitement',       TextareaType::class, [
                'required' => false,
                'attr'     => [
                    'maxlength' => '9999'
                ]])
            ->add('save',          SubmitType::class);
        ;


    }

    // -----------------------------------------------------------------------------------------------------------------

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class'         => 'AppBundle\Entity\Contact',
            'cascade_validation' => true
        ]);
    }
}
