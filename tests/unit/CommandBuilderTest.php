<?php

declare(strict_types = 1);

namespace Sweetchuck\CliCmdBuilder\Tests\Unit;

use Sweetchuck\CliCmdBuilder\CommandBuilder;

/**
 * @covers \Sweetchuck\CliCmdBuilder\CommandBuilder
 */
class CommandBuilderTest extends BuilderTestBase
{
    /**
     * @var \Sweetchuck\CliCmdBuilder\CommandBuilder
     */
    protected $builder;

    /**
     * @var string|\Sweetchuck\CliCmdBuilder\CliCmdBuilderInterface
     */
    protected $builderClass = CommandBuilder::class;

    public function testGetSetConfig(): void
    {
        $this->assertSame(
            [
                'optionSeparator' => '=',
                'outputType' => '',
            ],
            $this->builder->getConfig()
        );

        $this->builder->setConfig(['optionSeparator' => ' ']);
        $this->assertSame(
            [
                'optionSeparator' => ' ',
                'outputType' => '',
            ],
            $this->builder->getConfig()
        );

        $this->builder->setConfig(['outputType' => 'inlineCommand']);
        $this->assertSame(
            [
                'optionSeparator' => ' ',
                'outputType' => 'inlineCommand',
            ],
            $this->builder->getConfig()
        );
    }

