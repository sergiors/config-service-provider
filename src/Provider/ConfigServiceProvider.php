<?php
namespace Inbep\Silex\Provider;

use Silex\Application;
use Silex\ServiceProviderInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\LoaderResolver;
use Symfony\Component\Config\Loader\DelegatingLoader;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Inbep\Silex\DependencyInjection\Loader\YamlFileLoader;

/**
 * @author Sérgio Rafael Siqueira <sergio@inbep.com.br>
 */
class ConfigServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app['config.replacements'] = [];

        $app['config.parameters'] = $app->share(function (Application $app) {
            return new ParameterBag($app['config.replacements']);
        });

        $app['config.loader.yml'] = $app->share(function (Application $app) {
            return new YamlFileLoader($app, new FileLocator());
        });

        $app['config.resolver'] = $app->share(function (Application $app) {
            return new LoaderResolver([
                $app['config.loader.yml']
            ]);
        });

        $app['config.loader'] = $app->share(function (Application $app) {
            return new DelegatingLoader($app['config.resolver']);
        });
    }

    public function boot(Application $app)
    {
    }
}
