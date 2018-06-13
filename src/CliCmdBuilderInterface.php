<?php

namespace Sweetchuck\CliCmdBuilder;

interface CliCmdBuilderInterface
{
    public function build(array $configOverride = []): string;

    public function getConfig(): array;

    /**
     * @return $this
     */
    public function setConfig(array $config);

    /**
     * @return $this
     */
    public function addComponents(array $components);

    /**
     * @return $this
     */
    public function addComponent(array $component);
}
