<?php

declare(strict_types=1);

namespace Plenipotentiary\Laravel\Pleni\OpenAI\Contexts\Default\Endpoint\Commands;

use Illuminate\Console\Command;
use Plenipotentiary\Laravel\Pleni\OpenAI\Contexts\Default\Endpoint\Actions\CreateCompletionAction;
use Plenipotentiary\Laravel\Pleni\Support\Result;

class CreateCompletionCommand extends Command
{
    protected $signature = 'openai:create-completion 
                            {prompt : The prompt to complete}
                            {--model=text-davinci-003 : OpenAI model to use}
                            {--max-tokens=150 : Maximum tokens to generate}
                            {--temperature=0.7 : Sampling temperature (0-2)}
                            {--top-p=1 : Nucleus sampling parameter (0-1)}
                            {--stop= : Stop sequences (comma-separated)}
                            {--suffix= : Suffix to append to prompt}
                            {--echo : Echo the prompt in the response}
                            {--best-of=1 : Number of completions to generate}
                            {--user= : User identifier for tracking}';

    protected $description = 'Create a text completion using OpenAI';

    public function handle(): int
    {
        $prompt = $this->argument('prompt');
        
        $options = [
            'max_tokens' => (int) $this->option('max-tokens'),
            'temperature' => (float) $this->option('temperature'),
            'top_p' => (float) $this->option('top-p'),
            'best_of' => (int) $this->option('best-of'),
        ];

        // Add optional parameters
        if ($stop = $this->option('stop')) {
            $options['stop'] = array_map('trim', explode(',', $stop));
        }

        if ($suffix = $this->option('suffix')) {
            $options['suffix'] = $suffix;
        }

        if ($this->option('echo')) {
            $options['echo'] = true;
        }

        if ($user = $this->option('user')) {
            $options['user'] = $user;
        }

        $model = $this->option('model');

        $this->info("Creating completion with model: {$model}");
        $this->line("Prompt: {$prompt}");
        $this->newLine();

        $result = CreateCompletionAction::run($prompt, $model, $options);

        return $this->handleResult($result);
    }

    private function handleResult(Result $result): int
    {
        if ($result->isOk()) {
            $data = $result->unwrap();
            
            $this->info('✅ Completion created successfully!');
            $this->newLine();
            
            if (isset($data['choices']) && count($data['choices']) > 0) {
                foreach ($data['choices'] as $index => $choice) {
                    $this->line("Choice " . ($index + 1) . ":");
                    $this->line($choice['text']);
                    $this->newLine();
                }
                
                if (isset($data['usage'])) {
                    $usage = $data['usage'];
                    $this->line("Token usage:");
                    $this->line("  Prompt tokens: {$usage['prompt_tokens']}");
                    $this->line("  Completion tokens: {$usage['completion_tokens']}");
                    $this->line("  Total tokens: {$usage['total_tokens']}");
                }
            }
            
            return Command::SUCCESS;
        }

        if ($result->isInvalid()) {
            $this->error('❌ Validation failed:');
            foreach ($result->violations() as $violation) {
                $this->line("   • {$violation['field']}: {$violation['message']}");
            }
            return Command::FAILURE;
        }

        if ($result->isErr()) {
            $error = $result->error();
            $this->error("❌ Error: {$error['error']}");
            if (isset($error['message'])) {
                $this->line("   Details: {$error['message']}");
            }
            return Command::FAILURE;
        }

        return Command::FAILURE;
    }
}
