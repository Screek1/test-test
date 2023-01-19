<?php
/**
 * Created by TutMee Co.
 * User: Domenik88(kataevevgenii@gmail.com)
 * Date: 20.10.2020
 *
 * @package viksemenov20
 */

namespace App\Form;


use App\Model\UserProfileModel;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserProfileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('about')
            ->add('address')
            ->add('city')
            ->add('email')
            ->add('name')
            ->add('phoneNumber')
            ->add('postalCode')
            ->add('state')
            ->add('userType');
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => UserProfileModel::class,
                'csrf_protection' => false,
                'allow_extra_fields' => true
            ]
        );
    }
}