    public function casesBuild(): array
    {
        return [
            'empty' => [
                '',
            ],
            'basic' => [
                "A='a' B='b' my-cmd --dry-run --path#'my-path' --num#'42' 'c' d '42' 1>'/dev/null' 2>&1",
                [
                    'envVars' => [
                        'A' => 'a',
                        'B' => 'b',
                    ],
                    'executable' => 'my-cmd',
                    'options' => [
                        [
                            'dry-run',
                            true,
                        ],
                        [
                            'path',
                            'my-path',
                        ],
                        [
                            'num',
                            42,
                        ],
                    ],
                    'arguments' => [
                        [
                            'c',
                            'single:unsafe',
                        ],
                        [
                            'd',
                            'single:safe',
                        ],
                        [
                            42,
                            'single:unsafe',
                        ],
                    ],
                    'components' => [
                        [
                            'type' => 'redirectStdOutput',
                            'value' => '/dev/null',
                        ],
                        [
                            'type' => 'redirectStdError',
                            'value' => '&1',
                        ],
                    ],
                ],
                [
                    'optionSeparator' => '#',
                ],
            ],
            'envVar' => [
                "A='b' C='d'",
                [
                    'components' => [
                        [
                            'type' => 'envVar',
                            'name' => 'A',
                            'value' => 'b',
                        ],
                        [
                            'type' => 'envVar',
                            'name' => 'C',
                            'value' => 'd',
                        ],
                    ],
                ],
            ],
            'envVar CliCmdBuilderInterface' => [
                'A="$(foo \'bar\')"',
                [
                    'components' => [
                        [
                            'type' => 'envVar',
                            'name' => 'A',
                            'value' => (new CommandBuilder())
                                ->setExecutable('foo')
                                ->addArgument('bar'),
                        ],
                    ],
                ],
            ],
            'executable' => [
                'my-cmd',
                [
                    'components' => [
                        [
                            'type' => 'executable',
                            'value' => 'my-cmd',
                        ],
                    ],
                ],
            ],
            'executable CliCmdBuilderInterface' => [
                '$(which php) --ini',
                [
                    'components' => [
                        [
                            'type' => 'executable',
                            'value' => (new CommandBuilder())
                                ->setExecutable('which')
                                ->addArgument('php', 'single:safe'),
                        ],
                        [
                            'type' => 'option:flag',
                            'name' => 'ini',
                            'value' => true,
                        ],
                    ],
                ],
            ],
            'option:flag null null' => [
                'a --b',
                [
                    'components' => [
                        [
                            'type' => 'executable',
                            'value' => 'a',
                        ],
                    ],
                    'options' => [
                        ['b'],
                    ],
                ],
            ],
            'option:flag false null' => [
                'a',
                [
                    'components' => [
                        [
                            'type' => 'executable',
                            'value' => 'a',
                        ],
                    ],
                    'options' => [
                        ['b', false],
                    ],
                ],
            ],
            'option:flag true null' => [
                'a --b',
                [
                    'components' => [
                        [
                            'type' => 'executable',
                            'value' => 'a',
                        ],
                    ],
                    'options' => [
                        ['--b', true],
                    ],
                ],
            ],
            'option:tri-state true' => [
                'a --b',
                [
                    'components' => [
                        [
                            'type' => 'executable',
                            'value' => 'a',
                        ],
                    ],
                    'options' => [
                        ['b', true, 'tri-state'],
                    ],
                ],
            ],
            'option:tri-state false' => [
                'a --no-b',
                [
                    'components' => [
                        [
                            'type' => 'executable',
                            'value' => 'a',
                        ],
                    ],
                    'options' => [
                        ['b', false, 'tri-state'],
                    ],
                ],
            ],
            'option:tri-state null' => [
                'a',
                [
                    'components' => [
                        [
                            'type' => 'executable',
                            'value' => 'a',
                        ],
                    ],
                    'options' => [
                        ['b', null, 'tri-state'],
                    ],
                ],
            ],
            'option:value null' => [
                'a',
                [
                    'components' => [
                        [
                            'type' => 'executable',
                            'value' => 'a',
                        ],
                    ],
                    'options' => [
                        ['b', null, 'value'],
                    ],
                ],
            ],
            'option:value string empty' => [
                "a --b=''",
                [
                    'components' => [
                        [
                            'type' => 'executable',
                            'value' => 'a',
                        ],
                    ],
                    'options' => [
                        ['b', '', 'value'],
                    ],
                ],
            ],
            'option:value string nasty' => [
                "a --b='c'\''d'",
                [
                    'components' => [
                        [
                            'type' => 'executable',
                            'value' => 'a',
                        ],
                    ],
                    'options' => [
                        ['b', "c'd", 'value'],
                    ],
                ],
            ],
            'option:value CliCmdBuilderInterface' => [
                "a --b=\"$(c)\"",
                [
                    'components' => [
                        [
                            'type' => 'executable',
                            'value' => 'a',
                        ],
                    ],
                    'options' => [
                        [
                            'b',
                            (new CommandBuilder())->setExecutable('c'),
                            'value',
                        ],
                    ],
                ],
            ],
            'option:value:list empty' => [
                'a',
                [
                    'components' => [
                        [
                            'type' => 'executable',
                            'value' => 'a',
                        ],
                    ],
                    'options' => [
                        ['b', [], 'value:list'],
                    ],
                ],
            ],
            'option:value:list vector' => [
                "a --b='c,d,e'",
                [
                    'components' => [
                        [
                            'type' => 'executable',
                            'value' => 'a',
                        ],
                    ],
                    'options' => [
                        ['b', ['c', 'd', 'e'], 'value:list'],
                    ],
                ],
            ],
            'option:value:list hash false' => [
                'a',
                [
                    'components' => [
                        [
                            'type' => 'executable',
                            'value' => 'a',
                        ],
                    ],
                    'options' => [
                        ['b', ['c' => false], 'value:list'],
                    ],
                ],
            ],
            'option:value:list hash mixed' => [
                "a --b='d,e'",
                [
                    'components' => [
                        [
                            'type' => 'executable',
                            'value' => 'a',
                        ],
                    ],
                    'options' => [
                        ['b', ['c' => false, 'd' => true, 'e' => true], 'value:list'],
                    ],
                ],
            ],
            'option:value:multi empty' => [
                'a',
                [
                    'components' => [
                        [
                            'type' => 'executable',
                            'value' => 'a',
                        ],
                    ],
                    'options' => [
                        ['b', [], 'value:multi'],
                    ],
                ],
            ],
            'option:value:multi vector' => [
                "a --b='c' --b='d' --b='e'",
                [
                    'components' => [
                        [
                            'type' => 'executable',
                            'value' => 'a',
                        ],
                    ],
                    'options' => [
                        ['b', ['c', 'd', 'e'], 'value:multi'],
                    ],
                ],
            ],
            'option:value:multi hash false' => [
                'a',
                [
                    'components' => [
                        [
                            'type' => 'executable',
                            'value' => 'a',
                        ],
                    ],
                    'options' => [
                        ['b', ['c' => false], 'value:multi'],
                    ],
                ],
            ],
            'option:value:multi hash mixed' => [
                "a --b='d' --b='e'",
                [
                    'components' => [
                        [
                            'type' => 'executable',
                            'value' => 'a',
                        ],
                    ],
                    'options' => [
                        ['b', ['c' => false, 'd' => true, 'e' => true], 'value:multi'],
                    ],
                ],
            ],
            'option:value:multi autodetect type' => [
                "a --b='d' --b='e'",
                [
                    'components' => [
                        [
                            'type' => 'executable',
                            'value' => 'a',
                        ],
                    ],
                    'options' => [
                        ['b', ['c' => false, 'd' => true, 'e' => true]],
                    ],
                ],
            ],
            'option:value:multi CliCmdBuilderInterface' => [
                "a --b='c' --b=\"\$(d --e='f')\"",
                [
                    'components' => [
                        [
                            'type' => 'executable',
                            'value' => 'a',
                        ],
                    ],
                    'options' => [
                        [
                            'b',
                            [
                                'c' => true,
                                'd' => (new CommandBuilder())
                                    ->setExecutable('d')
                                    ->addOption('e', 'f'),
                            ],
                        ],
                    ],
                ],
            ],
            'components 1' => [
                "a --b='c' 'd' -- --e='f' 'g'",
                [
                    'components' => [
                        [
                            'type' => 'executable',
                            'value' => 'a',
                        ],
                        [
                            'type' => 'option:value',
                            'name' => 'b',
                            'value' => 'c',
                        ],
                        [
                            'type' => 'argument:single:unsafe',
                            'value' => 'd',
                        ],
                        [
                            'type' => 'argument:separator',
                            'value' => 'd',
                        ],
                        [
                            'type' => 'option:value',
                            'name' => 'e',
                            'value' => 'f',
                        ],
                        [
                            'type' => 'argument:single:unsafe',
                            'value' => 'g',
                        ],
                    ],
                ],
            ],
            'argument:single:unsafe CliCmdBuilderInterface' => [
                'a "$(b)"',
                [
                    'executable' => 'a',
                    'arguments' => [
                        [
                            (new CommandBuilder())->setExecutable('b'),
                        ],
                    ],
                ],
            ],
            'argument:single:safe' => [
                'a "$(b)" c',
                [
                    'executable' => 'a',
                    'arguments' => [
                        [
                            (new CommandBuilder())->setExecutable('b'),
                        ],
                        [
                            'c',
                            'argument:single:safe',
                        ],
                    ],
                ],
            ],
            'argument:multi:unsafe' => [
                'a "$(b)"',
                [
                    'executable' => 'a',
                    'arguments' => [
                        [
                            (new CommandBuilder())->setExecutable('b'),
                        ],
                    ],
                ],
            ],
            'background' => [
                "a -b 'c' &",
                [
                    'executable' => 'a',
                    'options' => [
                        [
                            '-b',
                            'c',
                        ],
                    ],
                    'components' => [
                        [
                            'type' => 'background',
                            'value' => true,
                        ],
                    ],
                ],
            ],
            'stdInputSource file' => [
                "a < 'b.txt'",
                [
                    'executable' => 'a',
                    'components' => [
                        [
                            'type' => 'stdInputSource',
                            'value' => 'b.txt',
                        ],
                    ],
                ],
            ],
            'stdInputSource forward' => [
                "a <<< $(/dev/stdin)",
                [
                    'executable' => 'a',
                    'components' => [
                        [
                            'type' => 'stdInputSource',
                            'value' => '<<< $(/dev/stdin)',
                        ],
                    ],
                ],
            ],
            'outputType inlineCommand' => [
                "$(a 'b\"c')",
                [
                    'components' => [
                        [
                            'type' => 'executable',
                            'value' => 'a',
                        ],
                    ],
                    'arguments' => [
                        ['b"c'],
                    ],
                ],
                [
                    'outputType' => 'inlineCommand',
                ],
            ],
            'outputType inlineCommandString' => [
                '"$(a \'b\\"c\')"',
                [
                    'components' => [
                        [
                            'type' => 'executable',
                            'value' => 'a',
                        ],
                    ],
                    'arguments' => [
                        ['b"c'],
                    ],
                ],
                [
                    'outputType' => 'inlineCommandString',
                ],
            ],
            'outputType stringSafe' => [
                '"a \'b\\"c\'"',
                [
                    'components' => [
                        [
                            'type' => 'executable',
                            'value' => 'a',
                        ],
                    ],
                    'arguments' => [
                        ['b"c'],
                    ],
                ],
                [
                    'outputType' => 'stringSafe',
                ],
            ],
            'outputType stringUnsafe' => [
                "docker-compose exec web /bin/bash -c 'foo --bar='\''baz'\'''",
                [
                    'components' => [
                        [
                            'type' => 'executable',
                            'value' => 'docker-compose',
                        ],
                        [
                            'type' => 'argument:single:safe',
                            'value' => 'exec',
                        ],
                        [
                            'type' => 'argument:single:safe',
                            'value' => 'web',
                        ],
                        [
                            'type' => 'argument:single:safe',
                            'value' => '/bin/bash',
                        ],
                        [
                            'type' => 'option:value',
                            'name' => '-c',
                            'value' => (new CommandBuilder())
                                ->setOutputType('stringUnsafe')
                                ->setExecutable('foo')
                                ->addOption('bar', 'baz'),
                        ],
                    ],
                ],
            ],
            'pipe - string' => [
                "cat 'a.txt' | grep 'b'",
                [

                    'components' => [
                        [
                            'type' => 'executable',
                            'value' => 'cat',
                        ],
                        [
                            'type' => 'pipe',
                            'value' => "grep 'b'",
                        ],
                    ],
                    'arguments' => [
                        ['a.txt'],
                    ],
                ],
            ],
            'pipe - builder' => [
                "cat 'a.txt' | grep 'b'",
                [
                    'arguments' => [
                        ['a.txt'],
                    ],
                    'components' => [
                        [
                            'type' => 'executable',
                            'value' => 'cat',
                        ],
                        [
                            'type' => 'pipe',
                            'value' => (new CommandBuilder())
                                ->setExecutable('grep')
                                ->addArgument('b'),
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @dataProvider casesBuild
     */
    public function testBuild(
        string $expected,
        array $props = [],
        ?array $config = null,
        array $configOverride = []
    ) {
        if ($config !== null) {
            $this->builder->setConfig($config);
        }

        if (array_key_exists('envVars', $props)) {
            $this->builder->addEnvVars($props['envVars']);
        }

        if (array_key_exists('executable', $props)) {
            $this->builder->setExecutable($props['executable']);
        }

        if (array_key_exists('options', $props)) {
            foreach ($props['options'] as $option) {
                $this->builder->addOption(...$option);
            }
        }

        if (array_key_exists('arguments', $props)) {
            foreach ($props['arguments'] as $argument) {
                $this->builder->addArgument(...$argument);
            }
        }

        if (array_key_exists('components', $props)) {
            $this->builder->addComponents($props['components']);
        }

        $this->assertSame($expected, $this->builder->build($configOverride));
    }
}
