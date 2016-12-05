<?php

namespace DiktaplusBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\LanguageType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;

class TextType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder->add('language', LanguageType::class, array(
            'required' => true,
            "attr" => array('class' => 'form-control')
        ));

        $builder->add('difficulty', ChoiceType::class, array(
            'choices' => array(
                'Easy' => 'Easy',
                'Medium' => 'Medium',
                'Hard' => 'Hard',
            ),
            'required' => true,
            "attr" => array('class' => 'form-control'),
            'choices_as_values' => true
        ));

        $builder->add('title', \Symfony\Component\Form\Extension\Core\Type\TextType::class, array(
            'required' => 'true',
            "attr" => array('class' => 'form-control')
        ));

        $builder->add('content', TextareaType::class, array(
            'attr' => array('class' => 'tinymce form-control'),
            'required' => 'true',
        ));

        $builder->add('Submit', 'submit', array("attr" => array('class' => 'btn btn-success', 'style' => 'margin-top:20px;')));
    }

    public function getName()
    {
        return 'AddText';
    }

}

?>