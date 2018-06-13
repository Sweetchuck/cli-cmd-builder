<?php

declare(strict_types = 1);

namespace Sweetchuck\CliCmdBuilder;

class Utils
{
    public static function ensureDashPrefix(string $string): string
    {
        return preg_match('/^-/', $string) ? $string : "--{$string}";
    }

    public static function filterEnabled(array $items): array
    {
        return (gettype(reset($items)) === 'boolean') ?
            array_keys($items, true, true)
            : $items;
    }

    public static function wrapCommand(string $wrapper, string $command): string
    {
        switch ($wrapper) {
            case 'unchanged':
                // Do nothing.
                break;

            case 'inlineCommand':
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
