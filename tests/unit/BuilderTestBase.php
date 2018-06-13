<?php

namespace Sweetchuck\CliCmdBuilder\Tests\Unit;

use Codeception\Test\Unit;

abstract class BuilderTestBase extends Unit
{
    /**
     * @var \Sweetchuck\CliCmdBuilder\CliCmdBuilderInterface
     */
    protected $builder;

    /**
     * @var string|\Sweetchuck\CliCmdBuilder\CliCmdBuilderInterface
     */
    protected $builderClass = '';

    /**
     * {@inheritdoc}
     */
    public function __construct(
        ?string $name = null,
        array $data = [],
        string $dataName = ''
    ) {
        parent::__construct($name, $data, $dataName);
        if ($dataName) {
            $this->builder = new $this->builderClass();
        }
    }

    abstract public function casesBuild(): array;

    /**
     * @dataProvider casesBuild
     */
    public function testBuild(string $expected, array $components = [], ?array $config = null)
    {
        if ($config !== null) {
            $this->builder->setConfig($config);
        }

        $this->assertSame($expected, $this->builder->build());
    }
}
