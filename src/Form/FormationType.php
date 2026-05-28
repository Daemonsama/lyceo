<?php

namespace App\Form;

use App\Entity\Categorie;
use App\Entity\Formation;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FormationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titre')
            ->add('description', TextareaType::class, [
                'attr' => [
                    'class' => 'ckeditor',
                ],
            ])
            ->add('prix', MoneyType::class, [
                'currency' => 'EUR',
            ])
            ->add('media', TextType::class, [
                'required' => false,
                'label' => 'Vidéo de présentation',
                'help' => 'Collez un lien YouTube/URL vidéo (https://...) ou un nom de fichier local (ex: intro.mp4).',
                'attr' => [
                    'placeholder' => 'https://www.youtube.com/watch?v=... ou intro.mp4',
                ],
                'empty_data' => '',
            ])
            ->add('categorie', EntityType::class, [
                'class' => Categorie::class,
                'choice_label' => 'nom',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Formation::class,
        ]);
    }
}
