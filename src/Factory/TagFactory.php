<?php

namespace App\Factory;

use App\Entity\Tag;
use Zenstruck\Foundry\ModelFactory;
use Zenstruck\Foundry\Proxy;

/**
 * @method static Tag|Proxy findOrCreate(array $attributes)
 * @method static Tag|Proxy random()
 * @method static Tag[]|Proxy[] randomSet(int $number)
 * @method static Tag[]|Proxy[] randomRange(int $min, int $max)
 * @method Tag|Proxy create($attributes = [])
 * @method Tag[]|Proxy[] createMany(int $number, $attributes = [])
 */
final class TagFactory extends ModelFactory
{
    protected function getDefaults(): array
    {
        return [
            'name' => self::faker()->word(),
        ];
    }

    protected static function getClass(): string
    {
        return Tag::class;
    }
}
