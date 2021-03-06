<?php

namespace Sergiors\Silex\Provider;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\LoaderResolver;
use Symfony\Component\Config\Loader\DelegatingLoader;
use Sergiors\Silex\Config\Loader\YamlFileLoader;
use Sergiors\Silex\Config\Loader\PhpFileLoader;
use Sergiors\Silex\Config\Loader\DirectoryLoader;
use Symfony\Component\DependencyInjection\ParameterBag\EnvPlaceholderParameterBag;

/**
 * @author Sérgio Rafael Siqueira <sergio@inbep.com.br>
 */
class ConfigServiceProvider implements ServiceProviderInterface
{
    /**
     * @param Container $app
     */
    public function register(Container $app)
    {
        $app['config.initializer'] = $app->protect(function () use ($app) {
            static $initialized = false;

            if ($initialized) {
                return;
            }

            $initialized = true;

            $filenames = (array) $app['config.filenames'];

            foreach ($filenames as $path) {
                $path = $app['config.replacements.resolver']($path);
                $app['config.loader']->load($path);
            }
        });

        $app['config.replacements.resolver'] = $app->protect(function ($value) use ($app) {
            $replacements = $app['config.replacements'];

            if ([] === $replacements) {
                return $value;
            }

            if (is_array($value)) {
                return array_map(function ($value) use ($app) {
                    return $app['config.replacements.resolver']($value);
                }, $value);
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

        $app['config.filenames'] = [];
        $app['config.replacements'] = [];
    }

    /**
     * @param string $value
     * @param array  $replacements
     *
     * @return mixed
     */
    private function resolveString($value, array $replacements)
    {
        $parameterBag = new EnvPlaceholderParameterBag($replacements);
        return $parameterBag->resolveValue($value);
    }
}
