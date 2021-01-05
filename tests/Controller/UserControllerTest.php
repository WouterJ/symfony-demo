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

use App\Factory\UserFactory;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Browser\Test\HasBrowser;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

/**
 * Functional test for the controllers defined inside the UserController used
 * for managing the current logged user.
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
class UserControllerTest extends KernelTestCase
{
    use ResetDatabase, Factories, HasBrowser;

    /**
     * @dataProvider getUrlsForAnonymousUsers
     */
    public function testAccessDeniedForAnonymousUsers(string $httpMethod, string $url): void
    {
        $this->kernelBrowser()
            ->request($httpMethod, $url)
            ->assertOn('http://localhost/en/login');
    }

    public function getUrlsForAnonymousUsers(): ?\Generator
    {
        yield ['GET', '/en/profile/edit'];
        yield ['GET', '/en/profile/change-password'];
    }

    public function testEditUser(): void
    {
        $user = UserFactory::new()->create(['email' => 'jane_admin@symfony.com'])->object();

        $newUserEmail = 'admin_jane@symfony.com';
        $this->kernelBrowser()->actingAs($user)
            ->visit('/en/profile/edit')
            ->fillField('Email', $newUserEmail)
            ->click('Save changes')
            ->assertOn('/en/profile/edit')
        ;

        UserFactory::repository()->assertExists(['username' => $user->getUsername(), 'email' => $newUserEmail]);
    }

    public function testChangePassword(): void
    {
        $user = UserFactory::new()->create()->object();

        $newUserPassword = 'new-password';
        $this->kernelBrowser()->actingAs($user)->interceptRedirects()
            ->visit('/en/profile/change-password')
            ->fillField('Current password', 'kitten')
            ->fillField('New password', $newUserPassword)
            ->fillField('Confirm password', $newUserPassword)
            ->click('Save changes')
            ->assertRedirectedTo('/en/logout', 1)
        ;
    }
}
