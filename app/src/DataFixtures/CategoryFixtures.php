<?php

namespace App\DataFixtures;

use App\Entity\Category;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CategoryFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $categories = [
            ['name' => 'Conseils', 'color' => '#F59E0B', 'description' => 'Conseils et astuces'],
            ['name' => 'Déco', 'color' => '#EC4899', 'description' => 'Décoration intérieure'],
            ['name' => 'Habits', 'color' => '#8B5CF6', 'description' => 'Vêtements et mode'],
            ['name' => 'Meubles', 'color' => '#10B981', 'description' => 'Mobilier et ameublement'],
            ['name' => 'Appareils', 'color' => '#3B82F6', 'description' => 'Électroménager et high-tech'],
            ['name' => 'Autres', 'color' => '#6B7280', 'description' => 'Divers'],
        ];

        foreach ($categories as $data) {
            $category = new Category();
            $category->setName($data['name']);
            $category->setColor($data['color']);
            $category->setDescription($data['description']);
            $manager->persist($category);
        }

        $manager->flush();
    }
}
