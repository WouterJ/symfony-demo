<?php

use App\Factory\PostFactory;
use App\Factory\UserFactory;
use App\Pagination\Paginator;
use PHPUnit\Framework\Assert;
use Symfony\Component\DomCrawler\Crawler;
use Zenstruck\Browser\Response;
use function Zenstruck\Browser\Pest\{visit, get, actingAs};

beforeEach(fn() => PostFactory::new()->createMany(14));

visit('/en/blog/')
    ->assertSuccessful()
    ->assertElementCount('article.post', Paginator::PAGE_SIZE)
;

get('/en/blog/rss.xml')
    ->assertHeaderEquals('Content-Type', 'text/xml; charset=UTF-8')
    ->assertResponse(function (Response $response) {
        $crawler = new Crawler();
        $crawler->addXmlContent($response->body());
        Assert::assertCount(
            Paginator::PAGE_SIZE,
            $crawler->filter('item'),
            'The xml file displays the right number of posts.'
        );
    })
;

test('adding comments', function () {
    $user = UserFactory::new()->create()->object();
    PostFactory::new()->create(['title' => 'Hello world']);

    actingAs($user)
        ->visit('/en/blog/posts/hello-world')
        ->fillField('Content', 'Hi, Symfony!')
        ->click('Publish comment')
        ->assertSeeIn('.post-comment', 'Hi, Symfony!')
    ;
});
