<?php

use Plenipotentiary\Laravel\Pleni\OpenAI\Contexts\Default\Endpoint\Adapter\OpenAIAdapter;
use Plenipotentiary\Laravel\Contracts\Client\HttpProviderClientContract;
use Plenipotentiary\Laravel\Contracts\Error\ErrorMapperContract;
use Plenipotentiary\Laravel\Support\Result;
use Psr\Log\LoggerInterface;

beforeEach(function () {
    $this->client = Mockery::mock(HttpProviderClientContract::class);
    $this->errorMapper = Mockery::mock(ErrorMapperContract::class);
    $this->logger = Mockery::mock(LoggerInterface::class);

    $this->adapter = new OpenAIAdapter($this->client, $this->errorMapper, $this->logger);
});

afterEach(function () {
    Mockery::close();
});

it('validates missing model and prompt for createCompletion', function () {
    $result = $this->adapter->validate('createCompletion', []);

    expect($result->isInvalid())->toBeTrue();
    $violations = $result->violations();
    expect(array_column($violations, 'field'))->toContain('model')
        ->toContain('prompt');
});

it('validates missing messages array for createChatCompletion', function () {
    $result = $this->adapter->validate('createChatCompletion', ['model' => 'gpt-3.5-turbo']);

    expect($result->isInvalid())->toBeTrue();
    $violations = $result->violations();
    expect(array_column($violations, 'field'))->toContain('messages');
});
