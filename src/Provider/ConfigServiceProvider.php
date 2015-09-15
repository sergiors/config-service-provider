<?php
namespace Inbep\Silex\Provider;

use Silex\Application;
use Silex\ServiceProviderInterface;
use Symfony\Component\Config\FileLocator;

/**
 * @author Sérgio Rafael Siqueira <sergio@inbep.com.br>
 */
class ConfigServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app['paths'] = [];

        $app['file_locator'] = $app->share(function (Application $app) {
            return new FileLocator($app['paths']);
        });
    }

    public function boot(Application $app)
    {
    }
}
