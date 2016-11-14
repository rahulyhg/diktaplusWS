<?php

namespace DiktaplusBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\FormBuilderInterface;

class UserType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('email', 'email', array("label" => "Email: ",
            "required" => true,
            "attr" => array('class' => 'form-control')))
            ->add('username', 'text', array("label" => "Username: ",
                "required" => true,
                "attr" => array('class' => 'form-control')))
            ->add('country', CountryType::class, array("label" => "Country: ",
                "required" => true,
                "attr" => array('class' => 'form-control')))
            ->add('password', 'repeated', array(
                'type' => 'password',
                'invalid_message' => 'Passwords do not match',
                'required' => true,
                'first_options' => array('label' => 'Password: ', "attr" => array('class' => 'form-control')),
                'second_options' => array('label' => 'Repeat password: ', "attr" => array('class' => 'form-control'))))
            ->add('Submit', 'submit', array("attr" => array('class' => 'btn btn-success', 'style' => 'margin-top:15px;')));
    }

    public function getName()
    {
        return '';
    }


}

?>