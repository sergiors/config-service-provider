<?php

namespace Sergiors\Silex\Tests\Provider;

use Pimple\Container;
use Sergiors\Silex\Provider\ConfigServiceProvider;

class ConfigServiceProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function register()
    {
        $app = $this->createApplication();
        $app['config.paths'] = __DIR__.'/../app/config_dev.yml';
        $app['config.replacements'] = [
            'root_dir' => dirname(__DIR__)
        ];
        $app['db.options'] = [
            'driver' => null,
        ];
        $app['foo.options'] = [
            'foo' => [
                'bar' => null,
                'beer' => [],
            ],
        ];
        $app->register(new ConfigServiceProvider());

        $this->assertCount(1, $app['twig.options']);
        $this->assertCount(6, $app['db.options']);
        $this->assertEquals($app['router']['resource'], dirname(__DIR__).'/app/routing.yml');
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
        $app['config.paths'] = __DIR__.'/../app/config.php';
        $app['config.replacements'] = [
            'root_dir' => dirname(__DIR__)
        ];
        $app->register(new ConfigServiceProvider());

        $this->assertCount(1, $app['twig.options']);
        $this->assertArrayHasKey('debug', $app['twig.options']);
    }

    /**
     * @test
     */
    public function shouldLoadFileDirectory()
    {
        $app = $this->createApplication();
        $app['config.paths'] =  dirname(__DIR__).'/app/sub/';
        $app['config.replacements'] = [
            'root_dir' => dirname(__DIR__)
        ];
        $app->register(new ConfigServiceProvider());

        $this->assertCount(1, $app['twig.options']);
        $this->assertCount(1, $app['router']);
        $this->assertCount(5, $app['db.options']);
        $this->assertEquals($app['router']['resource'], dirname(__DIR__).'/app/routing.yml');
    }

    public function createApplication()
    {
        return new Container();
    }
}
