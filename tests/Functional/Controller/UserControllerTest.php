<?php

use App\Factory\UserFactory;
use function Zenstruck\Browser\Pest\{actingAs, visit};

test('access denied for anonymous users', function (string $url) {
    visit($url)->assertOn('http://localhost/en/login');
})->with([
    '/en/profile/edit',
    '/en/profile/change-password'
]);

test('edit user', function () {
    $user = UserFactory::new()->create(['email' => 'jane_admin@symfony.com'])->object();

    $newUserEmail = 'admin_jane@symfony.com';
    actingAs($user)
        ->visit('/en/profile/edit')
        ->fillField('Email', $newUserEmail)
        ->click('Save changes')
        ->assertOn('/en/profile/edit')
    ;

    UserFactory::repository()->assertExists(['username' => $user->getUsername(), 'email' => $newUserEmail]);
});

test('change password', function () {
    $user = UserFactory::new()->create()->object();

    $newUserPassword = 'new-password';
    actingAs($user)->interceptRedirects()
        ->visit('/en/profile/change-password')
        ->fillField('Current password', 'kitten')
        ->fillField('New password', $newUserPassword)
        ->fillField('Confirm password', $newUserPassword)
        ->click('Save changes')
        ->assertRedirectedTo('/en/logout', 1)
    ;
});
