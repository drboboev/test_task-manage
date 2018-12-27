<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ExpressionLanguage\Tests\Node\Obj;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserController extends AbstractController
{
    public function list(ObjectManager $manager)
    {
        $users = $manager->getRepository(User::class)->findAll();

        return $this->render("admin/user/list.html.twig", [
            "users" => $users
        ]);
    }

    public function view($id, ObjectManager $manager)
    {
        $user = $manager->getRepository(User::class)->find($id);

        return $this->render("admin/user/view.html.twig", [
            "user" => $user
        ]);
    }

    public function add(ObjectManager $manager, Request $request, UserPasswordEncoderInterface $encoder)
    {
        $user = new User();

        $form = $this->createFormBuilder($user)
            ->add("username", TextType::class, [
                "label" => "Имя пользователя",
                "attr" => ["class" => "form-control"]
            ])
            ->add("password", PasswordType::class, [
                "label" => "Пароль",
                "attr" => ["class" => "form-control"]
            ])
            ->add("save", SubmitType::class, [
                "label" => "Добавить",
                "attr" => ["class" => "btn btn-primary"]
            ])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $form->getData();

            $check = $manager->getRepository(User::class)->findBy(["username" => $user->getUsername()]);

            if ($check) {
                $form->addError(new FormError("Такой пользователь уже существует"));
            } else {
                $user->setPassword(
                    $encoder->encodePassword($user, $user->getPassword())
                );

                $manager->persist($user);
                $manager->flush();

                return $this->redirectToRoute("user-list");
            }
        }

        return $this->render("admin/user/add.html.twig", [
            "form" => $form->createView()
        ]);
    }

    public function edit($id, ObjectManager $manager, Request $request, UserPasswordEncoderInterface $encoder)
    {
        $user = $manager->getRepository(User::class)->find($id);

        $form = $this->createFormBuilder($user)
            ->add("username", TextType::class, [
                "label" => "Имя пользователя",
                "attr" => ["class" => "form-control"]
            ])
            ->add("save", SubmitType::class, [
                "label" => "Добавить",
                "attr" => ["class" => "btn btn-primary"]
            ])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $form->getData();

            $check = $manager->getRepository(User::class)->findUserByTitleWithNotMatchId($id, $user->getUsername());

            if ($check) {
                $form->addError(new FormError("Такой пользователь уже существует"));
            } else {
                $manager->persist($user);
                $manager->flush();

                return $this->redirectToRoute("user-list");
            }
        }

        return $this->render("admin/user/edit.html.twig", [
            "form" => $form->createView()
        ]);
    }

    public function delete()
    {
        return $this->render("admin/user/delete.html.twig");
    }
}
