<?php

namespace App\Controller;

use App\Entity\Status;
use App\Entity\Task;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;

class TaskController extends AbstractController
{
    public function list(ObjectManager $manager)
    {
        $tasks = $manager->getRepository(Task::class)->findAll();

        return $this->render("admin/task/list.html.twig", [
            "tasks" => $tasks
        ]);
    }

    public function view($id, ObjectManager $manager)
    {
        $task = $manager->getRepository(Task::class)->find($id);

        return $this->render("admin/task/view.html.twig", [
            "task" => $task
        ]);
    }

    public function add(Request $request, ObjectManager $manager)
    {
        $task = new Task();

        $form = $this->createFormBuilder($task)
            ->add("title", TextType::class, [
                "label" => "Название задачи",
                "attr" => ["class" => "form-control"]
            ])
            ->add("description", TextareaType::class, [
                "label" => "Описание задачи",
                "attr" => ["class" => "form-control"]
            ])
            ->add("due_date", DateTimeType::class, [
                "label" => "Срок до",
                "widget" => "single_text",
                "attr" => ["class" => "form-control"]
            ])
            ->add("status", EntityType::class, [
                "class" => Status::class,
                "choice_label" => "title",
                "label" => "Статус",
                "attr" => ["class" => "form-control"]
            ])
            ->add("save", SubmitType::class, [
                "label" => "Добавить",
                "attr" => ["class" => "btn btn-primary"]
            ])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $task = $form->getData();
            $check = $manager->getRepository(Task::class)->findOneBy(["title" => $task->getTitle()]);
            if ($check) {
                $form->addError(new FormError("Такая задача уже существует"));
            } else {
                $task->setCreatedAt(new \DateTime('now'));
                $task->setUpdatedAt(new \DateTime('now'));
                $manager->persist($task);
                $manager->flush();
                return $this->redirectToRoute("task-list");
            }
        }

        return $this->render("admin/task/add.html.twig", [
            "form" => $form->createView()
        ]);
    }

    public function edit($id, ObjectManager $manager, Request $request)
    {
        $task = $manager->getRepository(Task::class)->find($id);

        $form = $this->createFormBuilder($task)
            ->add("title", TextType::class, [
                "label" => "Название задачи",
                "attr" => ["class" => "form-control"]
            ])
            ->add("description", TextareaType::class, [
                "label" => "Описание задачи",
                "attr" => ["class" => "form-control"]
            ])
            ->add("due_date", DateTimeType::class, [
                "label" => "Срок до",
                "widget" => "single_text",
                "attr" => ["class" => "form-control"]
            ])
            ->add("status", EntityType::class, [
                "class" => Status::class,
                "choice_label" => "title",
                "label" => "Статус",
                "attr" => ["class" => "form-control"]
            ])
            ->add("save", SubmitType::class, [
                "label" => "Сохранить",
                "attr" => ["class" => "btn btn-primary"]
            ])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $task = $form->getData();
            $check = $manager->getRepository(Task::class)->findTaskByTitleWithNotMatchId($id, $task->getTitle());
            if ($check) {
                $form->addError(new FormError("Такая задача уже существует"));
            } else {
                $task->setUpdatedAt(new \DateTime('now'));
                $manager->persist($task);
                $manager->flush();
                return $this->redirectToRoute("task-list");
            }
        }

        return $this->render("admin/task/edit.html.twig", [
            "form" => $form->createView()
        ]);
    }

    public function delete($id, ObjectManager $manager, Request $request)
    {
        $task = $manager->getRepository(Task::class)->find($id);

        $form = $this->createFormBuilder($task)
            ->add("submit", SubmitType::class, [
                "label" => "Подтвердить",
                "attr" => ["class" => "btn btn-primary"]
            ])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $task = $form->getData();

            $manager->remove($task);
            $manager->flush();

            return $this->redirectToRoute("task-list");
        }

        return $this->render("admin/task/delete.html.twig", [
            "form" => $form->createView()
        ]);
    }
}
