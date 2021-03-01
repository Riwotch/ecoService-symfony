<?php

namespace App\DataFixtures;

use App\Entity\Category;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CategoryFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $types = [
            1 => ['name' => 'Produits de beauté'],
            2 => ['name' => 'Cuisine et Maison'],
            3 => ['name' => 'Bricolages'],
            4 => ['name' => 'Hygiène et Santé'],
            5 => ['name' => 'Jardin'],
            6 => ['name' => 'Jeux et Jouets']
        ];

        foreach($types as $key => $value){
            $type = new Category();
            $type->setName($value['name']);
            $manager->persist($type);

            $this->addReference('category_'.$key, $type);
        }
        $manager->flush();
    }
}
