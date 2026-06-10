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
    private const EMPTY = '';

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $iconHelp = 'Icône Bootstrap (sans « bi- »), ex. building. Laissez vide pour afficher une valeur texte.';

        $builder
            ->add('heroTitlePrefix', TextType::class, [
                'label' => 'Début du titre',
                'empty_data' => self::EMPTY,
            ])
            ->add('heroTitleHighlight', TextType::class, [
                'label' => 'Partie soulignée du titre',
                'empty_data' => self::EMPTY,
            ])
            ->add('heroLead', TextareaType::class, [
                'label' => 'Texte d\'introduction',
                'attr' => ['class' => 'ckeditor', 'rows' => 4],
                'empty_data' => self::EMPTY,
            ])

            ->add('stat1Value', TextType::class, ['label' => 'Chiffre 1 — valeur', 'required' => false, 'empty_data' => self::EMPTY])
            ->add('stat1Icon', TextType::class, ['label' => 'Chiffre 1 — icône', 'required' => false, 'help' => $iconHelp])
            ->add('stat1Label', TextType::class, ['label' => 'Chiffre 1 — légende', 'required' => false, 'empty_data' => self::EMPTY, 'help' => 'Laissez tout vide pour masquer cet indicateur.'])
            ->add('stat2Value', TextType::class, ['label' => 'Chiffre 2 — valeur', 'required' => false, 'empty_data' => self::EMPTY])
            ->add('stat2Icon', TextType::class, ['label' => 'Chiffre 2 — icône', 'required' => false, 'help' => $iconHelp])
            ->add('stat2Label', TextType::class, ['label' => 'Chiffre 2 — légende', 'required' => false, 'empty_data' => self::EMPTY, 'help' => 'Laissez tout vide pour masquer cet indicateur.'])
            ->add('stat3Value', TextType::class, ['label' => 'Chiffre 3 — valeur', 'required' => false, 'empty_data' => self::EMPTY])
            ->add('stat3Icon', TextType::class, ['label' => 'Chiffre 3 — icône', 'required' => false, 'help' => $iconHelp])
            ->add('stat3Label', TextType::class, ['label' => 'Chiffre 3 — légende', 'required' => false, 'empty_data' => self::EMPTY, 'help' => 'Laissez tout vide pour masquer cet indicateur.'])
            ->add('stat4Value', TextType::class, ['label' => 'Chiffre 4 — valeur', 'required' => false, 'empty_data' => self::EMPTY])
            ->add('stat4Icon', TextType::class, ['label' => 'Chiffre 4 — icône', 'required' => false, 'help' => $iconHelp])
            ->add('stat4Label', TextType::class, ['label' => 'Chiffre 4 — légende', 'required' => false, 'empty_data' => self::EMPTY, 'help' => 'Laissez tout vide pour masquer cet indicateur.'])

            ->add('missionTitle', TextType::class, ['label' => 'Titre de section', 'empty_data' => self::EMPTY])
            ->add('missionLead', TextareaType::class, [
                'label' => 'Accroche',
                'attr' => ['class' => 'ckeditor', 'rows' => 4],
                'empty_data' => self::EMPTY,
            ])
            ->add('missionBody', TextareaType::class, [
                'label' => 'Paragraphe principal',
                'attr' => ['class' => 'ckeditor', 'rows' => 5],
                'empty_data' => self::EMPTY,
            ])
            ->add('missionCardTitle', TextType::class, ['label' => 'Titre du encadré objectifs', 'empty_data' => self::EMPTY])
            ->add('missionListItems', TextareaType::class, [
                'label' => 'Liste des objectifs',
                'attr' => ['rows' => 6],
                'help' => 'Une ligne = une puce.',
                'empty_data' => self::EMPTY,
            ])

            ->add('audienceTitle', TextType::class, ['label' => 'Titre de section', 'empty_data' => self::EMPTY])
            ->add('audienceLead', TextareaType::class, [
                'label' => 'Sous-titre',
                'attr' => ['rows' => 2],
                'empty_data' => self::EMPTY,
            ])
            ->add('audienceCard1Title', TextType::class, ['label' => 'Carte 1 — titre', 'required' => false, 'empty_data' => self::EMPTY, 'help' => 'Laissez vide pour masquer la carte.'])
            ->add('audienceCard1Text', TextareaType::class, ['label' => 'Carte 1 — texte', 'required' => false, 'attr' => ['rows' => 3], 'empty_data' => self::EMPTY])
            ->add('audienceCard1Icon', TextType::class, ['label' => 'Carte 1 — icône', 'required' => false, 'help' => $iconHelp, 'empty_data' => self::EMPTY])
            ->add('audienceCard2Title', TextType::class, ['label' => 'Carte 2 — titre', 'required' => false, 'empty_data' => self::EMPTY, 'help' => 'Laissez vide pour masquer la carte.'])
            ->add('audienceCard2Text', TextareaType::class, ['label' => 'Carte 2 — texte', 'required' => false, 'attr' => ['rows' => 3], 'empty_data' => self::EMPTY])
            ->add('audienceCard2Icon', TextType::class, ['label' => 'Carte 2 — icône', 'required' => false, 'help' => $iconHelp, 'empty_data' => self::EMPTY])

            ->add('servicesTitle', TextType::class, ['label' => 'Titre de section', 'empty_data' => self::EMPTY])
            ->add('servicesLead', TextareaType::class, [
                'label' => 'Sous-titre',
                'attr' => ['rows' => 2],
                'empty_data' => self::EMPTY,
            ])
            ->add('service1Title', TextType::class, ['label' => 'Service 1 — titre', 'required' => false, 'empty_data' => self::EMPTY, 'help' => 'Laissez vide pour masquer ce service.'])
            ->add('service1Text', TextareaType::class, ['label' => 'Service 1 — description', 'required' => false, 'attr' => ['rows' => 3], 'empty_data' => self::EMPTY])
            ->add('service1Icon', TextType::class, ['label' => 'Service 1 — icône', 'required' => false, 'help' => $iconHelp, 'empty_data' => self::EMPTY])
            ->add('service2Title', TextType::class, ['label' => 'Service 2 — titre', 'required' => false, 'empty_data' => self::EMPTY, 'help' => 'Laissez vide pour masquer ce service.'])
            ->add('service2Text', TextareaType::class, ['label' => 'Service 2 — description', 'required' => false, 'attr' => ['rows' => 3], 'empty_data' => self::EMPTY])
            ->add('service2Icon', TextType::class, ['label' => 'Service 2 — icône', 'required' => false, 'help' => $iconHelp, 'empty_data' => self::EMPTY])
            ->add('service3Title', TextType::class, ['label' => 'Service 3 — titre', 'required' => false, 'empty_data' => self::EMPTY, 'help' => 'Laissez vide pour masquer ce service.'])
            ->add('service3Text', TextareaType::class, ['label' => 'Service 3 — description', 'required' => false, 'attr' => ['rows' => 3], 'empty_data' => self::EMPTY])
            ->add('service3Icon', TextType::class, ['label' => 'Service 3 — icône', 'required' => false, 'help' => $iconHelp, 'empty_data' => self::EMPTY])

            ->add('aboutImageFile', FileType::class, [
                'label' => 'Photo (fichier)',
                'required' => false,
                'mapped' => false,
                'attr' => [
                    'accept' => 'image/jpeg,image/png,image/webp,image/gif',
                    'class' => 'form-control home-about-file-input',
                ],
                'help' => 'JPEG, PNG, WebP ou GIF — max. 5 Mo.',
            ])
            ->add('removeUploadedAboutImage', CheckboxType::class, [
                'label' => 'Supprimer la photo enregistrée',
                'required' => false,
                'mapped' => false,
            ])
            ->add('aboutImagePanX', HiddenType::class)
            ->add('aboutImagePanY', HiddenType::class)
            ->add('aboutImageZoom', HiddenType::class)
            ->add('aboutImageUrl', TextareaType::class, [
                'label' => 'Adresse web de l\'image (optionnel)',
                'attr' => ['rows' => 2],
                'empty_data' => self::EMPTY,
            ])
            ->add('aboutName', TextType::class, ['label' => 'Nom sous la photo', 'empty_data' => self::EMPTY])
            ->add('aboutRole', TextType::class, ['label' => 'Rôle / sous-titre', 'empty_data' => self::EMPTY])
            ->add('aboutHeading', TextType::class, ['label' => 'Titre de la colonne texte', 'empty_data' => self::EMPTY])
            ->add('aboutParagraph1', TextareaType::class, [
                'label' => 'Premier paragraphe',
                'attr' => ['class' => 'ckeditor', 'rows' => 5],
                'empty_data' => self::EMPTY,
            ])
            ->add('aboutParagraph2', TextareaType::class, [
                'label' => 'Deuxième paragraphe',
                'attr' => ['class' => 'ckeditor', 'rows' => 5],
                'empty_data' => self::EMPTY,
            ])
            ->add('aboutListTitle', TextType::class, ['label' => 'Titre du bloc liste', 'empty_data' => self::EMPTY])
            ->add('aboutListItems', TextareaType::class, [
                'label' => 'Éléments de la liste',
                'attr' => ['rows' => 8],
                'help' => 'Une ligne par puce.',
                'empty_data' => self::EMPTY,
            ])
            ->add('aboutBadge1', TextType::class, ['label' => 'Badge 1', 'required' => false, 'empty_data' => self::EMPTY, 'help' => 'Laissez vide pour masquer. Icône : médaille.'])
            ->add('aboutBadge2', TextType::class, ['label' => 'Badge 2', 'required' => false, 'empty_data' => self::EMPTY, 'help' => 'Laissez vide pour masquer. Sans icône par défaut.'])
            ->add('aboutBadge3', TextType::class, ['label' => 'Badge 3', 'required' => false, 'empty_data' => self::EMPTY, 'help' => 'Laissez vide pour masquer. Icône : ampoule.'])
            ->add('aboutBadge4', TextType::class, ['label' => 'Badge 4', 'required' => false, 'empty_data' => self::EMPTY, 'help' => 'Laissez vide pour masquer. Icône : étoile.'])

            ->add('contactTitle', TextType::class, ['label' => 'Titre', 'empty_data' => self::EMPTY])
            ->add('contactLead', TextareaType::class, [
                'label' => 'Texte d\'introduction',
                'attr' => ['rows' => 3],
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
