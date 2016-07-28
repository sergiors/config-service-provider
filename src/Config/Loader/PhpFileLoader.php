<?php

namespace Sergiors\Silex\Config\Loader;

/**
 * @author Sérgio Rafael Siqueira <sergio@inbep.com.br>
 */
class PhpFileLoader extends FileLoader
{
    /**
     * {@inheritdoc}
     */
    public function load($resource, $type = null)
    {
        // the container and loader variables are exposed to the included file below
        $container = $this->container;
        $loader = $this;
        $path = $this->locator->locate($resource);
        $this->setCurrentDir(dirname($path));
        include $path;
    }
    /**
     * {@inheritdoc}
     */
    public function supports($resource, $type = null)
    {
        return is_string($resource) && 'php' === pathinfo($resource, PATHINFO_EXTENSION);
    }
}
