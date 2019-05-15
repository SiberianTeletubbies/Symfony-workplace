<?php

namespace App\Form;

use App\Entity\Task;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateIntervalType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Security;
use Vich\UploaderBundle\Form\Type\VichFileType;
use Vich\UploaderBundle\Form\Type\VichImageType;

class TaskType extends AbstractType
{
    private $security;
    private $router;

    public function __construct(
        Security $security,
        UrlGeneratorInterface $router
    ) {
        $this->security = $security;
        $this->router = $router;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('description',
                TextareaType::class,
                [
                    'label' => 'Описание',
                    'required' => true,
                    'attr' => ['rows' => '5'],
                ])
            ->add('duration',
                DateIntervalType::class,
                [
                    'label' => 'Длительность задачи',
                    'input' => 'string',
                    'required' => true,
                    'widget' => 'integer',
                    'with_years' => false,
                    'with_months' => false,
                    'with_days' => true,
                    'with_hours' => true,
                ])
            ->add('additionalData',
                TaskAdditionalDataType::class,
                [
                    'label' => false,
                ]);

        if ($this->security->isGranted('ROLE_ADMIN')) {
            $builder
                ->add('user',
                    EntityType::class,
                    [
                        'class' => User::class,
                        'choice_label' => 'username',
                        'label' => 'Пользователь задачи',
                        'required' => false,
                        'empty_data' => null,
                        'placeholder' => 'Не выбран',
                    ])
            ;
        }

        $params = [
            'label' => 'Файл задачи',
            'required' => false,
            'attr' => ['placeholder' => 'Выберите файл'],
        ];
        /* @var Task $task */
        $task = $builder->getData();
        if (null != $task->getAttachment()) {
            $params['download_uri'] = $this->router->generate(
                'task.download', ['id' => $task->getId()]
            );
            $params['attr']['placeholder'] = $task->getAttachmentFileName();
        }
        $builder
            ->add('attachmentFile',
                VichFileType::class,
                $params
            );

        $params = [
            'label' => 'Изображение задачи',
            'required' => false,
            'attr' => ['placeholder' => 'Выберите изображение'],
            'imagine_pattern' => 'task_image_100x100',
            'download_uri' => false,
        ];
        if (null != $task->getImage()) {
            $params['attr']['placeholder'] = $task->getImageFileName();
        }
        $builder
            ->add('imageFile',
                VichImageType::class,
                $params
            );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Task::class,
        ]);
    }
}
