<?php

namespace Alpha\Console;

class CommandDefinition
{
    public string $commandName = '';
    public array $arguments = [];
    public array $options = [];

    private array $commonOptions = [
        '--help' => [
            'description' => 'Вывод информации о команде',
            'isHidden' => true,
        ],
        '--interactive' => [
            'description' => 'Вызов команды в режиме интерактивного ввода',
            'isHidden' => true,
        ]
    ];

    public function __construct(
        private readonly string $signature,
        public readonly string $description = '',
    )
    {
        $this->prepareSignature();
        $this->options += $this->commonOptions;
    }

    public function getCommonOptions() :array
    {
        return $this->commonOptions;
    }
    private function prepareSignature(): void
    {
        preg_match('/^([^{\s]+)/u', $this->signature, $matches);
        $this->commandName = $matches[1];

        preg_match_all('/{([^}]+)}/u', $this->signature, $matches);

        foreach ($matches[1] as $prepareString) {
            $isRequired = true;
            $defaultValue = null;

            $parts = explode(':', $prepareString);
            $name = isset($parts[0]) ? trim($parts[0]) : '';
            $description = isset($parts[1]) ? trim($parts[1]) : '';

            if (str_starts_with($name, '?')) {
                $name = substr($name, 1);
                $isRequired = false;
            }

            if (str_contains($name, '--')) {
                $this->options[$name] = [
                    'description' => $description,
                ];

                continue;
            }

            if (substr_count($name, "=") === 1 && str_ends_with($name, "=") === false) {
                $arr = explode("=", $name);
                $defaultValue = $arr[1];
                $name = $arr[0];
            }

            $this->arguments[$name] = [
                'description' => $description,
                'required' => $isRequired,
                'default' => $defaultValue,
            ];
        }
    }
}