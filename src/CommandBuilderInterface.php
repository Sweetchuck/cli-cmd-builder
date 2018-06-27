<?php

declare(strict_types = 1);

namespace Sweetchuck\CliCmdBuilder;

interface CommandBuilderInterface extends CliCmdBuilderInterface
{

    public function getOptionSeparator(): string;

    /**
     * @return $this
     */
    public function setOptionSeparator(string $optionSeparator);

    public function getOutputType(): string;

    /**
     * @return $this
     */
    public function setOutputType(string $outputType);

    /**
     * @return $this
     */
    public function addEnvVars(array $envVars);

    /**
     * @param string $name
     * @param string|\Sweetchuck\CliCmdBuilder\CliCmdBuilderInterface $value
     *
     * @return $this
     */
    public function addEnvVar(string $name, $value);

    /**
     * @param string|\Sweetchuck\CliCmdBuilder\CliCmdBuilderInterface $executable
     *
     * @return $this
     */
    public function setExecutable($executable);

    /**
     * @param string $name
     * @param mixed $value
     * @param string $type
     *
     * @return $this
     */
    public function addOption(string $name, $value = null, ?string $type = null);

    /**
     * @param mixed $value
     * @param string $type
     *
     * @return $this
     */
    public function addArgument($value, ?string $type = null);
}
