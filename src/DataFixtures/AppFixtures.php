<?php

namespace App\DataFixtures;

use App\Entity\Supplies\Measure;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    private function loadMeasures(ObjectManager $manager)
    {
        $mMilliliter = new Measure();
        $mMilliliter
            ->setId(1)
            ->setName('measure_milliliter_name')
            ->setUnit('measure_milliliter_unit')
            ->setPhysicalQuantity('volume');
        $manager->persist($mMilliliter);

        $mLiter = new Measure();
        $mLiter
            ->setId(2)
            ->setName('measure_liter_name')
            ->setUnit('measure_liter_unit')
            ->setPhysicalQuantity('volume');
        $manager->persist($mLiter);

        $mGram = new Measure();
        $mGram
            ->setId(3)
            ->setName('measure_gram_name')
            ->setUnit('measure_gram_unit')
            ->setPhysicalQuantity('mass');
        $manager->persist($mGram);

        $mKilogram = new Measure();
        $mKilogram
            ->setId(4)
            ->setName('measure_kilogram_name')
            ->setUnit('measure_kilogram_unit')
            ->setPhysicalQuantity('mass');
        $manager->persist($mKilogram);

        $mPiece = new Measure();
        $mPiece
            ->setId(5)
            ->setName('measure_piece_name')
            ->setUnit('measure_piece_unit')
            ->setPhysicalQuantity('piece');
        $manager->persist($mPiece);
    }

    public function load(ObjectManager $manager)
    {
        $this->loadMeasures($manager);

        $manager->flush();
    }
}
