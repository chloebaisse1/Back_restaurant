<?php

namespace App\DataFixtures;

use App\Entity\Restaurant;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Exception;

class RestaurantFixtures extends Fixture
{
    public const RESTAURANTS_REFERENCE = "restaurant";
    public const RESTAURANT_NB_TUPLES = 20;
    /**
     * @throws Exception
     */
    public function load(ObjectManager $manager): void
    {
        for($i = 1; $i <= self::RESTAURANT_NB_TUPLES; $i++) {
            $restaurant = (new Restaurant())
                ->setName("Restaurant n° $i")
                ->setDescription("Description restaurant n° $i")
                ->setAmOpeningTime([])
                ->setPmOpeningTime([])
                ->setMaxGuest(random_int(10, 50))
                ->setCreatedAt(new DateTimeImmutable());

            $manager->persist($restaurant);
            $this->addReference(self::RESTAURANTS_REFERENCE . $i, $restaurant);
        }

      $manager->flush();
    }
}
