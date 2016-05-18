<?php

namespace Sergiors\Silex\DependencyInjection\Loader;

use Pimple\Container;
use Symfony\Component\Config\Loader\FileLoader as BaseFileLoader;
use Symfony\Component\Config\FileLocatorInterface;

/**
 * @author Sérgio Rafael Siqueira <sergio@inbep.com.br>
 */
abstract class FileLoader extends BaseFileLoader
{
    /**
     * @var Container
     */
    protected $container;

    /**
     * Constructor.
     *
     * @param Container            $container A Pimple instance
     * @param FileLocatorInterface $locator A FileLocator instance
     */
    public function __construct(Container $container, FileLocatorInterface $locator)
    {
        $this->container = $container;

        parent::__construct($locator);
    }
}
