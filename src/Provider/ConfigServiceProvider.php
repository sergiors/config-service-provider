<?php

namespace Sergiors\Silex\Provider;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\LoaderResolver;
use Symfony\Component\Config\Loader\DelegatingLoader;
use Sergiors\Silex\Loader\YamlFileLoader;
use Sergiors\Silex\Loader\PhpFileLoader;
use Sergiors\Silex\Loader\DirectoryLoader;

/**
 * @author Sérgio Rafael Siqueira <sergio@inbep.com.br>
 */
class ConfigServiceProvider implements ServiceProviderInterface
{
    public function register(Container $app)
    {
        $app['config.initializer'] = $app->protect(function () use ($app) {
            static $initialized = false;

            if ($initialized) {
                return;
            }

            $initialized = true;

            $paths = (array) $app['config.options']['paths'];

            foreach ($paths as $path) {
                $path = $app['config.replacements']($path);
                $app['config.loader']->load($path);
            }
        });

        $app['config.replacements'] = $app->protect(function ($value) use ($app) {
            $replacements = $app['config.options']['replacements'];

            if ([] === $replacements) {
                return $value;
            }

            if (is_array($value)) {
                foreach ($value as $k => $v) {
                    $value[$k] = $app['config.replacements']($v);
                }

                return $value;
            }

            if (!is_string($value)) {
                return $value;
            }

            return $this->resolveString($value, $replacements);
        });

        $app['config.locator'] = function () {
            return new FileLocator();
        };

        $app['config.loader.yml'] = $app->factory(function (Container $app) {
            return new YamlFileLoader($app, $app['config.locator']);
        });

        $app['config.loader.php'] = $app->factory(function (Container $app) {
            return new PhpFileLoader($app, $app['config.locator']);
        });

        $app['config.loader.directory'] = $app->factory(function (Container $app) {
            return new DirectoryLoader($app, $app['config.locator']);
        });

        $app['config.loader.resolver'] = function (Container $app) {
            return new LoaderResolver([
                $app['config.loader.yml'],
                $app['config.loader.directory'],
                $app['config.loader.php'],
            ]);
        };

        $app['config.loader'] = function (Container $app) {
            return new DelegatingLoader($app['config.loader.resolver']);
        };

        $app['config.initializer']();

        $app['config.options'] = [
            'paths' => [],
            'replacements' => []
        ];
    }

    private function resolveString($value, array $replacements)
    {
        return preg_replace_callback('/%%|%([^%\s]+)%/', function ($match) use ($replacements) {
            // skip %%
            if (!isset($match[1])) {
                return '%%';
            }

            $key = strtolower($match[1]);

            return $replacements[$key];
        }, $value);
    }
}
