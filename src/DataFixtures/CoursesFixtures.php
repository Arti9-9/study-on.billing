<?php

namespace App\DataFixtures;

use App\Entity\Course;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CoursesFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $courses = [
            [
                'code' => 'PPBIB',
                'title' => 'Программирование на Python (базовый)',
                'type' => 2,
                'price' => 2000,
            ],
            [
                'code' => 'PPBIB3',
                'title' => 'Программирование на Python (базовый)',
                'type' => 2,
                'price' => 2000,
            ],
            [
                'code' => 'PPBI',
                'title' => 'Программирование на Python (продвинутый)',
                'type' => 1,
                'price' => 2000,
            ],
            [
                'code' => 'PPBI2',
                'title' => 'Программирование на Python 2',
                'type' => 3,
                'price' => 2000,
            ],
            [
                'code' => 'MSCB',
                'title' => 'Математическая статистика (базовый)',
                'type' => 2,
                'price' => 1000,
            ],
            [
                'code' => 'MSC',
                'title' => 'Математическая статистика',
                'type' => 3,
                'price' => 1000,
            ],
            [
                'code' => 'CAMPB',
                'title' => 'Курс подготовки вожатых (базовый)',
                'type' => 2,
                'price' => 3000,
            ],
            [
                'code' => 'CAMP',
                'title' => 'Курс подготовки вожатых (продвинутый)',
                'type' => 1,
                'price' => 3000,
            ],
        ];

        foreach ($courses as $course) {
            $newCourse = new Course();
            $newCourse->setCode($course['code']);
            $newCourse->setType($course['type']);
            $newCourse->setTitle($course['title']);
            if (isset($course['price'])) {
                $newCourse->setPrice($course['price']);
            }
            $manager->persist($newCourse);
        }
        $manager->flush();
    }
}