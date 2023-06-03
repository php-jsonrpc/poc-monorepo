<?php
declare(strict_types=1);

namespace PhpJsonRpc\Builder\Helper;

use Symfony\Component\Yaml\Yaml;
use function file_get_contents;
use function gettype;
use function is_array;
use function sprintf;

class YamlHelper
{
    public static function loadAsYaml(string $content): array
    {
        $data = Yaml::parse($content);

        if (true !== is_array($data)) {
            throw new \Exception(sprintf('Expected an array but got %s', gettype($data)));
        }

        return $data;
    }
}
