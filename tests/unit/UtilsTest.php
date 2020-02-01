<?php

declare(strict_types = 1);

namespace Sweetchuck\CliCmdBuilder\Test;

use Codeception\Test\Unit;
use Sweetchuck\CliCmdBuilder\Utils;

/**
 * @covers \Sweetchuck\CliCmdBuilder\Utils<extended>
 */
class UtilsTest extends Unit
{

    public function casesEnsureDashPrefix(): array
    {
        return [
            // @todo empty string.
            'exists' => ['-a', '-a'],
            'not-exists' => ['--a', 'a'],
        ];
    }

    /**
     * @dataProvider casesEnsureDashPrefix
     */
    public function testEnsureDashPrefix(string $expected, string $string): void
    {
        $this->assertEquals($expected, Utils::ensureDashPrefix($string));
    }

    public function casesGetOptionNameWithSeparator(): array
    {
        return [
            'simple' => ['--a=', 'a', '='],
            'already' => ['--a=', '--a', '='],
        ];
    }

    /**
     * @dataProvider casesGetOptionNameWithSeparator
     */
    public function testGetOptionNameWithSeparator($expected, string $name, string $separator): void
    {
        $this->assertSame($expected, Utils::getOptionNameWithSeparator($name, $separator));
    }

    public function casesWrapCommand(): array
    {
        return [
            'empty' => ['a', '', 'a'],
            'unchanged' => ['a', '', 'a'],
            'inlineCommand' => ['$(a)', 'inlineCommand', 'a'],
            'inlineCommandString' => ['"$(a)"', 'inlineCommandString', 'a'],
            'stringSafe' => ['"a --b=\"c\""', 'stringSafe', 'a --b="c"'],
            'stringUnsafe' => ["'a --b=\${c}'", 'stringUnsafe', 'a --b=${c}'],
        ];
    }

    /**
     * @dataProvider casesWrapCommand
     */
    public function testWrapCommand(string $expected, string $wrapper, string $command): void
    {
        $this->assertSame($expected, Utils::wrapCommand($wrapper, $command));
    }
}
