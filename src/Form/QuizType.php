<?php

namespace App\Form;

use App\Entity\Chapitre;
use App\Entity\Quiz;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class QuizType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titre', TextType::class, [
                'label' => 'Titre du quiz',
            ])
            ->add('seuilReussite', IntegerType::class, [
                'label' => 'Score minimum pour réussir (%)',
                'attr' => ['min' => 1, 'max' => 100],
            ]);

        if ($options['chapitre_choices']) {
            $builder->add('chapitre', EntityType::class, [
                'class' => Chapitre::class,
                'choices' => $options['chapitre_choices'],
                'choice_label' => fn (Chapitre $c) => sprintf('Ch. %d – %s', $c->getOrdre(), $c->getTitre()),
                'label' => 'Après le chapitre',
                'disabled' => $options['lock_chapitre'],
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Quiz::class,
            'chapitre_choices' => [],
            'lock_chapitre' => false,
        ]);
    }
}
