<?php

declare(strict_types=1);

namespace Cds\NettePresenterTests\Utils;

use Nette\Security\IIdentity;

/**
 * Test variant of Nette identity.
 *
 * @method array<string, mixed> getData()
 */
final class TestIdentity implements IIdentity
{
    public mixed $id;

    /** @var array<string, mixed> */
    public array $data = [];

    /** @var array<string> */
    public array $roles = [];

    public function __construct(mixed $id = 'test-user')
    {
        $this->id = $id;
    }

    /**
     * Sets user data value.
     */
    public function __set(string $key, mixed $value): void
    {
        $this->data[$key] = $value;
    }

    public function __isset(string $key): bool
    {
        return isset($this->data[$key]);
    }

    /**
     * {@inheritDoc}
     */
    public function getId(): mixed
    {
        return $this->id;
    }

    /**
     * {@inheritDoc}
     *
     * @return array<string>
     */
    public function getRoles(): array
    {
        return $this->roles;
    }

    /**
     * Returns user data value.
     */
    public function &__get(string $key): mixed
    {
        return $this->data[$key];
    }
}
