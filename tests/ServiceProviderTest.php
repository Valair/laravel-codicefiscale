<?php

use Orchestra\Testbench\TestCase;
use robertogallea\LaravelCodiceFiscale\CodiceFiscale;

class ServiceProviderTest extends TestCase
{
    protected function getPackageProviders($app): array
    {
        return [robertogallea\LaravelCodiceFiscale\CodiceFiscaleServiceProvider::class];
    }

    public function testServiceRegistered(): void
    {
        $provider = $this->app->make(CodiceFiscale::class);
        $this->assertInstanceOf(CodiceFiscale::class, $provider);
    }
}
