<?php

namespace Sergiors\Silex\Provider;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\LoaderResolver;
use Symfony\Component\Config\Loader\DelegatingLoader;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Sergiors\Silex\DependencyInjection\Loader\YamlFileLoader;
use Sergiors\Silex\DependencyInjection\Loader\PhpFileLoader;
use Sergiors\Silex\DependencyInjection\Loader\DirectoryLoader;

/**
 * @author Sérgio Rafael Siqueira <sergio@inbep.com.br>
 */
class ConfigServiceProvider implements ServiceProviderInterface
{
    public function register(Container $app)
    {
        $app['config.parameters'] = function () use ($app) {
            return new ParameterBag($app['config.replacements']);
        };

        $app['config.loader.yml'] = $app->factory(function (Container $app) {
            return new YamlFileLoader($app, new FileLocator());
        });

        $app['config.loader.php'] = $app->factory(function (Container $app) {
            return new PhpFileLoader($app, new FileLocator());
        });

        $app['config.loader.directory'] = $app->factory(function (Container $app) {
            return new DirectoryLoader($app, new FileLocator());
        });

        $app['config.resolver'] =function () use ($app) {
            return new LoaderResolver([
                $app['config.loader.yml'],
                $app['config.loader.directory'],
                $app['config.loader.php'],
            ]);
        };

        $app['config.loader'] = function () use ($app) {
            return new DelegatingLoader($app['config.resolver']);
        };

        $app['config.replacements'] = [];
    }
}
