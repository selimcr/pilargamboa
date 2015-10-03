<?php

namespace Tecnotek\Bundle\AsiloBundle\Form;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Security\Core\SecurityContext;

class PatientForm extends AbstractType
{
    private $securityContext;
    private $em;
    /**
     * @param SecurityContext $securityContext
     * @param \Doctrine\ORM\EntityManager $em
     */
    public function __construct(SecurityContext $securityContext, \Doctrine\ORM\EntityManager $em)
    {
        $this->securityContext = $securityContext;
        $this->em = $em;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $maritalStatusList = $this->em->getRepository("TecnotekAsiloBundle:MaritalStatus")->findAll();
        $maritalStatusOptions = array();
        $maritalStatusOptions['Other'] = "Otro";
        foreach ($maritalStatusList as $maritalStatus){
            $maritalStatusOptions[$maritalStatus->getName()] = $maritalStatus->getName();
        }

        $nutritionalStatusList = $this->em->getRepository("TecnotekAsiloBundle:NutritionalStatus")->findAll();
        $nutritionalStatusOptions = array();
        $nutritionalStatusOptions['Other'] = "Otro";
        foreach ($nutritionalStatusList as $nutritionalStatus){
            $nutritionalStatusOptions[$nutritionalStatus->getName()] = $nutritionalStatus->getName();
        }

        $scholarityList = $this->em->getRepository("TecnotekAsiloBundle:Scholarity")->findAll();
        $scholarityOptions = array();
        $scholarityOptions['Other'] = "Otra";
        foreach ($scholarityList as $scholarity){
            $scholarityOptions[$scholarity->getName()] = $scholarity->getName();
        }

        $builder
            ->add('firstname', 'text', array('trim' => true))
            ->add('lastname', 'text', array('trim' => true))
            ->add('secondSurname', 'text', array('trim' => true, 'required' => false))
            ->add('documentId', 'text', array('trim' => true, 'required' => false))
            ->add('birthdate', 'date', array(
                'input'  => 'datetime',
                'widget' => 'single_text', 'required' => false,
            ))
            ->add('admissionDate', 'date', array(
                'input'  => 'datetime',
                'widget' => 'single_text', 'required' => false,
            ))
            ->add('gender', 'choice', array(
                'choices'  => array('1' => 'Hombre', '2' => 'Mujer'),
                'required' => true, 'required' => true,
            ))
            ->add('country', 'choice', array(
                'choices'  => array('Costarricense' => 'Costarricense', 'Other' => 'Otra'),
                'required' => true, 'required' => true,
            ))
            ->add('state', 'text', array('trim' => true, 'required' => false))
            ->add('civilStatus', 'choice', array(
                'choices'  => $maritalStatusOptions,
                'required' => true, 'required' => false,
            ))
            ->add('address', 'textarea',
                array('trim' => true, 'required' => false,))

            ->add('weight', 'number',
                array('trim' => true, 'required' => false,))
            ->add('height', 'number',
                array('trim' => true, 'required' => false,))
            ->add('brachialCircumference', 'number',
                array('trim' => true, 'required' => false,))
            ->add('calfCircumference', 'number',
                array('trim' => true, 'required' => false,))
            ->add('kneeHeight', 'number',
                array('trim' => true, 'required' => false,))
            ->add('imc', 'number',
                array('trim' => true, 'required' => false,))
            ->add('nutritionalState', 'choice', array(
                'choices'  => $nutritionalStatusOptions,
                'required' => true, 'required' => false,
            ))
            ->add('scholarity', 'choice', array(
                'choices'  => $scholarityOptions,
                'required' => true, 'required' => false,
            ))
            ->add('liveWith', 'textarea',
                array('trim' => true, 'required' => false,))
            ->add('phones', 'textarea',
                array('trim' => true, 'required' => false,))
            ->add('laterality', 'choice', array(
                'choices'  => array('Derecho' => 'Derecho', 'Izquierdo' => 'Izquierdo'),
                'required' => true, 'required' => false,
            ))
        ;


            /*->
            add('description', 'textarea',
                    array('trim' => true,
                          'attr' => array('placeholder' => 'restaurant.description')));

        /*$restaurant = $options['data'];
        if( $restaurant->getAlias() == null){
            $builder->add('alias', 'text', array('trim' => true));
        }
        // Current logged user
        $user = $this->securityContext->getToken()->getUser();
        if($user->hasRole("ROLE_ADMIN")){
            $builder->add('categories', null, array('expanded' => "true", "multiple" => "true"));
        }*/
    }

    public function getName()
    {
        return 'tecnotek_patient_form';
    }
}
