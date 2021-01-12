<?php

namespace Zenstruck\Browser\Pest;

use Symfony\Component\Security\Core\User\UserInterface;

function visit(string $url) {
    return test()->browser()->visit($url);
}

function get(string $url, $options = []) {
    return test()->browser()->get($url, $options);
}

function post(string $url, $options = []) {
    return test()->browser()->post($url, $options);
}

function delete(string $url, $options = []) {
    return test()->browser()->delete($url, $options);
}

function put(string $url, $options = []) {
    return test()->browser()->put($url, $options);
}

function request(string $method, string $url, $options = []) {
    return test()->browser()->request($method, $url, $options);
}

function actingAs(UserInterface $user, ?string $firewall = null) {
    return test()->browser()->actingAs($user, $firewall);
}
