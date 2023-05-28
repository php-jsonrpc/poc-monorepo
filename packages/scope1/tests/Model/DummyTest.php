<?php
namespace PhpJsonrpc\PocMonorepoPkg1\Tests\Model;

use PhpJsonrpc\PocMonorepoPkg1\Model\Dummy;
use PHPUnit\Framework\TestCase;

/**
 * @covers \PhpJsonrpc\PocMonorepoPkg1\Model\Dummy
 */
class DummyTest extends TestCase
{
    public function testShouldWork(): void
    {
        $this->assertInstanceOf(Dummy::class, new Dummy());
    }
}
