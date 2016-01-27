<?php

namespace Sergiors\Silex\DependencyInjection\Loader;

/**
 * @author Sebastien Lavoie <seb@wemakecustom.com>
 * @author Sérgio Rafael Siqueira <sergio@inbep.com.br>
 *
 * @see https://github.com/symfony/dependency-injection/blob/master/Loader/DirectoryLoader.php
 */
class DirectoryLoader extends FileLoader
{
    /**
     * {@inheritdoc}
     */
    public function load($file, $type = null)
    {
        $file = rtrim($file, '/');
        $path = $this->locator->locate($file);

        foreach (scandir($path) as $dir) {
            if ('.' !== $dir[0]) {
                if (is_dir($path.'/'.$dir)) {
                    $dir .= '/'; // append / to allow recursion
                }
                $this->setCurrentDir($path);
                $this->import($dir, null, false, $path);
            }
        }
    }
    /**
     * {@inheritdoc}
     */
    public function supports($resource, $type = null)
    {
        if ('directory' === $type) {
            return true;
        }

        return null === $type && is_string($resource) && '/' === substr($resource, -1);
    }
}
