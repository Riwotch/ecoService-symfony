<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use App\Entity\Product;
use App\Entity\Category;

class ProductFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $faker = Factory::create('fr_FR');
        $events[] = new Product();
        for ($i = 0; $i < 30; $i++) {
            $randomDate = new \DateTime();
            $event = new Product();
            try {
                $randomDate = new \DateTime($faker->date('Y-m-d'));
                $randomDate = new \DateTime($faker->dateTimeInInterval('-10 days', '+0 days', null)->format('Y-m-d'));
            } catch (\Exception $e) {
                $randomDate = new \DateTime("2021-05-15");
            }

            $category = $this->getReference('category_'. $faker->numberBetween(1,6));

            $event
                ->setName($faker->sentence(3, true))
                ->setModifiedAt($randomDate)
                ->setCreatedAt($randomDate )
                ->setImg("img" . $faker->numberBetween(1, 10) . ".jpg")
                ->setPrice($faker->randomFloat(2, 5, 30))
                ->setQty($faker->numberBetween(0,60))
                ->setDescription($faker->realText($faker->numberBetween(101, 549)))
                ->setCategory($category);

            $manager->persist($event);
        }
        $manager->flush();
    }

    public function getDependencies(){
        return [CategoryFixtures::class];
    }


}
