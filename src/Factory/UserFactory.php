<?php

namespace App\Factory;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Zenstruck\Foundry\RepositoryProxy;
use Zenstruck\Foundry\ModelFactory;
use Zenstruck\Foundry\Proxy;

/**
 * @method static User|Proxy findOrCreate(array $attributes)
 * @method static User|Proxy random()
 * @method static User[]|Proxy[] randomSet(int $number)
 * @method static User[]|Proxy[] randomRange(int $min, int $max)
 * @method static UserRepository|RepositoryProxy repository()
 * @method User|Proxy create($attributes = [])
 * @method User[]|Proxy[] createMany(int $number, $attributes = [])
 */
final class UserFactory extends ModelFactory
{
    private $passwordEncoder;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        parent::__construct();

        $this->passwordEncoder = $passwordEncoder;
    }

    protected function getDefaults(): array
    {
        return [
            'fullName' => self::faker()->firstName.' '.self::faker()->lastName,
            'username' => self::faker()->userName,
            'email' => self::faker()->safeEmail,
            'password' => 'kitten',
            'roles' => ['ROLE_USER'],
        ];
    }

    protected function initialize(): self
    {
        return $this
            ->afterInstantiate(function(User $user, array $attributes) {
                $user->setPassword($this->passwordEncoder->encodePassword($user, $attributes['password']));
            })
        ;
    }

    protected static function getClass(): string
    {
        return User::class;
    }
}
