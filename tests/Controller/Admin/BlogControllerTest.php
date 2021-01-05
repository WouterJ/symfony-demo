<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Tests\Controller\Admin;

use App\Factory\PostFactory;
use App\Factory\UserFactory;
use App\Repository\PostRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Response;
use Zenstruck\Browser\KernelBrowser;
use Zenstruck\Browser\Test\HasBrowser;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

/**
 * Functional test for the controllers defined inside the BlogController used
 * for managing the blog in the backend.
 *
 * See https://symfony.com/doc/current/testing.html#functional-tests
 *
 * Whenever you test resources protected by a firewall, consider using the
 * technique explained in:
 * https://symfony.com/doc/current/testing/http_authentication.html
 *
 * Execute the application tests using this command (requires PHPUnit to be installed):
 *
 *     $ cd your-symfony-project/
 *     $ ./vendor/bin/phpunit
 */
class BlogControllerTest extends KernelTestCase
{
    use ResetDatabase, Factories, HasBrowser;

    private function adminBrowser(): KernelBrowser
    {
        // @todo for some reason, UserFactory::findOrCreate() doesn't work here either
        $user = UserFactory::repository()->findBy([], null, 1);
        $user = [] !== $user ? $user[0] : null;
        if (!$user || !\in_array('ROLE_ADMIN', $user->getRoles())) {
            $user = UserFactory::new()->create(['roles' => ['ROLE_ADMIN']]);
        }

        return $this->kernelBrowser()->actingAs($user->object());
    }

    /**
     * @dataProvider getUrlsForRegularUsers
     */
    public function testAccessDeniedForRegularUsers(string $httpMethod, string $url): void
    {
        $user = UserFactory::new()->create()->object();

        $this->kernelBrowser()->actingAs($user)
            ->request($httpMethod, $url)
            ->assertStatus(Response::HTTP_FORBIDDEN)
        ;
    }

    public function getUrlsForRegularUsers(): ?\Generator
    {
        yield ['GET', '/en/admin/post/'];
        yield ['GET', '/en/admin/post/1'];
        yield ['GET', '/en/admin/post/1/edit'];
        yield ['POST', '/en/admin/post/1/delete'];
    }

    public function testAdminBackendHomePage(): void
    {
        $this->adminBrowser()
            ->visit('/en/admin/post/')
            ->assertSuccessful()
            ->assertSeeElement('body#admin_post_index #main tbody tr')
        ;
    }

    /**
     * This test changes the database contents by creating a new blog post. However,
     * thanks to the DAMADoctrineTestBundle and its PHPUnit listener, all changes
     * to the database are rolled back when this test completes. This means that
     * all the application tests begin with the same database contents.
     */
    public function testAdminNewPost(): void
    {
        $postTitle = 'Blog Post Title';
        $postSummary = 'Some summary';
        $postContent = 'Some blog post content';

        $this->adminBrowser()
            ->visit('/en/admin/post/new')
            ->fillField('Title', $postTitle)
            ->fillField('Summary', $postSummary)
            ->fillField('Content', $postContent)
            ->click('Create post')
            ->assertOn('/en/admin/post/')
        ;

        PostFactory::repository()->assertExists([
            'title' => $postTitle,
            'summary' => $postSummary,
            'content' => $postContent,
        ]);
    }

    public function testAdminNewDuplicatedPost(): void
    {
        $postTitle = 'Blog Post Title';
        $postSummary = 'Some summary';
        $postContent = 'Some blog post content';

        PostFactory::new()->create([
            'title' => $postTitle,
            'summary' => $postSummary,
            'content' => $postContent,
        ]);

        $this->adminBrowser()
            ->visit('/en/admin/post/new')
            ->fillField('Title', $postTitle)
            ->fillField('Summary', $postSummary)
            ->fillField('Content', $postContent)
            ->click('Create post')
            ->assertSee('This title was already used in another blog post, but they must be unique.')
            ->assertSeeIn('form .form-group.has-error label', 'Title')
        ;
    }

    public function testAdminShowPost(): void
    {
        $post = PostFactory::new()->create([
            'author' => UserFactory::new()->create(['roles' => ['ROLE_ADMIN']]),
        ]);

        $this->adminBrowser()
            ->visit('/en/admin/post/'.$post->getId())
            ->assertSuccessful()
        ;
    }

    /**
     * This test changes the database contents by editing a blog post. However,
     * thanks to the DAMADoctrineTestBundle and its PHPUnit listener, all changes
     * to the database are rolled back when this test completes. This means that
     * all the application tests begin with the same database contents.
     */
    public function testAdminEditPost(): void
    {
        $post = PostFactory::new()->create([
            'author' => UserFactory::new()->create(['roles' => ['ROLE_ADMIN']]),
        ]);

        $newBlogPostTitle = 'New Blog Post Title';
        $this->adminBrowser()
            ->visit('/en/admin/post/'.$post->getId().'/edit')
            ->fillField('Title', $newBlogPostTitle)
            ->click('Save changes')
            ->assertOn('/en/admin/post/'.$post->getId().'/edit')
        ;

        PostFactory::repository()->assertExists(['id' => $post->getId(), 'title' => $newBlogPostTitle]);
    }

    /**
     * This test changes the database contents by deleting a blog post. However,
     * thanks to the DAMADoctrineTestBundle and its PHPUnit listener, all changes
     * to the database are rolled back when this test completes. This means that
     * all the application tests begin with the same database contents.
     */
    public function testAdminDeletePost(): void
    {
        $this->markTestSkipped('Not working currently');

        $post = PostFactory::new()->create([
            'author' => UserFactory::new()->create(['username' => 'jane_admin', 'password' => 'kitten', 'roles' => ['ROLE_ADMIN']]),
        ]);

        $this->adminBrowser()
            ->visit('/en/admin/post/'.$post->getId())
            // @todo how to match the second button containing "Delete post" ? (and/or how to match this CSS selector)
            ->click('#delete-form button')
            ->assertOn('/en/admin/post')
        ;

        PostFactory::repository()->assertNotExists(['id' => $post->getId()]);
    }
}
