<?php

declare(strict_types=1);

namespace Tests\Utils;

use Cds\NettePresenterTests\Utils\TestIdentity;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * @internal
 */
#[CoversClass(TestIdentity::class)]
final class TestIdentityTest extends TestCase
{
    private TestIdentity $identity;

    public function setUp(): void
    {
        parent::setUp();

        $this->identity = new TestIdentity('test-user');
    }

    /**
     * Test obtaining identity ID.
     */
    #[Test]
    public function getId(): void
    {
        self::assertEquals('test-user', $this->identity->getId());
    }

    /**
     * Test obtaining roles - empty list.
     */
    #[Test]
    public function rolesEmpty(): void
    {
        self::assertEmpty($this->identity->roles);
        self::assertEmpty($this->identity->getRoles());
    }

    /**
     * Test obtaining roles.
     */
    #[Test]
    public function roles(): void
    {
        $this->identity->roles[] = 'role1';

        self::assertEquals(['role1'], $this->identity->roles);
        self::assertEquals(['role1'], $this->identity->getRoles());
    }

    /**
     * Test obtaining data.
     */
    #[Test]
    public function dataEmpty(): void
    {
        self::assertEmpty($this->identity->data);
        self::assertFalse(isset($this->identity->key1));
        self::assertFalse(isset($this->identity->key2));
        self::assertFalse(isset($this->identity->key3));
    }

    /**
     * Test obtaining data.
     */
    #[Test]
    public function data(): void
    {
        // @phpstan-ignore property.notFound
        $this->identity->key1 = 'value1';
        // @phpstan-ignore property.notFound
        $this->identity->key2 = 'value2';

        self::assertTrue(isset($this->identity->key1));
        self::assertTrue(isset($this->identity->key2));
        self::assertFalse(isset($this->identity->key3));
        self::assertEquals('value1', $this->identity->key1);
        self::assertEquals('value2', $this->identity->key2);
    }
}
