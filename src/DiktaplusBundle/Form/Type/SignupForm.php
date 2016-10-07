<?php

namespace Web\BlogBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Doctrine\ORM\EntityRepository;

class SignupForm extends AbstractType {

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add('email', 'email', array("label" => "Email: ",
                "required" => false,
                "attr" => array('class' => 'form-control')))

            ->add('password', 'repeated', array(
                'type' => 'password',
                'invalid_message' => 'Passwords do not match',
                'required' => true,
                'first_options' => array('label' => 'Password: ',"attr" => array('class' => 'form-control')),
                'second_options' => array('label' => 'Repeat password: ',"attr" => array('class' => 'form-control'))))

            ->add('Submit', 'submit',array("attr" => array('class' => 'btn btn-success')));
    }

    public function getName() {
        return 'Sign up';
    }

}

?>