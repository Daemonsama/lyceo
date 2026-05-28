<?php

namespace App\Form;

use App\Entity\HomePromoBlock;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class HomePromoBlockType extends AbstractType
{
    private const EMPTY = '';

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('sectionTitle', TextType::class, [
                'label' => 'Titre de la section',
                'empty_data' => self::EMPTY,
            ])
            ->add('sectionLead', TextareaType::class, [
                'label' => 'Sous-titre (optionnel)',
                'attr' => ['rows' => 2],
                'required' => false,
                'empty_data' => self::EMPTY,
            ])
            ->add('videoFile', FileType::class, [
                'label' => 'Vidéo depuis votre ordinateur',
                'required' => false,
                'mapped' => false,
                'attr' => [
                    'accept' => 'video/mp4,video/webm,video/ogg,video/quicktime',
                    'class' => 'form-control',
                ],
                'help' => 'MP4, WebM, OGG ou MOV — max. 200 Mo. Prioritaire sur le lien ci-dessous.',
            ])
            ->add('removeUploadedVideo', CheckboxType::class, [
                'label' => 'Supprimer la vidéo enregistrée sur le serveur',
                'required' => false,
                'mapped' => false,
            ])
            ->add('videoUrl', TextareaType::class, [
                'label' => 'Lien vers une vidéo (YouTube, Vimeo, ou adresse .mp4 / .webm)',
                'attr' => ['rows' => 3],
                'required' => false,
                'help' => 'YouTube / Vimeo : collez l’URL de la barre d’adresse. '
                    .'LinkedIn : collez le lien du post ; sur le site, un bouton ouvre la vidéo sur LinkedIn (pas de lecture intégrée, limitation LinkedIn).',
                'empty_data' => self::EMPTY,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => HomePromoBlock::class,
        ]);
    }
}
