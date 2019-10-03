<?php

namespace robertogallea\LaravelCodiceFiscale;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;
use robertogallea\LaravelCodiceFiscale\Exceptions\CodiceFiscaleValidationException;

class CodiceFiscaleServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot(Repository $config, CodiceFiscale $codiceFiscale)
    {
        $configPath = $this->packagePath('config/codicefiscale.php');
        $this->publishes([
            $configPath => config_path('codicefiscale.php'),
        ], 'config');

        $this->registerValidator($codiceFiscale);
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $configPath = $this->packagePath('config/codicefiscale.php');
        $this->mergeConfigFrom($configPath, 'codicefiscale');

        $this->app->singleton(CodiceFiscale::class, function (Container $app) {
            return new CodiceFiscale(
                new $app['config']['codicefiscale.city-decoder']()
            );
        });
    }

    public function registerValidator(CodiceFiscale $codiceFiscale)
    {
        Validator::extend('codice_fiscale', function ($attribute, $value, $parameters, $validator) use ($codiceFiscale) {
            $cf = $codiceFiscale;

            try {
                $result = $cf->parse($value);
            } catch (CodiceFiscaleValidationException $exception) {
                switch ($exception->getCode()) {
                    case CodiceFiscaleValidationException::NO_CODE:
                        $error_msg = str_replace([':attribute'], [$attribute], trans('validation.codice_fiscale.no_code'));
                        break;
                    case CodiceFiscaleValidationException::WRONG_SIZE:
                        $error_msg = str_replace([':attribute'], [$attribute], trans('validation.codice_fiscale.wrong_size'));
                        break;
                    case CodiceFiscaleValidationException::BAD_CHARACTERS:
                        $error_msg = str_replace([':attribute'], [$attribute], trans('validation.codice_fiscale.wrong_size'));
                        break;
                    case CodiceFiscaleValidationException::BAD_OMOCODIA_CHAR:
                        $error_msg = str_replace([':attribute'], [$attribute], trans('validation.codice_fiscale.wrong_size'));
                        break;
                    case CodiceFiscaleValidationException::WRONG_CODE:
                        $error_msg = str_replace([':attribute'], [$attribute], trans('validation.codice_fiscale.wrong_code'));
                        break;
                }

                $validator->addReplacer('codice_fiscale', function ($message, $attribute, $rule, $parameters) use ($error_msg) {
                    return str_replace([':attribute'], [$attribute], str_replace('codice fiscale', ':attribute', $error_msg));
                });

                return false;
            }

            return true;
        });
    }

    private function packagePath($path)
    {
        return dirname(__DIR__)."/$path";
    }
}
