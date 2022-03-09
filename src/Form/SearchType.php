<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class SearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('search', TextType::class,
                [
                    "attr" => [
                        "placeholder" => "Account ID",
                        "class" => "form-control col-lg-2"
                    ]
                ])
            ->add('searchBtn', SubmitType::class, 
                [
                'label' => 'GET DATA',
                "attr" => [
                    "class" => "btn btn-info btn-sm"
                ]])
            ->add('allDataBtn', SubmitType::class, [
                'label' => 'GET ALL', 
                "attr" => [
                    "class" => "btn btn-info btn-sm"
                ]])
            ->getForm();
    }
}
