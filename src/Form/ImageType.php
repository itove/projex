<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Image;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Vich\UploaderBundle\Form\Type\VichImageType;

class ImageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('imageFile', VichImageType::class, [
                'label' => '图片文件',
                'required' => false,
                'allow_delete' => true,
                'download_uri' => false,
            ])
            ->add('caption', TextType::class, [
                'label' => '标题',
                'required' => false,
            ])
            // ->add('altText', TextType::class, [
            //     'label' => 'Alt 文本',
            //     'required' => false,
            // ])
            // ->add('category', TextType::class, [
            //     'label' => '分类',
            //     'required' => false,
            // ])
            ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Image::class,
        ]);
    }
}
