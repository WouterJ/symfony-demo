<?php

namespace App\Factory;

use App\Entity\Post;
use App\Repository\PostRepository;
use Symfony\Component\String\Slugger\SluggerInterface;
use Zenstruck\Foundry\Instantiator;
use Zenstruck\Foundry\RepositoryProxy;
use Zenstruck\Foundry\ModelFactory;
use Zenstruck\Foundry\Proxy;

/**
 * @method static Post|Proxy findOrCreate(array $attributes)
 * @method static Post|Proxy random()
 * @method static Post[]|Proxy[] randomSet(int $number)
 * @method static Post[]|Proxy[] randomRange(int $min, int $max)
 * @method static PostRepository|RepositoryProxy repository()
 * @method Post|Proxy create($attributes = [])
 * @method Post[]|Proxy[] createMany(int $number, $attributes = [])
 */
final class PostFactory extends ModelFactory
{
    private $slugger;

    public function __construct(SluggerInterface $slugger)
    {
        parent::__construct();

        $this->slugger = $slugger;
    }

    protected function getDefaults(): array
    {
        return [
            'title' => self::faker()->sentence(),
            'summary' => self::faker()->paragraph(),
            'content' => self::faker()->paragraphs(3, true),
            'publishedAt' => self::faker()->dateTimeBetween('-1 month'),
        ];
    }

    protected function initialize(): self
    {
        return $this
            ->instantiateWith((new Instantiator())->alwaysForceProperties(['id']))
            ->beforeInstantiate(function(array $attributes): array {
                $attributes['slug'] = $attributes['slug'] ?? $this->slugger->slug($attributes['title'])->lower();

                // @todo allow UserFactory::random() to create 1 User if none is available?
                if (!isset($attributes['author'])) {
                    if (UserFactory::repository()->count([]) > 0) {
                        $attributes['author'] = UserFactory::random();
                    } else {
                        $attributes['author'] = UserFactory::new()->create();
                    }
                }

                if (!isset($attributes['tags'])) {
                    if (TagFactory::repository()->count([]) > 0) {
                        $attributes['tags'] = array_map([TagFactory::class, 'random'], array_fill(0, random_int(2, 4), null));
                    } else {
                        $attributes['tags'] = TagFactory::new()->createMany(random_int(2, 4));
                    }
                }

                return $attributes;
            })
        ;
    }

    protected static function getClass(): string
    {
        return Post::class;
    }
}
