<?php
namespace PhpJsonrpc\PocMonorepoPkg1\Model;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class Dummy
{
    function __construct(public readonly LoggerInterface $logger = new NullLogger()) {

    }
}
