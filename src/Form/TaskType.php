<?php

namespace App\Form;

use App\Entity\Task;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\DateIntervalType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Security;
use Vich\UploaderBundle\Form\Type\VichFileType;

class TaskType extends AbstractType
{
    private $security;
    private $router;

    public function __construct(Security $security, UrlGeneratorInterface $router)
    {
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
                    'attr' => array('rows' => '5')
                ])
            ->add('duration',
                DateIntervalType::class,
                [
                    'label' => 'Длительность задачи',
                    'required' => true,
                    'widget' => 'integer',
                    'with_years'  => false,
                    'with_months' => false,
                    'with_days'   => true,
                    'with_hours'  => true,
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
                        'placeholder' => 'Не выбран'
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
        if ($task->getAttachment() != null) {
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

        $builder->get('duration')
            ->addModelTransformer(new CallbackTransformer(
                function ($iso8601) {
                    if ($iso8601) {
                        return new \DateInterval($iso8601);
                    }
                    return new \DateInterval('P0DT0M');
                },
                function (\DateInterval $di) {
                    return $this->dateIntervalToString($di);
                }
            ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Task::class,
        ]);
    }

    /**
     * @param \DateInterval $interval
     *
     * @return string
     */
    private function dateIntervalToString(\DateInterval $interval) {

        $date = array_filter(array(
            'Y' => $interval->y,
            'M' => $interval->m,
            'D' => $interval->d
        ));

        $time = array_filter(array(
            'H' => $interval->h,
            'M' => $interval->i,
            'S' => $interval->s
        ));

        $specString = 'P';
        foreach ($date as $key => $value) {
            $specString .= $value . $key;
        }
        if (count($time) > 0) {
            $specString .= 'T';
            foreach ($time as $key => $value) {
                $specString .= $value . $key;
            }
        }

        return $specString == 'P' ? 'P0DT0M' : $specString;
    }
}
