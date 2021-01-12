<?php

use App\Factory\PostFactory;
use App\Factory\UserFactory;
use Symfony\Component\HttpFoundation\Response;
use function Zenstruck\Browser\Pest\actingAs;

test('access denied for regular users', function (string $httpMethod, string $url) {
    $user = UserFactory::new()->create()->object();

    actingAs($user)
        ->request($httpMethod, $url)
        ->assertStatus(Response::HTTP_FORBIDDEN)
    ;
})->with([
    ['GET', '/en/admin/post/'],
    ['GET', '/en/admin/post/1'],
    ['GET', '/en/admin/post/1/edit'],
    ['POST', '/en/admin/post/1/delete'],
]);

test('backend home page', function () {
    actingAsAdmin()->visit('/en/admin/post/')
        ->assertSuccessful()
        ->assertSeeElement('body#admin_post_index #main tbody tr')
    ;
});

test('new post', function () {
    $postTitle = 'Blog Post Title';
    $postSummary = 'Some summary';
    $postContent = 'Some blog post content';

    actingAsAdmin()->visit('/en/admin/post/new')
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
});

test('new duplicated post', function () {
    $postTitle = 'Blog Post Title';
    $postSummary = 'Some summary';
    $postContent = 'Some blog post content';

    PostFactory::new()->create([
        'title' => $postTitle,
        'summary' => $postSummary,
        'content' => $postContent,
    ]);

    actingAsAdmin()->visit('/en/admin/post/new')
        ->fillField('Title', $postTitle)
        ->fillField('Summary', $postSummary)
        ->fillField('Content', $postContent)
        ->click('Create post')
        ->assertSee('This title was already used in another blog post, but they must be unique.')
        ->assertSeeIn('form .form-group.has-error label', 'Title')
    ;
});

test('show post', function () {
    $post = PostFactory::new()->create([
        'author' => UserFactory::new()->create(['roles' => ['ROLE_ADMIN']]),
    ]);

    actingAsAdmin()->visit('/en/admin/post/'.$post->getId())
        ->assertSuccessful()
    ;
});

test('edit post', function () {
    $post = PostFactory::new()->create([
        'author' => UserFactory::new()->create(['roles' => ['ROLE_ADMIN']]),
    ]);

    $newBlogPostTitle = 'New Blog Post Title';
    actingAsAdmin()
        ->visit('/en/admin/post/'.$post->getId().'/edit')
        ->fillField('Title', $newBlogPostTitle)
        ->click('Save changes')
        ->assertOn('/en/admin/post/'.$post->getId().'/edit')
    ;

    PostFactory::repository()->assertExists(['id' => $post->getId(), 'title' => $newBlogPostTitle]);
});
