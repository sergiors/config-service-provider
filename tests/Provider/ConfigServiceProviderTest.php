<?php
namespace Sergiors\Silex\Provider;

use Silex\Application;
use Silex\WebTestCase;

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
        $app['db.options'] = [
            'driver' => null
        ];
        $app['foo.options'] = [
            'foo' => [
                'bar' => null,
                'beer' => []
            ]
        ];

        $app['config.loader']->load(__DIR__.'/../app/config_dev.yml');

        $this->assertCount(1, $app['twig.options']);
        $this->assertCount(6, $app['db.options']);
        $this->assertEquals($app['router']['resource'], dirname(__DIR__).'/app/routing.yml');
        $this->assertEquals($app['config.parameters']->get('root_dir'), dirname(__DIR__));
        $this->assertEquals('pdo_pgsql', $app['db.options']['driver']);
        $this->assertEquals('beer', $app['foo.options']['foo']['bar']);
        $this->assertCount(2, $app['foo.options']['foo']['beer']);
    }

    /**
     * @test
     */
    public function shouldSupportPhpFile()
    {
        $app = $this->createApplication();
        $app->register(new ConfigServiceProvider(), [
            'config.replacements' => [
                'root_dir' => dirname(__DIR__)
            ]
        ]);

        $app['config.loader']->load(__DIR__.'/../app/config.php');
        $this->assertCount(1, $app['twig.options']);
        $this->assertArrayHasKey('debug', $app['twig.options']);
    }

    /**
     * @test
     */
    public function shouldLoadFileDirectory()
    {
        $app = $this->createApplication();
        $app->register(new ConfigServiceProvider(), [
            'config.replacements' => [
                'root_dir' => dirname(__DIR__)
            ]
        ]);

        $app['config.loader']->load(dirname(__DIR__).'/app/sub/');

        $this->assertCount(1, $app['twig.options']);
        $this->assertCount(1, $app['router']);
        $this->assertCount(5, $app['db.options']);
        $this->assertEquals($app['router']['resource'], dirname(__DIR__).'/app/routing.yml');
    }

    public function createApplication()
    {
        $app = new Application();
        $app['debug'] = true;
        $app['exception_handler']->disable();

        return $app;
    }
}
