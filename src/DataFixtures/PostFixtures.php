<?php

namespace App\DataFixtures;

use App\Entity\Post;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class PostFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create("fr_FR");
        for ($i=0;$i < 20;$i++) {
            $post = new Post();
            $post->setTitle($faker->words($faker->numberBetween(1,10),true,));
            $post->setContent($faker->paragraphs(3,true));
            $post->setCreatedAt($faker->dateTimeBetween("-6 months"));
            $categoryRef = $faker->numberBetween(1,10);
            $post->setCategory($this->getReference("category_".$categoryRef));
            $manager->persist($post);
        }
        $manager->flush();
    }

    public function getDependencies()
    {
        // Retourne un tableau de fixtures
        return[
            CategoryFixtures::class
        ];
    }
}