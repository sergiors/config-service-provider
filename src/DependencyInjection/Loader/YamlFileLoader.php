<?php

namespace Sergiors\Silex\DependencyInjection\Loader;

use Symfony\Component\Yaml\Parser as YamlParser;
use Symfony\Component\Yaml\Exception\ParseException;

/**
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Sérgio Rafael Siqueira <sergio@inbep.com.br>
 */
class YamlFileLoader extends FileLoader
{
    /**
     * @var YamlParser
     */
    private $yamlParser;

    /**
     * {@inheritdoc}
     */
    public function load($resource, $type = null)
    {
        $path = $this->locator->locate($resource);

        $content = $this->loadFile($path);

        // empty file
        if (null === $content) {
            return;
        }

        // imports
        $this->parseImports($content, $path);

        $content = $this->container['config.parameters']->resolveValue($content);

        foreach ($content as $namespace => $values) {
            if (in_array($namespace, ['imports'])) {
                continue;
            }

            if ($this->container->offsetExists($namespace) && is_array($values)) {
                $values = $this->merge($this->container[$namespace], $values);
            }

            $this->container[$namespace] = $values;
        }

        return $content;
    }

    /**
     * @param array $current
     * @param array $new
     *
     * @return array
     */
    private function merge(array $current, array $new)
    {
        foreach ($new as $key => $value) {
            if (isset($current[$key]) && is_array($value)) {
                $current[$key] = $this->merge($current[$key], $value);
                continue;
            }

            $current[$key] = $value;
        }

        return $current;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($resource, $type = null)
    {
        return is_string($resource) && in_array(pathinfo($resource, PATHINFO_EXTENSION), ['yml', 'yaml'], true);
    }

    /**
     * Parses all imports.
     *
     * @param array  $content
     * @param string $file
     */
    private function parseImports(&$content, $file)
    {
        if (!isset($content['imports'])) {
            return;
        }

        if (!is_array($content['imports'])) {
            throw new \InvalidArgumentException(
                sprintf('The "imports" key should contain an array in %s. Check your YAML syntax.', $file)
            );
        }

        foreach ($content['imports'] as $import) {
            if (!is_array($import)) {
                throw new \InvalidArgumentException(
                    sprintf('The values in the "imports" key should be arrays in %s. Check your YAML syntax.', $file)
                );
            }

            $ignoreErrors = isset($import['ignore_errors']) ? (bool) $import['ignore_errors'] : false;

            $this->setCurrentDir(dirname($file));
            $this->import($import['resource'], null, $ignoreErrors, $file);
        }
    }

    /**
     * Loads a YAML file.
     *
     * @param string $file
     *
     * @return array The file content
     *
     * @throws \InvalidArgumentException when the given file is not a local file or when it does not exist
     */
    protected function loadFile($file)
    {
        if (!class_exists('Symfony\Component\Yaml\Parser')) {
            throw new \RuntimeException(
                'Unable to load YAML config files as the Symfony Yaml Component is not installed.'
            );
        }

        if (!stream_is_local($file)) {
            throw new \InvalidArgumentException(sprintf('This is not a local file "%s".', $file));
        }

        if (!file_exists($file)) {
            throw new \InvalidArgumentException(sprintf('The service file "%s" is not valid.', $file));
        }

        if (null === $this->yamlParser) {
            $this->yamlParser = new YamlParser();
        }

        try {
            $configuration = $this->yamlParser->parse(file_get_contents($file));
        } catch (ParseException $e) {
            throw new \InvalidArgumentException(sprintf('The file "%s" does not contain valid YAML.', $file), 0, $e);
        }

        return $this->validate($configuration, $file);
    }

    /**
     * Validates a YAML file.
     *
     * @param mixed  $content
     * @param string $file
     *
     * @return array
     *
     * @throws InvalidArgumentException When service file is not valid
     */
    private function validate($content, $file)
    {
        if (null === $content) {
            return $content;
        }

        if (!is_array($content)) {
            throw new \InvalidArgumentException(sprintf(
                'The service file "%s" is not valid. It should contain an array. Check your YAML syntax.',
                $file
            ));
        }

        foreach ($content as $namespace => $data) {
            if (in_array($namespace, array('imports'))) {
                continue;
            }
        }

        return $content;
    }
}
