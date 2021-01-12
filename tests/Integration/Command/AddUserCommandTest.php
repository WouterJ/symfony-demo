<?php

use App\Factory\UserFactory;
use PHPUnit\Framework\Assert;

use function Symfony\Bridge\Pest\run;

exec('stty 2>&1', $output, $exitcode);
$isSttySupported = 'Windows' === PHP_OS_FAMILY || 0 === $exitcode;

define('USER_DATA', [
    'username' => 'chuck_norris',
    'password' => 'foobar',
    'email' => 'chuck@norris.com',
    'full-name' => 'Chuck Norris',
]);

test('run non-interactive', function (bool $isAdmin) {
    $input = USER_DATA;
    if ($isAdmin) {
        $input['--admin'] = 1;
    }

    run('app:add-user', $input);

    assertUserCreated($isAdmin);
})->with([true, false])->skip(!$isSttySupported, '`stty` is required to test this command.');

test('run interactive', function (bool $isAdmin) {
    run(
        'app:add-user',
        // these are the arguments (only 1 is passed, the rest are missing)
        $isAdmin ? ['--admin' => 1] : [],
        // these are the responses given to the questions asked by the command
        // to get the value of the missing required arguments
        array_values(USER_DATA)
    );

    assertUserCreated($isAdmin);
})->with([true, false])->skip(!$isSttySupported, '`stty` is required to test this command.');

function assertUserCreated(bool $isAdmin): void {
    UserFactory::repository()->assertExists([
        'fullName' => USER_DATA['full-name'],
        'username' => USER_DATA['username'],
    ]);

    $user = UserFactory::repository()->find(['fullName' => USER_DATA['full-name'], 'username' => USER_DATA['username']])->object();
    Assert::assertSame($isAdmin ? ['ROLE_ADMIN'] : ['ROLE_USER'], $user->getRoles());
}
