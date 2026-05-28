<?php

namespace App\Form;

use App\Entity\Chapitre;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ChapitreType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titre')
            ->add('contenu', TextareaType::class, [
                'label' => 'Contenu texte',
                'required' => false,
                'attr' => [
                    'class' => 'ckeditor',
                ],
                'help' => 'Optionnel si vous ajoutez une vidéo ou une image. Au moins un contenu (texte, fichier ou lien) est requis.',
            ])
            ->add('media', FileType::class, [
                'label' => 'Image ou vidéo (fichier)',
                'required' => false,
                'mapped' => false,
                'attr' => [
                    'accept' => 'image/jpeg,image/png,image/gif,image/webp,video/mp4,video/webm,video/ogg,video/quicktime',
                    'class' => 'form-control',
                ],
                'help' => 'JPG, PNG, GIF, WebP, MP4, WebM, OGG ou MOV — maximum 100 Mo. Prioritaire sur le lien ci-dessous.',
            ])
            ->add('mediaUrl', TextType::class, [
                'label' => 'Lien Google Drive ou vidéo en ligne',
                'required' => false,
                'attr' => [
                    'placeholder' => 'https://drive.google.com/file/d/…/view?usp=sharing',
                ],
                'help' => 'Collez le lien de partage Google Drive (accès « Toute personne disposant du lien »). YouTube et Vimeo sont également acceptés.',
            ])
            ->add('removeMedia', CheckboxType::class, [
                'label' => 'Supprimer le média actuel (fichier ou lien)',
                'required' => false,
                'mapped' => false,
            ])
            ->add('ordre', IntegerType::class, [
                'required' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Chapitre::class,
        ]);
    }
}
