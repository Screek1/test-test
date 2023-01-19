<?php
/**
 * Created by TutMee Co.
 * User: Domenik88(kataevevgenii@gmail.com)
 * Date: 20.10.2020
 *
 * @package viksemenov20
 */

namespace App\Form\Assessment;


use App\Model\Assessment\AssessmentModel;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AssessmentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('email')
            ->add('phone')
            ->add('selectedAddress')
            ->add('userSearch');
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => AssessmentModel::class,
                'csrf_protection' => false,
                'allow_extra_fields' => true
            ]
        );
    }
}