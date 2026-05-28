<?php

namespace App\Form;

use App\Entity\HomePageContent;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class HomePageContentType extends AbstractType
{
    /** Évite null vers des propriétés string (ex. CKEditor vide). */
    private const EMPTY = '';

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('heroTitlePrefix', TextType::class, [
                'label' => 'Bandeau — début du titre (avant la partie soulignée)',
                'empty_data' => self::EMPTY,
            ])
            ->add('heroTitleHighlight', TextType::class, [
                'label' => 'Bandeau — partie soulignée',
                'empty_data' => self::EMPTY,
            ])
            ->add('heroLead', TextareaType::class, [
                'label' => 'Bandeau — texte d\'introduction sous le titre',
                'attr' => ['class' => 'ckeditor', 'rows' => 4],
                'empty_data' => self::EMPTY,
            ])
            ->add('aboutImageFile', FileType::class, [
                'label' => 'Photo (fichier sur votre ordinateur)',
                'required' => false,
                'mapped' => false,
                'attr' => [
                    'accept' => 'image/jpeg,image/png,image/webp,image/gif',
                    'class' => 'form-control home-about-file-input',
                ],
                'help' => 'JPEG, PNG, WebP ou GIF — max. 5 Mo. Prioritaire sur une adresse web ci-dessous.',
            ])
            ->add('removeUploadedAboutImage', CheckboxType::class, [
                'label' => 'Supprimer la photo enregistrée sur le serveur',
                'required' => false,
                'mapped' => false,
            ])
            ->add('aboutImagePanX', HiddenType::class)
            ->add('aboutImagePanY', HiddenType::class)
            ->add('aboutImageZoom', HiddenType::class)
            ->add('aboutImageUrl', TextareaType::class, [
                'label' => 'Adresse web de l’image (optionnel)',
                'attr' => ['rows' => 2],
                'help' => 'Si vous n’envoyez pas de fichier, vous pouvez coller un lien https:// vers une image.',
                'empty_data' => self::EMPTY,
            ])
            ->add('aboutName', TextType::class, [
                'label' => 'À propos — nom affiché sous la photo',
                'empty_data' => self::EMPTY,
            ])
            ->add('aboutRole', TextType::class, [
                'label' => 'À propos — sous-titre (ex. Coach professionnel certifié)',
                'empty_data' => self::EMPTY,
            ])
            ->add('aboutHeading', TextType::class, [
                'label' => 'À propos — titre de la colonne texte',
                'empty_data' => self::EMPTY,
            ])
            ->add('aboutParagraph1', TextareaType::class, [
                'label' => 'À propos — premier paragraphe',
                'attr' => ['class' => 'ckeditor', 'rows' => 5],
                'help' => 'Vous pouvez utiliser du HTML simple (&lt;strong&gt;, &lt;em&gt;, etc.).',
                'empty_data' => self::EMPTY,
            ])
            ->add('aboutParagraph2', TextareaType::class, [
                'label' => 'À propos — deuxième paragraphe',
                'attr' => ['class' => 'ckeditor', 'rows' => 5],
                'empty_data' => self::EMPTY,
            ])
            ->add('aboutListTitle', TextType::class, [
                'label' => 'À propos — titre du bloc liste',
                'empty_data' => self::EMPTY,
            ])
            ->add('aboutListItems', TextareaType::class, [
                'label' => 'À propos — lignes du bloc « Parcours & expériences »',
                'attr' => ['rows' => 8],
                'help' => 'Une ligne par puce (pas de tiret : chaque ligne devient une ligne à part).',
                'empty_data' => self::EMPTY,
            ])
            ->add('aboutBadge1', TextType::class, [
                'label' => 'Badge 1',
                'empty_data' => self::EMPTY,
            ])
            ->add('aboutBadge2', TextType::class, [
                'label' => 'Badge 2',
                'empty_data' => self::EMPTY,
            ])
            ->add('aboutBadge3', TextType::class, [
                'label' => 'Badge 3',
                'empty_data' => self::EMPTY,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => HomePageContent::class,
        ]);
    }
}
