<?php

namespace App\Form;

use App\Entity\TaskAdditionalData;
use App\Services\StringFormatterService;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Range;

class TaskAdditionalDataType extends AbstractType
{
    private $formatter;

    public function __construct(StringFormatterService $formatter)
    {
        $this->formatter = $formatter;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('info',
                TextareaType::class,
                [
                    'label' => 'Дополнительная информация',
                    'required' => true,
                    'constraints' => [
                        new NotBlank(),
                    ],
                    'attr' => ['rows' => '3'],
                    'mapped' => false,
                ])
            ->add('priority',
                ChoiceType::class,
                [
                    'label' => 'Приоритет задачи',
                    'required' => true,
                    'constraints' => [
                        new NotBlank(),
                        new Range(['min' => 0, 'max' => 2]),
                    ],
                    'choices' => [
                        $this->formatter->formatPriorityTask(StringFormatterService::PRIORITY_LOW) => StringFormatterService::PRIORITY_LOW,
                        $this->formatter->formatPriorityTask(StringFormatterService::PRIORITY_MEDIUM) => StringFormatterService::PRIORITY_MEDIUM,
                        $this->formatter->formatPriorityTask(StringFormatterService::PRIORITY_HIGH) => StringFormatterService::PRIORITY_HIGH,
                    ],
                    'mapped' => false,
                ]);

        $builder->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $event) {
            /** @var TaskAdditionalData $data */
            $data = $event->getData();
            if ($data) {
                $taskData = $data->getData();
                $form = $event->getForm();
                $form->get('info')->setData(isset($taskData['info']) ? $taskData['info'] : null);
                $form->get('priority')->setData(isset($taskData['priority']) ? $taskData['priority'] : null);
            }
        });

        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) {
            /** @var TaskAdditionalData $data */
            $data = $event->getData();
            $form = $event->getForm();
            $data->setData([
                'info' => $form->get('info')->getData(),
                'priority' => $form->get('priority')->getData(),
            ]);
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => TaskAdditionalData::class,
        ]);
    }
}
