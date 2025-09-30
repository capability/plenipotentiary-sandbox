<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\Support\InputSource;

use Symfony\Component\Console\Input\InputInterface;

final class ConsoleSource implements InputSource
{
    public function __construct(private InputInterface $input) {}

    public function get(string $key): mixed
    {
        if ($this->input->hasOption($key)) {
            return $this->input->getOption($key);
        }

        if ($this->input->hasArgument($key)) {
            return $this->input->getArgument($key);
        }

        return null;
    }
}
