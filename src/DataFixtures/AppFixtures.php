<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\DataFixtures;

use App\Entity\Comment;
use App\Entity\Post;
use App\Entity\Tag;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\String\Slugger\SluggerInterface;
use function Symfony\Component\String\u;

class AppFixtures extends Fixture
{
    private $passwordEncoder;
    private $slugger;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder, SluggerInterface $slugger)
    {
        $this->passwordEncoder = $passwordEncoder;
        $this->slugger = $slugger;
    }

    public function load(ObjectManager $manager): void
    {
        $this->loadUsers($manager);
        $this->loadTags($manager);
        $this->loadPosts($manager);
    }

    private function loadUsers(ObjectManager $manager): void
    {
        foreach ([
            ['fullname' => 'Jane Doe', 'username' => 'jane_admin', 'email' => 'jane_admin@symfony.com', 'roles' => ['ROLE_ADMIN']],
            ['fullname' => 'Tom Doe','username' =>  'tom_admin', 'email' => 'tom_admin@symfony.com', 'roles' => ['ROLE_ADMIN']],
            ['fullname' => 'John Doe', 'username' => 'john_user', 'email', 'john_user@symfony.com'],
        ] as $userData) {
            $user = UserFactory::new()->create($userData);
        }
    }

    private function loadTags(ObjectManager $manager): void
    {
        TagFactory::new()->createMany(9);
    }

    private function loadPosts(ObjectManager $manager): void
    {
        $post = PostFactory::new()->create([
            'title' => $title,
            'publishedAt' => new \DateTime('now - '.$i.'days'),
            'author' => UserFactory::findOrCreate(['username' => 'jane_admin']),
        ]);
        CommentFactory::new()->createMany(4, ['post' => $post]);

        $posts = PostFactory::new()->createMany(5);
        foreach ($posts as $post) {
            CommentFactory::new()->createMany(4, ['post' => $post]);
        }
    }
}
