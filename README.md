Config Service Provider
-----------------------

Install
-------
```
composer require sergiors/config-service-provider
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

routing.options:
    paths: %root_dir%/app/routing.yml
```

In your php file
```php
use Sergiors\Silex\Provider\ConfigServiceProvider;

$app['config.filenames'] = '%root_dir%/config/config_%env%.yml';
$app['config.replacements' = [
    'root_dir' => dirname(__DIR__)
];
$app->register(new ConfigServiceProvider());
```

Now you can access `$app['twig.options']['debug']` and others

License
-------
MIT
