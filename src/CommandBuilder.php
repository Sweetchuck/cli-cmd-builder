<?php

declare(strict_types = 1);

namespace Sweetchuck\CliCmdBuilder;

class CommandBuilder implements CommandBuilderInterface
{
    /**
     * @var array
     */
    protected $components = [];

    /**
     * @var array
     */
    protected $command = [];

    // region optionSeparator
    /**
     * @var string
     */
    protected $optionSeparator = '=';

    /**
     * {@inheritdoc}
     */
    public function getOptionSeparator(): string
    {
        return $this->optionSeparator;
    }

    /**
     * {@inheritdoc}
     */
    public function setOptionSeparator(string $value)
    {
        $this->optionSeparator = $value;

        return $this;
    }

    // endregion

    // region outputType
    /**
     * @var string
     *
     * @todo Use constants for the available values.
     */
    protected $outputType = '';

    /**
     * {@inheritdoc}
     */
    public function getOutputType(): string
    {
        return $this->outputType;
    }

    /**
     * {@inheritdoc}
     */
    public function setOutputType(string $value)
    {
        $this->outputType = $value;

        return $this;
    }
    // endregion

    protected $config = [];

    public function __toString(): string
    {
        return $this->build();
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig(): array
    {
        $this->initConfig();

        return $this->config;
    }

    /**
     * {@inheritdoc}
     */
    public function setConfig(array $config)
    {
        if (array_key_exists('optionSeparator', $config)) {
            $this->setOptionSeparator($config['optionSeparator']);
        }

        if (array_key_exists('outputType', $config)) {
            $this->setOutputType($config['outputType']);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addComponents(array $components)
    {
        foreach ($components as $component) {
            $this->addComponent($component);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addComponent(array $component)
    {
        $this->components[] = $component;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addEnvVars(array $envVars)
    {
        foreach ($envVars as $name => $value) {
            $this->addEnvVar($name, $value);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addEnvVar(string $name, $value)
    {
        $this->addComponent([
            'type' => 'envVar',
            'name' => $name,
            'value' => $value,
        ]);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setExecutable($executable)
    {
        $this->addComponent([
            'type' => 'executable',
            'value' => $executable,
        ]);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addOption(string $name, $value = null, ?string $type = null)
    {
        if ($value === null && $type === null) {
            $value = true;
        }

        if ($type === null) {
            $type = $this->getOptionType($value);
        }

        if (!preg_match('/^option:/', $type)) {
            $type = "option:$type";
        }

        $this->addComponent([
            'type' => $type,
            'name' => $name,
            'value' => $value,
        ]);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addArgument($value, ?string $type = null)
    {
        if ($type === null) {
            $type = $this->getArgumentType($value);
        }

        if (!preg_match('/^argument:/', $type)) {
            $type = "argument:$type";
        }

        $this->addComponent([
            'type' => $type,
            'value' => $value,
        ]);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function build(array $configOverride = []): string
    {
        $this->command = [];

        return $this
            ->initConfig()
            ->applyConfigOverride($configOverride)
            ->buildComponents()
            ->buildPostProcess();
    }

    /**
     * @return $this
     */
    protected function initConfig()
    {
        $this->config = [
            'optionSeparator' => $this->getOptionSeparator(),
            'outputType' => $this->getOutputType(),
        ];

        return $this;
    }

    /**
     * @return $this
     */
    protected function applyConfigOverride(array $configOverride)
    {
        $this->config = (array) $configOverride + $this->config;

        return $this;
    }

    protected function getOptionType($value): string
    {
        if (is_bool($value)) {
            return 'option:flag';
        }

        if (is_iterable($value)) {
            return 'option:value:multi';
        }

        return 'option:value';
    }

    /**
     * @param int|string|\Sweetchuck\CliCmdBuilder\CliCmdBuilderInterface $value
     */
    protected function getArgumentType($value): string
    {
        return 'argument:single:unsafe';
    }

    /**
     * @return $this
     */
    protected function buildComponents()
    {
        $components = [
            'envVar' => [
                'pattern' => [],
                'args' => [],
            ],
            'executable' => '',
            'optsAndArgs' => [
                'pattern' => [],
                'args' => [],
            ],
            'redirectStdOutput' => '',
            'redirectStdError' => '',
            'stdInputSource' => false,
            'background' => false,
            'pipe' => [],
        ];

        $os = $this->config['optionSeparator'];

        foreach ($this->components as $component) {
            if (!isset($component['value'])) {
                continue;
            }

            $matches = ['group' => ''];
            preg_match('/^(?P<group>[^:]+)(:|$)/', $component['type'], $matches);
            $componentGroup = $matches['group'];

            $optionCliName = $componentGroup === 'option' ? $component['name'] : '';

            switch ($component['type']) {
                case 'envVar':
                    $components[$componentGroup]['pattern'][] = "{$component['name']}=%s";
                    $components[$componentGroup]['args'][] = ($component['value'] instanceof CliCmdBuilderInterface) ?
                        $component['value']->build(['outputType' => 'inlineCommandString'])
                        : escapeshellarg((string) $component['value']);
                    break;

                case 'executable':
                    if ($component['value'] instanceof CliCmdBuilderInterface) {
                        $config = $component['value']->getConfig();
                        if (empty($config['outputType'])) {
                            $config['outputType'] = 'inlineCommand';
                        }

                        $components[$componentGroup] = $component['value']->build($config);
                        break;
                    }

                    if ($component['value']) {
                        $components[$componentGroup] = escapeshellcmd((string) $component['value']);
                    }
                    break;

                case 'option:flag':
                    if ($component['value']) {
                        $components['optsAndArgs']['pattern'][] = Utils::ensureDashPrefix($optionCliName);
                    }
                    break;

                case 'option:tri-state':
                    $optionCliName = ltrim($optionCliName, '-');
                    $components['optsAndArgs']['pattern'][] = $component['value'] ?
                        "--$optionCliName"
                        : "--no-$optionCliName";
                    break;

                case 'option:value':
                    if ($component['value'] === null) {
                        break;
                    }

                    $pattern = Utils::getOptionNameWithSeparator($optionCliName, $os) . '%s';
                    $components['optsAndArgs']['pattern'][] = $pattern;
                    if (($component['value'] instanceof CliCmdBuilderInterface)) {
                        $config = $component['value']->getConfig();
                        if (empty($config['outputType'])) {
                            $config['outputType'] = 'inlineCommandString';
                        }

                        $components['optsAndArgs']['args'][] = $component['value']->build($config);
                        break;
                    }

                    $components['optsAndArgs']['args'][] = escapeshellarg((string) $component['value']);
                    break;

                case 'option:value:list':
                    $values = Utils::filterEnabled($component['value']);
                    if ($values) {
                        $pattern = Utils::getOptionNameWithSeparator($optionCliName, $os) . '%s';
                        $separator = $option['separator'] ?? ',';
                        $components['optsAndArgs']['pattern'][] = $pattern;
                        $components['optsAndArgs']['args'][] = escapeshellarg(implode($separator, $values));
                    }
                    break;

                case 'option:value:multi':
                    $values = [];
                    foreach ($component['value'] as $key => $value) {
                        if ($value === false || $value === null) {
                            continue;
                        }

                        if ($value === true) {
                            $value = $key;
                        }

                        if ($value instanceof  CliCmdBuilderInterface) {
                            $values[] = $value->build(['outputType' => 'inlineCommandString']);

                            continue;
                        }

                        $values[] = escapeshellarg((string) $value);
                    }

                    $pattern = Utils::getOptionNameWithSeparator($optionCliName, $os) . '%s';
                    foreach ($values as $value) {
                        $components['optsAndArgs']['pattern'][] = $pattern;
                        $components['optsAndArgs']['args'][] = $value;
                    }
                    break;

                case 'argument:separator':
                    if ($component['value']) {
                        $components['optsAndArgs']['pattern'][] = '--';
                    }
                    break;

                case 'argument:single:unsafe':
                    $components['optsAndArgs']['pattern'][] = '%s';
                    $components['optsAndArgs']['args'][] = ($component['value'] instanceof CliCmdBuilderInterface) ?
                        $component['value']->build(['outputType' => 'inlineCommandString'])
                        : escapeshellarg((string) $component['value']);
                    break;

                case 'argument:single:safe':
                    $components['optsAndArgs']['pattern'][] = '%s';
                    $components['optsAndArgs']['args'][] = (string) $component['value'];
                    break;

                case 'background':
                    $components[$componentGroup] = (bool) $component['value'];
                    break;

                case 'stdInputSource':
                    if ($component['value']) {
                        $components[$componentGroup] = mb_substr($component['value'], 0, 4) === '<<< ' ?
                            $component['value']
                            : '< ' . escapeshellarg((string) $component['value']);
                    }
                    break;

                case 'redirectStdOutput':
                case 'redirectStdError':
                    if ($component['value']) {
                        $components[$componentGroup] = preg_match('/^&\d$/u', $component['value']) ?
                            $component['value']
                            : escapeshellarg((string) $component['value']);
                    }
                    break;

                case 'pipe':
                    if ($component['value']) {
                        $components[$componentGroup][] = $component['value'];
                    }
                    break;
            }
        }

        if ($components['envVar']['pattern']) {
            $this->command[] = vsprintf(
                implode(' ', $components['envVar']['pattern']),
                $components['envVar']['args']
            );
        }

        if ($components['executable']) {
            $this->command[] = $components['executable'];
        }

        if ($components['optsAndArgs']['pattern']) {
            $this->command[] = vsprintf(
                implode(' ', $components['optsAndArgs']['pattern']),
                $components['optsAndArgs']['args']
            );
        }

        if ($components['redirectStdOutput']) {
            $this->command[] = "1>{$components['redirectStdOutput']}";
        }

        if ($components['redirectStdError']) {
            $this->command[] = "2>{$components['redirectStdError']}";
        }

        if ($components['stdInputSource']) {
            $this->command[] = $components['stdInputSource'];
        }

        if ($components['background']) {
            $this->command[] = '&';
        }

        foreach ($components['pipe'] as $pipe) {
            $pipe = (string) $pipe;
            if ($pipe) {
                $this->command[] = "| $pipe";
            }
        }

        return $this;
    }

    protected function buildPostProcess(): string
    {
        if (!$this->command) {
            return '';
        }

        return Utils::wrapCommand($this->config['outputType'], implode(' ', $this->command));
    }
}
