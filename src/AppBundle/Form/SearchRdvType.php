<?php

namespace AppBundle\Form;

use Doctrine\ORM\EntityRepository;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\DateType;

use AppBundle\Repository\EtageRepository;

class SearchRdvType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $date_options = [
            'widget' => 'single_text',
            'required' => false,
            'attr'   => [
              'date-format'   => 'dd/mm/yyyy',
              'min'=>'1900-01-01'
            ]
        ];

        $builder
          ->add('origineRappel', EntityType::class, [
            'class'        => 'AppBundle:ContactOrigine',
            'choice_label' => 'label',
            'placeholder'  => '',
            'expanded'     => false,
            'required'     => false
          ])
          ->add('dateRappel',     DateType::class, $date_options)
          ->add('dateCreation',   DateType::class, $date_options)

          ->add('save', SubmitType::class);
        ;
    }
    
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => null,
            'csrf_protection' => false
        ));
    }
    
    
}
