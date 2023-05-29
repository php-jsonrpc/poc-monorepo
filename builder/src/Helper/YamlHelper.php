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
    public static function loadFileAsYaml(string $filepath): array
    {
        $content = file_get_contents($filepath);
        if (false === $content) {
            throw new \Exception('Unable to load the file');
        }

        return self::loadAsYaml($content);
    }

    public static function loadAsYaml(string $content): array
    {
        $data = Yaml::parse($content);

        if (!is_array($data)) {
            throw new \Exception(sprintf('Expected an array but got %s', gettype($data)));
        }

        return $data;
    }
}
