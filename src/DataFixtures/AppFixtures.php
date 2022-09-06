<?php

namespace App\DataFixtures;

use App\Entity\Comment;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use App\Entity\Post;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class AppFixtures extends Fixture
{

    private $passwordEncoder;
    private $faker;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
        $this->faker = Factory::create();
    }

    public function load(ObjectManager $manager): void
    {
        $this->loadUser($manager);
        $this->loadPost($manager);
        $this->loadComment($manager);
    }

    public function loadPost(ObjectManager $manager)
    {

        for ($i = 0; $i < 100; $i++) {

            $post = new Post;

            $post->setTitle($this->faker->sentence());
            $post->setAuthor($this->getReference('user_admin_' . rand(0, 9)));
            $post->setContent($this->faker->realText());
            $post->setPublished(new \DateTime());
            $post->setSlug($this->faker->slug());

            $this->addReference("post_$i", $post);

            $manager->persist($post);
        }

        $manager->flush();
    }

    public function loadUser(ObjectManager $manager)
    {
        $roles = [User::ROLE_ADMIN, User::ROLE_COMMENTATOR, User::ROLE_EDITOR, User::ROLE_SUPERADMIN, User::ROLE_WRITER];

        for ($i = 0; $i < 10; $i++) {
            $user = new User;

            $user->setUsername($this->faker->userName);
            $user->setName($this->faker->name);
            $user->setPassword($this->passwordEncoder->encodePassword($user, 'admin'));
            $user->setRoles([$roles[rand(0, 4)]]);

            $this->addReference("user_admin_$i", $user);

            $manager->persist($user);
        }

        $manager->flush();
    }

    public function loadComment(ObjectManager $manager)
    {

        for ($i = 0; $i < 1000; $i++) {

            $comment = new Comment;

            $comment->setContent($this->faker->sentence());
            $comment->setAuthor($this->getReference('user_admin_' . rand(0, 9)));
            $comment->setPost($this->getReference('post_' . rand(0, 99)));
            $comment->setPublished(new \DateTime());

            $manager->persist($comment);
        }

        $manager->flush();
    }
}
