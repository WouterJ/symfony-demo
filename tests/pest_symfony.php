<?php

namespace Symfony\Bridge\Pest;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

function run(string $commandName, array $arguments = [], array $inputs = []): CommandTester {
    $kernel = test()->createKernel();
    $application = new Application($kernel);

    $command = $application->find($commandName);
    $commandTester = new CommandTester($command);

    $commandTester->setInputs($inputs);
    $commandTester->execute($arguments);

    return $commandTester;
}
