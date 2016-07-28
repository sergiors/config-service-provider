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

        $app['db.options'] = [
            'driver' => null,
        ];
        $app['foo.options'] = [
            'foo' => [
                'bar' => null,
                'beer' => [],
            ],
        ];
        $app->register(new ConfigServiceProvider(), [
            'config.filenames' => '%root_dir%/config_%env%.yml',
            'config.replacements' => [
                'env' => 'dev',
                'root_dir' => dirname(__DIR__).'/app',
                'users' => 'fake'
            ]
        ]);
        $app['config.initializer']();

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
        $app->register(new ConfigServiceProvider(), [
            'config.filenames' => __DIR__.'/../app/config.php',
            'config.replacements' => [
                'root_dir' => dirname(__DIR__)
            ]
        ]);
        $app['config.initializer']();

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
            'config.filenames' => dirname(__DIR__).'/app/sub/',
             'config.replacements' => [
                 'root_dir' => dirname(__DIR__)
             ]
        ]);
        $app['config.initializer']();

        $this->assertCount(1, $app['twig.options']);
        $this->assertCount(1, $app['router']);
        $this->assertCount(5, $app['db.options']);
        $this->assertEquals($app['router']['resource'], dirname(__DIR__).'/app/routing.yml');
    }

    /**
     * @test
     */
    public function shouldLoadObject()
    {
        $app = $this->createApplication();

        $app->register(new ConfigServiceProvider(), [
            'config.filenames' => '%root_dir%/config_%env%.yml',
            'config.replacements' => [
                'env' => 'dev',
                'root_dir' => dirname(__DIR__).'/app',
                'users' => function () {
                    return new \stdClass();
                }
            ]
        ]);
        $app['config.initializer']();

        $this->assertInstanceOf(\stdClass::class, $app['users']);
    }

    public function createApplication()
    {
        return new Container();
    }
}
