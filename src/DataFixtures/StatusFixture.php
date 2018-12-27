<?php

namespace App\DataFixtures;


use App\Entity\Status;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class StatusFixture extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $statuses = [
            "Новый",
            "На выполнении",
            "Выполнено"
        ];

        foreach ($statuses as $s) {
            $status = new Status();
            $status->setTitle($s);
            $manager->persist($status);
        }

        $manager->flush();
    }
}
