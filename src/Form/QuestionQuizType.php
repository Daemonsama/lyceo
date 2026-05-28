<?php

namespace App\Form;

use App\Entity\QuestionQuiz;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class QuestionQuizType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('enonce', TextareaType::class, [
                'label' => 'Énoncé de la question',
                'attr' => ['rows' => 3],
            ])
            ->add('ordre', IntegerType::class, [
                'label' => 'Ordre',
            ])
            ->add('reponses', CollectionType::class, [
                'entry_type' => ReponseQuizType::class,
                'entry_options' => ['label' => false],
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'label' => 'Réponses',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => QuestionQuiz::class,
        ]);
    }
}
