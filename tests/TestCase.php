<?php

namespace Esoboy\Ui\Tests;

use Esoboy\Ui\Assets;
use Esoboy\Ui\UiServiceProvider;
use Livewire\Blaze\BlazeServiceProvider;
use Livewire\LivewireServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            LivewireServiceProvider::class,
            BlazeServiceProvider::class,
            UiServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('app.key', 'base64:'.base64_encode(str_repeat('u', 32)));
    }

    protected function setUp(): void
    {
        parent::setUp();

        // dist/ is a build output; tests read a fixed one.
        $this->app->instance(Assets::class, new Assets(__DIR__.'/Fixtures/dist'));

        // Livewire keeps per-request state (@assets already printed, …) in statics.
        app('livewire')->flushState();

        $this->artisan('view:clear');
    }
}
