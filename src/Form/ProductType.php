<?php

namespace App\Form;

use App\Entity\Product;
use App\Entity\Category;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\ChoiceList\ChoiceList;

class ProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', null, [
                'label' => false,
            ])
            ->add('description', null, [
                'label' => false,
            ])
            ->add('price', null, [
                'label' => false,
            ])
            ->add('quantity', null, [
                'label' => false,
            ])
            ->add('cat', EntityType::class, [
                'class' => Category::class,
                'label' => false,
                'mapped' => false,
                'choice_value' => ChoiceList::value($this, 'id'),
                'choice_label' => function ($category) {
                    return $category->getName();
                }
            ])
            ->add('image', FileType::class, [
                'mapped' => false,
                'data_class' => null,
                'required' => false,
                'label' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
        ]);
    }
}
