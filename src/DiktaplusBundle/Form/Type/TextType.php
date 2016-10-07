<?php

namespace DiktaplusBundle\Form\Type;

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\LanguageType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Doctrine\ORM\EntityRepository;

class TextType extends AbstractType {

    public function buildForm(FormBuilderInterface $builder, array $options) {

        $builder->add('language', LanguageType::class, array(
            'required' => true,
        ));

        $builder->add('difficulty', ChoiceType::class, array(
            'choices'  => array(
                'Easy' => 'Easy',
                'Medium' => 'Medium',
                'Hard' => 'Hard',
            ),
            'required' => true,
            'choices_as_values' => true,
        ));

        $builder->add('content', TextareaType::class, array(
            'attr' => array('class' => 'tinymce'),
            'required' => 'true'
        ));


        $builder->add('Submit', 'submit',array("attr" => array('class' => 'btn btn-success', 'style' => 'margin-top:20px;')));
    }

    public function getName() {
        return 'AddText';
    }

}

?>