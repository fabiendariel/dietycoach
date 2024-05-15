<?php

namespace AdminBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AdministrationCoachDisponibiliteType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $this->datas = $options['datas_import'];

/*/*
             * 'widget' => 'single_text',
            'html5'  => false,
            //'required' => false,
            //'format' => 'dd/mm/yyyy',
            // Options spécifique au datepicker Bootstrap
            //'attr'   => [
            //  'data-date-format'   => 'min="2017-06-01T08:30" max="2017-06-30T16:30"',
            //  'data-error'         => 'Cette valeur ne doit pas être vide.',
            //]
             */
        $builder
          ->add('id',      HiddenType::class, array('data' => $this->datas['id']))
          ->add('coa_id',  HiddenType::class, array('data' => $this->datas['coa_id']))
          ->add('date',    DateType::class, [
            'widget' => 'single_text',
            //'format' => 'dd-MM-yyyy',
            'html5'   => true,
            'required'=> true,
            'data' => isset($this->datas['date'])?$this->datas['date']:new \DateTime()

          ])
          ->add('heure_debut',          TimeType::class, [
            'widget' => 'choice',
            'hours'   => [8,9,10,11,12,13,14,15,16,17,18,19,20,21,22],
            'minutes'   => [0,30],
            'data' => isset($this->datas['heure_debut'])?$this->datas['heure_debut']:new \DateTime()
          ])
          ->add('heure_fin',            TimeType::class, [
            'widget' => 'choice',
            'hours'   => [8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23],
            'minutes'   => [0,30],
            'data' => isset($this->datas['heure_fin'])?$this->datas['heure_fin']:new \DateTime()
          ])

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
          'data_class'         => null,
          'datas_import'       => null,
          'cascade_validation' => true
        ]);
    }
}
