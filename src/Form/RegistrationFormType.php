<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'email',
                EmailType::class,
                [
                    'label' => 'Адрес электронной почты',
                    'required' => true,
                    'attr' => ['placeholder' => 'Введите свой адрес электронной почты',],
                ])
            ->add(
                'username',
                TextType::class,
                [
                    'label' => 'Имя пользователя',
                    'required' => true,
                    'attr' => ['placeholder' => 'Введите своё имя пользователя',],
                ])
            ->add(
                'plainPassword',
                PasswordType::class,
                [
                    'label' => 'Пароль',
                    'required' => true,
                    'attr' => ['placeholder' => 'Введите свой пароль',],
                ])
            ->add('termsAccepted',
                CheckboxType::class,
                [
                    'label' => 'я согласен с условиями пользования сайта',
                    'mapped' => false,
                    'constraints' => new IsTrue(),
                ])
        ;
    }

    public function getParent()
    {
        return 'FOS\UserBundle\Form\Type\RegistrationFormType';
    }

    public function getBlockPrefix()
    {
        return 'app_user_registration';
    }
}