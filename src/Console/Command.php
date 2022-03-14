<?php

namespace Jackiedo\Packager\Console;

use Illuminate\Console\Command as IlluminateCommand;
use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Jackiedo\Packager\PackageManager;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;

class Command extends IlluminateCommand
{
    /**
     * The config repository.
     *
     * @var \Illuminate\Config\Repository
     */
    protected $config;

    /**
     * The package manager instance.
     *
     * @var \Jackiedo\Packager\PackageManager
     */
    protected $manager;

    /**
     * The console color styles.
     *
     * @var array
     */
    protected $colorStyles = [
        'hightlight' => [
            'foreground' => 'black',
            'background' => 'white',
        ],
        'success' => [
            'foreground' => 'black',
            'background' => 'green',
        ],
        'warning' => [
            'foreground' => 'black',
            'background' => 'yellow',
        ],
        'error' => [
            'foreground' => 'white',
            'background' => 'red',
        ],
    ];

    /**
     * Create a new command instance.
     *
     * @param object $config  The config repository
     * @param object $manager The package manager
     *
     * @return void
     */
    public function __construct(Config $config, PackageManager $manager)
    {
        $this->config  = $config;
        $this->manager = $manager;

        parent::__construct();
    }

    /**
     * Alias of the fire() method.
     *
     * @return void
     */
    public function handle()
    {
        $this->fire();
    }

    /**
     * Prompt the user for input.
     *
     * @param string          $question
     * @param null|string     $default
     * @param callable|string $validator
     *
     * @return mixed
     */
    public function ask($question, $default = null, $validator = null)
    {
        return $this->output->ask($question, $default, $validator);
    }

    /**
     * Give the user a single choice from an array of answers.
     *
     * @param string      $question
     * @param null|string $default
     * @param null|mixed  $attempts
     * @param bool        $multiple
     * @param null|mixed  $normalizer
     * @param array       $choices
     *
     * @return array|string
     */
    public function choice($question, array $choices, $default = null, $attempts = null, $multiple = false, $normalizer = null)
    {
        $question = new ChoiceQuestion($question, $choices, $default);

        $question->setMaxAttempts($attempts)->setMultiselect($multiple)->setAutocompleterValues(null);

        if (is_callable($normalizer)) {
            $question->setNormalizer($normalizer);
        }

        $answer = $this->output->askQuestion($question);

        if (is_array($answer)) {
            $this->output->block('Selected: ' . implode(', ', $answer));
        } else {
            $this->output->block('Selected: ' . $answer);
        }

        return $answer;
    }

    /**
     * Write a message as standard output without new line.
     *
     * @param string          $message
     * @param string          $style
     * @param null|int|string $verbosity
     *
     * @return void
     */
    public function write($message, $style = null, $verbosity = null)
    {
        $styled = $style ? "<{$style}>{$message}</{$style}>" : $message;

        if (method_exists($this, 'parseVerbosity')) {
            $this->output->write($styled, false, $this->parseVerbosity($verbosity));
        } else {
            $this->output->write($styled, false);
        }
    }

    /**
     * Add newline(s).
     *
     * @param int $count The quantity of lines
     *
     * @return void
     */
    public function newLine($count = 1)
    {
        $this->output->newLine($count);
    }

    /**
     * Formats a section title.
     *
     * @param string $message
     *
     * @return void
     */
    public function section($message)
    {
        $this->block('>>>>> ' . $message, null, 'fg=white;bg=blue', ' ', true);
    }

    /**
     * Formats a message as a block of text.
     *
     * @param array|string $messages The message to write in the block
     * @param string       $label    The content will be display in front of message
     * @param string       $style    The output formatter style
     * @param string       $prefix   The prefix content will be display in front of label
     * @param bool         $padding
     * @param bool         $escape
     *
     * @return void
     */
    public function block($messages, $label = null, $style = null, $prefix = ' ', $padding = false, $escape = true)
    {
        $this->output->block($messages, $label, $style, $prefix, $padding, $escape);
    }

    /**
     * Format a message as a block with background is white.
     *
     * @param array|string $messages The message to write in the block
     * @param string       $label    The content will be display in front of message
     * @param string       $prefix   The prefix content will be display in front of label
     * @param bool         $padding
     * @param bool         $escape
     * @param mixed        $message
     *
     * @return void
     */
    public function whiteBlock($message, $label = null, $prefix = ' ', $padding = true, $escape = true)
    {
        $this->block($message, $label, 'hightlight', $prefix, $padding, $escape);
    }

    /**
     * Format a message as a block with the success style.
     *
     * @param array|string $messages The message to write in the block
     * @param string       $label    The content will be display in front of message
     * @param string       $prefix   The prefix content will be display in front of label
     * @param bool         $padding
     * @param bool         $escape
     * @param mixed        $message
     *
     * @return void
     */
    public function successBlock($message, $label = null, $prefix = ' ', $padding = true, $escape = true)
    {
        $this->block($message, $label, 'success', $prefix, $padding, $escape);
    }

    /**
     * Format a message as a block with the warning style.
     *
     * @param array|string $messages The message to write in the block
     * @param string       $label    The content will be display in front of message
     * @param string       $prefix   The prefix content will be display in front of label
     * @param bool         $padding
     * @param bool         $escape
     * @param mixed        $message
     *
     * @return void
     */
    public function warningBlock($message, $label = null, $prefix = ' ', $padding = true, $escape = true)
    {
        $this->block($message, $label, 'warning', $prefix, $padding, $escape);
    }

    /**
     * Format a message as a block with the error style.
     *
     * @param array|string $messages The message to write in the block
     * @param string       $label    The content will be display in front of message
     * @param string       $prefix   The prefix content will be display in front of label
     * @param bool         $padding
     * @param bool         $escape
     * @param mixed        $message
     *
     * @return void
     */
    public function errorBlock($message, $label = null, $prefix = ' ', $padding = true, $escape = true)
    {
        $this->block($message, $label, 'error', $prefix, $padding, $escape);
    }

    /**
     * Execute the console command.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->setFormatStyles();

        return parent::execute($input, $output);
    }

    /**
     * Initialize some styles for the formatter.
     *
     * @return void
     */
    protected function setFormatStyles()
    {
        if (property_exists($this, 'colorStyles')) {
            foreach ($this->colorStyles as $key => $value) {
                $foreground = Arr::get($value, 'foreground', 'default');
                $background = Arr::get($value, 'background', 'default');
                $style      = new OutputFormatterStyle($foreground, $background);

                $this->output->getFormatter()->setStyle(Str::snake($key), $style);
            }
        }

        return $this;
    }
}
