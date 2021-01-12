<?php

use App\Factory\PostFactory;
use function Zenstruck\Browser\Pest\visit;

test('public urls', function (string $url) {
    visit($url)->assertSuccessful();
})->with(['/', '/en/blog/', '/en/login/']);

test('public blog post', function () {
    PostFactory::new()->create(['slug' => 'hello-world']);

    visit('/en/blog/posts/hello-world')
        ->assertSuccessful()
    ;
});

test('secure urls', function (string $url) {
    PostFactory::findOrCreate(['id' => 1]);

    visit($url)
        ->assertOn('http://localhost/en/login')
    ;
})->with([
    '/en/admin/post/',
    '/en/admin/post/new',
    '/en/admin/post/1',
    '/en/admin/post/1/edit',
]);
