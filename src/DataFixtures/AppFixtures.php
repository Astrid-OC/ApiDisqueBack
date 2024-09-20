<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Disque;
use App\Entity\Chansons;
use App\Entity\Chanteur;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private $userPasswordHasher;

    public function __construct(UserPasswordHasherInterface $userPasswordHasher)
    {
        $this->userPasswordHasher = $userPasswordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        
        //Création d'unn user normal
        $user = new User();
        $user->setEmail("user@disqueapi.com");
        $user->setRoles(["ROLE_USER"]);

        $user->setPassword($this->userPasswordHasher->hashPassword($user, "password"));
        $manager->persist($user);

        //Création d'user admin
        $userAdmin = new User();
        $userAdmin->setEmail("admin@disqueapi.com");
        $userAdmin->setRoles(["ROLE_ADMIN"]);
        //cette méthode permet de retourner le mdp encodé de l'uti.
        $userAdmin->setPassword($this->userPasswordHasher->hashPassword($userAdmin, "password"));
        $manager->persist($userAdmin);

        //Création des chanteurs.
        $listChanteur = [];
        for ($i=0; $i < 10; $i++) 
        { 
            //céation du chanteurs lui-même.
            $chanteur = new Chanteur();
            $chanteur->setNomChanteur("NomChanteur" . $i);
            $manager->persist($chanteur);
            //on sauvegarde le chanteur créé dans un tableau.
            $listChanteur[] = $chanteur;
        }

        //Création des chansons.
        $listChanson = [];
        for ($i=0; $i < 15; $i++) 
        { 
            //création d'une chanson.
            $chanson = new Chansons();
            $chanson->setTitre('TitreChanson' . $i);
            $chanson->setDuree('3' . $i);
            $manager->persist($chanson);
            //on sauvegarde la chanson créé dans un tableau.
            $listChanson[] = $chanson;
        } 
        
        //Création des disques.
        for ($i=0; $i < 20; $i++) 
        { 
            $disque = new Disque();
            $disque->setNomDisque('Disque' . $i);
            $disque->setDescription("Description du disque" . $i);
            //on lie le disque à un chanteur pris au hasard dans le tableau des chanteurs.
            $disque->setChanteur($listChanteur[array_rand($listChanteur)]);
            //on lie le disque à une chanson prise au hasard dans le tab des chansons.
            $disque->addChanson($listChanson[array_rand($listChanson)]);
            $manager->persist($disque);
        }       

        $manager->flush();
    }

}
