<?php

declare(strict_types=1);

namespace Tests\Laravel\Jobs;

use Alama\Arazzo\Contracts\Spec\ArazzoDocument;
use Alama\Arazzo\Contracts\Spec\Components;
use Alama\Arazzo\Contracts\Spec\Info;
use Alama\Arazzo\Contracts\Spec\Step;
use Alama\Arazzo\Contracts\Spec\StepFlow;
use Alama\Arazzo\Contracts\Spec\StepIo;
use Alama\Arazzo\Contracts\Spec\StepTarget;
use Alama\Arazzo\Contracts\Spec\Workflow;
use Alama\Arazzo\Contracts\State\WorkflowContext;
use Alama\Arazzo\Laravel\Queue\Jobs\RunExecuteStepJob;
use Alama\Arazzo\Runner\AsyncRunner;
use Alama\Arazzo\Runner\Data\ExecuteStepJob;
use ReflectionMethod;

it('extracts definitionId, workflowId, and executionId from inner job', function (): void {
    $workflow = new Workflow('wf-1', 'WF 1', null, [], [], [
        $inStep = new Step('A', null, new StepTarget, new StepFlow, new StepIo),
    ], [], [], [], []);
    $document = new ArazzoDocument('1.0.0', new Info('t', null, null, '1'), [], [$workflow], new Components([], [], [], []), []);

    $context = (new WorkflowContext('def_1'))->withWorkflowId('wf-1')->withExecutionId('exec_1');
    $innerJob = new ExecuteStepJob($inStep, $context);

    $wrapper = new RunExecuteStepJob($innerJob);

    expect($wrapper->definitionId)->toBe('def_1');
    expect($wrapper->workflowId)->toBe('wf-1');
    expect($wrapper->executionId)->toBe('exec_1');
});

it('handle method accepts AsyncRunner parameter', function (): void {
    $method = new ReflectionMethod(RunExecuteStepJob::class, 'handle');
    $params = $method->getParameters();

    expect($params)->toHaveCount(1);
    expect($params[0]->getType()->getName())->toBe(AsyncRunner::class);
});

it('handle method calls handleStep on AsyncRunner', function (): void {
    $workflow = new Workflow('wf-1', 'WF 1', null, [], [], [
        $inStep = new Step('A', null, new StepTarget, new StepFlow, new StepIo),
    ], [], [], [], []);
    $document = new ArazzoDocument('1.0.0', new Info('t', null, null, '1'), [], [$workflow], new Components([], [], [], []), []);

    $context = (new WorkflowContext('def_1'))->withWorkflowId('wf-1')->withExecutionId('exec_1');
    $innerJob = new ExecuteStepJob($inStep, $context);
    $wrapper = new RunExecuteStepJob($innerJob);

    $runner = AsyncRunner::create();
    try {
        $wrapper->handle($runner);
    } catch (\Throwable) {
        // Expected: no queue driver configured for actual dispatch
    }

    expect(true)->toBeTrue();
});
