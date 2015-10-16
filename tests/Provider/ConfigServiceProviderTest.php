<?php
namespace Inbep\Silex\Provider;

use Silex\Application;
use Silex\WebTestCase;
use Symfony\Component\Config\FileLocator;

class ConfigServiceProviderTest extends WebTestCase
{
    /**
     * @test
     */
    public function register()
    {
        $app = $this->createApplication();
        $app->register(new ConfigServiceProvider(), [
            'config.replacements' => [
                'root_dir' => dirname(__DIR__)
            ]
        ]);

        $app['config.loader']->load(__DIR__.'/../app/config_dev.yml');

        $this->assertCount(1, $app['twig.options']);
        $this->assertCount(6, $app['db.options']);
        $this->assertEquals($app['router']['resource'], dirname(__DIR__).'/app/routing.yml');
        $this->assertEquals($app['config.parameters']->get('root_dir'), dirname(__DIR__));
    }

    public function createApplication()
    {
        $app = new Application();
        $app['debug'] = true;
        $app['exception_handler']->disable();

        return $app;
    }
}
