Install
-------
```
composer require inbep/config-service-provider
```

How to use
----------
Let's imagine you want to load the following yaml file:

```yaml
imports:
    - { resource: config.yml }

twig.options:
    debug: false

db.options:
      driver: ~
      host: ~
      user: ~
      password: ~
      dbname: ~

router:
    resource: %root_dir%/app/routing.yml
```

In your php file
```php
$app->register(use Inbep\Silex\Provider\ConfigServiceProvider(), [
    'config.replacements' => [
        'root_dir' => dirname(__DIR__)
    ]
]);

$app['config.loader']->load('app/config_dev.yml');
```

Now you can access `$app['twig.options']['debug']` and others

License
-------
MIT
