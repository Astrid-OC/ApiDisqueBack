<?php

namespace App\DataFixtures;

use App\Entity\Disque;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        for ($i=0; $i < 20; $i++) 
        { 
            $disque = new Disque();
            
            $manager->persist($disque);
        }
        

        $manager->flush();
    }
}
