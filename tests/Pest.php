<?php

use App\Factory\UserFactory;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Browser\Test\HasKernelBrowser;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;
use function Zenstruck\Browser\Pest\actingAs;

uses(KernelTestCase::class, ResetDatabase::class, Factories::class, HasKernelBrowser::class)->in('Functional');
uses(KernelTestCase::class, ResetDatabase::class, Factories::class)->in('Integration');

function actingAsAdmin() {
    $user = UserFactory::repository()->findBy([], null, 1);
    $user = [] !== $user ? $user[0] : null;
    if (!$user || !\in_array('ROLE_ADMIN', $user->getRoles())) {
        $user = UserFactory::new()->create(['roles' => ['ROLE_ADMIN']]);
    }

    return actingAs($user->object());
}
