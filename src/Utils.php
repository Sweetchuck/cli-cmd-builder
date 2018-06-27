<?php

declare(strict_types = 1);

namespace Sweetchuck\CliCmdBuilder;

class Utils
{
    public static function ensureDashPrefix(string $string): string
    {
        return mb_substr($string, 0, 1) === '-' ? $string : "--{$string}";
    }

    public static function filterEnabled(array $items): array
    {
        return (gettype(reset($items)) === 'boolean') ?
            array_keys($items, true, true)
            : $items;
    }

    public static function getOptionNameWithSeparator(string $name, string $separator):string
    {
        $name = static::ensureDashPrefix($name);

        return $name . (mb_substr($name, 0, 2) === '--' ? $separator : ' ');
    }

    public static function wrapCommand(string $wrapper, string $command): string
    {
        switch ($wrapper) {
            case '':
            case 'unchanged':
                // Do nothing.
                break;

            case 'inlineCommand':
                // @todo Escape.
                $command = "$({$command})";
                break;

            case 'inlineCommandString':
                $command = sprintf('"$(%s)"', addcslashes($command, '"'));
                break;

            case 'stringSafe':
                $command = '"' . addcslashes($command, '"') . '"';
                break;

            case 'stringUnsafe':
            default:
                $command = escapeshellarg($command);
                break;
        }

        return $command;
    }
}
