<?php

namespace App\Form;

use App\Entity\Categorie;
use App\Entity\Formation;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
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
            ->add('apercuFile', FileType::class, [
                'mapped' => false,
                'required' => false,
                'label' => 'Aperçu de la formation (image)',
                'help' => 'JPEG, PNG, WebP ou GIF — max. 5 Mo. Sans image, l\'aperçu par défaut du catalogue est utilisé.',
                'attr' => [
                    'accept' => 'image/jpeg,image/png,image/webp,image/gif',
                    'class' => 'form-control',
                ],
            ])
            ->add('removeApercu', CheckboxType::class, [
                'mapped' => false,
                'required' => false,
                'label' => 'Supprimer l\'aperçu actuel et revenir à l\'image par défaut',
            ])
            ->add('media', TextType::class, [
                'required' => false,
                'label' => 'Vidéo de présentation',
                'help' => 'Lien Google Drive (partage « Toute personne disposant du lien »), YouTube, Vimeo, URL vidéo directe, ou nom de fichier local (ex. intro.mp4).',
                'attr' => [
                    'placeholder' => 'https://drive.google.com/file/d/…/view ou https://www.youtube.com/watch?v=…',
                ],
                'empty_data' => '',
            ])
            ->add('categorie', EntityType::class, [
                'class' => Categorie::class,
                'choice_label' => 'nom',
            ])
            ->add('promoCodes', CollectionType::class, [
                'entry_type' => FormationPromoCodeType::class,
                'entry_options' => ['label' => false],
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'label' => 'Codes promo',
                'help' => 'Codes réservés à cette formation. Ils sont créés automatiquement dans Stripe à l’enregistrement.',
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
