<?php

namespace Sweetchuck\CliCmdBuilder\Tests\Unit;

use Sweetchuck\CliCmdBuilder\CommandBuilder;

/**
 * @coversDefaultClass \Sweetchuck\CliCmdBuilder\CommandBuilder
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

    public function casesBuild(): array
    {
        return [
            'empty' => [
                '',
            ],
            'basic' => [
                "A='a' B='b' my-cmd --dry-run --path#'my-path' 'c' d 1>'/dev/null' 2>&1",
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
            'executable path as string' => [
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
    public function testBuild(string $expected, array $props = [], ?array $config = null, array $configOverride = [])
    {
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
