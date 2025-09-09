<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Posts;
use App\Entity\Categories;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $hasher;

    public function __construct(UserPasswordHasherInterface $hasher)
    {
        $this->hasher = $hasher;
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        // --- Categories ---
        $categories = [];
        for ($i = 0; $i < 5; $i++) {
            $category = new Categories();
            $category->setNameCategory($faker->word);
            $manager->persist($category);
            $categories[] = $category;
        }

        // --- Users ---
        $users = [];
        for ($i = 0; $i < 10; $i++) {
            $user = new User();
            $user->setEmail($faker->unique()->safeEmail);
            $user->setPseudo($faker->userName);
            $user->setRoles(['ROLE_USER']);

            $hashedPassword = $this->hasher->hashPassword($user, 'password');
            $user->setPassword($hashedPassword);

            $manager->persist($user);
            $users[] = $user;
        }

        // --- Posts ---
        for ($i = 0; $i < 20; $i++) {
            $post = new Posts();
            $post->setTitle($faker->sentence(6, true));
            $post->setSubtitle($faker->sentence(10, true));
            $post->setContent($faker->paragraphs(5, true));
            $post->setContent1($faker->paragraphs(3, true));
            $post->setContent2($faker->paragraphs(3, true));
            $post->setImage($faker->lexify('image_????.jpg'));
            $post->setIsPublished($faker->boolean());

            // random category (1 à 5)
            $post->setCategory($faker->randomElement($categories));

            // random author (1 à 10)
            $post->setAuteur($faker->randomElement($users));

            $manager->persist($post);
        }

        $manager->flush();
    }
}
