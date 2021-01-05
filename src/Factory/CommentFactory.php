<?php

namespace App\Factory;

use App\Entity\Comment;
use Zenstruck\Foundry\ModelFactory;
use Zenstruck\Foundry\Proxy;

/**
 * @method static Comment|Proxy findOrCreate(array $attributes)
 * @method static Comment|Proxy random()
 * @method static Comment[]|Proxy[] randomSet(int $number)
 * @method static Comment[]|Proxy[] randomRange(int $min, int $max)
 * @method Comment|Proxy create($attributes = [])
 * @method Comment[]|Proxy[] createMany(int $number, $attributes = [])
 */
final class CommentFactory extends ModelFactory
{
    protected function getDefaults(): array
    {
        return [
            'content' => self::faker()->paragraph(),
        ];
    }

    protected function initialize(): self
    {
        return $this
            ->beforeInstantiate(function(array $attributes): array {
                $attributes['post'] = PostFactory::random();
                $attributes['publishedAt'] = $attributes['publishedAt'] ?? self::faker()->dateTimeBetween($attributes['post']->getPublishedAt());
                $attributes['author'] = UserFactory::random();

                return $attributes;
            })
        ;
    }

    protected static function getClass(): string
    {
        return Comment::class;
    }
}
