<?php

namespace App\Controller;


use App\Entity\Status;
use Doctrine\Common\Persistence\ObjectManager;
use const Grpc\STATUS_ABORTED;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class StatusController extends AbstractController
{
    public function list(ObjectManager $manager)
    {
        $statuses = $manager->getRepository(Status::class)->findAll();

        return $this->render("admin/status/list.html.twig", [
            "statuses" => $statuses
        ]);
    }

    public function add(Request $request, ObjectManager $manager)
    {
        $status = new Status();

        $form = $this->createFormBuilder($status)
            ->add("title", TextType::class, [
                "label" => "Название статуса",
                "attr" => ["class" => "form-control"]
            ])
            ->add("save", SubmitType::class, [
                "label" => "Добавить",
                "attr" => ["class" => "btn btn-primary"]
            ])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $status = $form->getData();

            $check = $manager->getRepository(Status::class)->findBy(["title" => $status->getTitle()]);

            if ($check) {
                $form->addError(new FormError("Такой статус уже существует"));
            } else {
                $manager->persist($status);
                $manager->flush();

                return $this->redirectToRoute("status-list");
            }
        }

        return $this->render("admin/status/add.html.twig", [
            "form" => $form->createView()
        ]);
    }

    public function edit($id, ObjectManager $manager, Request $request)
    {
        $status = $manager->getRepository(Status::class)->find($id);

        $form = $this->createFormBuilder($status)
            ->add("title", TextType::class, [
                "label" => "Название статуса",
                "attr" => ["class" => "form-control"]
            ])
            ->add("save", SubmitType::class, [
                "label" => "Сохранить",
                "attr" => ["class" => "btn btn-primary"]
            ])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $status = $form->getData();

            $check = $manager->getRepository(Status::class)->findStatusByTitleWithNotMatchId($id, $status->getTitle());

            if ($check) {
                $form->addError(new FormError("Такой статус уже существует"));
            } else {
                $manager->persist($status);
                $manager->flush();

                return $this->redirectToRoute("status-list");
            }
        }

        return $this->render("admin/status/edit.html.twig", [
            "form" => $form->createView()
        ]);
    }

    public function delete($id, ObjectManager $manager, Request $request)
    {
        $status = $manager->getRepository(Status::class)->find($id);

        $form = $this->createFormBuilder($status)
            ->add("submit", SubmitType::class, [
                "label" => "Подтвердить",
                "attr" => ["class" => "btn btn-primary"]
            ])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $status = $form->getData();

            $manager->remove($status);
            $manager->flush();

            return $this->redirectToRoute("status-list");
        }

        return $this->render("admin/status/delete.html.twig", [
            "form" => $form->createView()
        ]);
    }
}
