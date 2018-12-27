<?php

namespace App\Controller;

use App\Entity\Status;
use App\Entity\Task;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class MainController extends AbstractController
{
    public function index(ObjectManager $manager)
    {
        $tasks = $manager->getRepository(Task::class)->findAll();

        return $this->render("index.html.twig", [
            "tasks" => $tasks
        ]);
    }

    public function task_view($id, ObjectManager $manager, Request $request)
    {
        $task = $manager->getRepository(Task::class)->find($id);

        $form = $this->createFormBuilder($task)
            ->add("status", EntityType::class, [
                "class" => Status::class,
                "choice_label" => "title",
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

            $manager->persist($task);
            $manager->flush();

            return $this->redirectToRoute("index");
        }

        return $this->render("task/view.html.twig", [
            "task" => $task,
            "form" => $form->createView()
        ]);
    }
}
