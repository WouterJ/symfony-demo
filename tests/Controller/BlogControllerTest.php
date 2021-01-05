<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Tests\Controller;

use App\Factory\PostFactory;
use App\Factory\UserFactory;
use App\Pagination\Paginator;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DomCrawler\Crawler;
use Zenstruck\Browser\Test\HasBrowser;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

/**
 * Functional test for the controllers defined inside BlogController.
 *
 * See https://symfony.com/doc/current/testing.html#functional-tests
 *
 * Execute the application tests using this command (requires PHPUnit to be installed):
 *
 *     $ cd your-symfony-project/
 *     $ ./vendor/bin/phpunit
 */
class BlogControllerTest extends KernelTestCase
{
    use ResetDatabase, Factories, HasBrowser;

    public function testIndex(): void
    {
        PostFactory::new()->createMany(15);

        $this->kernelBrowser()
            ->visit('/en/blog/')
            ->assertSuccessful()
            ->assertElementCount('article.post', Paginator::PAGE_SIZE)
        ;
    }

    public function testRss(): void
    {
        PostFactory::new()->createMany(15);

        $response = $this->kernelBrowser()
            ->get('/en/blog/rss.xml')
            ->assertHeaderEquals('Content-Type', 'text/xml; charset=UTF-8')
            ->inner()->getResponse()
        ;

        // @todo besides adding a ->response() helper, maybe also add a ->crawler() helper? (Mink doesn't support XML responses)
        $dom = new \DOMDocument();
        $dom->loadXML($response->getContent());
        $crawler = new Crawler($dom);
        $this->assertCount(
            Paginator::PAGE_SIZE,
            $crawler->filter('item'),
            'The xml file displays the right number of posts.'
        );
    }

    /**
     * This test changes the database contents by creating a new comment. However,
     * thanks to the DAMADoctrineTestBundle and its PHPUnit listener, all changes
     * to the database are rolled back when this test completes. This means that
     * all the application tests begin with the same database contents.
     */
    public function testNewComment(): void
    {
        $user = UserFactory::new()->create()->object();
        PostFactory::new()->create(['title' => 'Hello world']);

        $this->kernelBrowser()->actingAs($user)
            ->visit('/en/blog/posts/hello-world')
            ->fillField('Content', 'Hi, Symfony!')
            ->click('Publish comment')
            ->assertSeeIn('.post-comment', 'Hi, Symfony!')
        ;
    }

    public function testAjaxSearch(): void
    {
        PostFactory::new()->create([
            'title' => 'Lorem ipsum dolor sit amet consectetur adipiscing elit',
            'author' => UserFactory::new()->create(['fullName' => 'Jane Doe']),
        ]);

        $this->kernelBrowser()
            ->get('/en/blog/search', [
                // @todo rename "parameters" to "query" to be consistent with Symfony's naming?
                'parameters' => ['q' => 'Lorem'],
                'ajax' => true,
            ])
            ->assertJson()
            ->assertJsonMatches('[0].title', 'Lorem ipsum dolor sit amet consectetur adipiscing elit')
            ->assertJsonMatches('[0].author', 'Jane Doe')
        ;
    }
}
