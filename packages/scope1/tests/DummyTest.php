<?php
namespace PhpJsonrpc\PocMonorepoPkg1\Tests;

use PhpJsonrpc\PocMonorepoPkg1\Dummy;
use PHPUnit\Framework\TestCase;

/**
 * @covers \PhpJsonrpc\PocMonorepoPkg1\Dummy
 */
class DummyTest extends TestCase
{
    public function testShouldWork(): void
    {
        $this->assertInstanceOf(Dummy::class, new Dummy());
    }
}
