<?php

/** @copyright Sven Ullmann <kontakt@sumedia-webdesign.de> **/

declare(strict_types=1);

namespace BricksFramework\ArrayKeyParser;

class ArrayKeyParser implements ArrayKeyParserInterface, ArrayKeyParserConfigInterface
{
    const DEFAULT_SEPARATOR = '.';

    const DEFAULT_ESCAPE = '\\';

    const DEFAULT_INDEX_IDENTIFIER = '<i>';

    /**
     * Separate your array <> path: $a['key']['value'] <> key.value
     *
     * @var string
     */
    protected $separator = self::DEFAULT_SEPARATOR;

    /**
     * Use to escape a dot in your key: my.path.image\.jpg
     *
     * @var string
     */
    protected $escape = self::DEFAULT_ESCAPE;

    /**
     * Usage: your.<i>.path = $a[your][0][path] / $a[your][1][path] / ...
     *
     * @var string
     */
    protected $indexIdentifier = self::DEFAULT_INDEX_IDENTIFIER;

    public function setSeparator(string $separator) : void
    {
        $this->separator = $separator;
    }

    public function getSeparator() : string
    {
        return $this->separator;
    }

    public function setEscape(string $escape) : void
    {
        $this->escape = $escape;
    }

    public function getEscape() : string
    {
        return $this->escape;
    }

    public function setIndexIdentifier(string $identifier) : void
    {
        $this->indexIdentifier = $identifier;
    }

    public function getIndexIdentifier() : string
    {
        return $this->indexIdentifier;
    }

    public function escape(string $key) : string
    {
        return str_replace($this->getSeparator(), $this->getEscape() . $this->getSeparator(), $key);
    }

    public function unescape(string $key) : string
    {
        $string = $key;
        $dblEscape = '<dbl-escape>';
        $i = 0;
        while (strstr($string, $dblEscape)) {
            $dblEscape = '<dbl-escape-' . (++$i) . '>';
        }

        $string = str_replace($this->getEscape() . $this->getEscape(), $dblEscape, $string);
        $string = str_replace($this->getEscape() . $this->getSeparator(), $this->getSeparator(), $string);
        $string = str_replace($dblEscape, $this->getEscape(), $string);

        return $string;
    }

    /**
     * @throws \Bricks\ArrayKeyParser\Exception\KeyNotExistsException 
     */
    public function get(array &$array, string $path)
    {
        $parts = $this->splitPath($path);
        return $this->getValue($array, $parts);
    }

    /**
     * @throws \Bricks\ArrayKeyParser\Exception\KeyNotExistsException 
     */
    protected function getValue(array &$array, array $parts)
    {
        $current = &$array;
        foreach ($parts as $key) {
            if (!isset($current[$key])) {
                throw new \Bricks\ArrayKeyParser\Exception\KeyNotExistsException('Given key ' . $key . ' not exists');
            }
            $current = &$current[$key];
        }
        return $current;
    }

    public function set(array &$array, string $path, $value) : void
    {
        $parts = $this->splitPath($path);
        $this->setValue($array, $parts, $value);
    }

    /**
     * @throws \Bricks\ArrayKeyParser\Exception\InvalidPathException 
     */
    protected function setValue(array &$array, array $parts, $value) : void
    {
        if (empty($parts)) {
            throw new \Bricks\ArrayKeyParser\Exception\InvalidPathException('path does not contain any parts');
        }

        $current = &$array;
        $count = count($parts);

        foreach ($parts as $i => $key) {
            if ($this->isIndex($key)) {
                $key = $this->getNextIndex($current);
            }
            if (!isset($current[$key])) {
                $current[$key] = $i + 1 == $count ? null : [];
            }
            $current = &$current[$key];
        }
        if (is_array($current) && !is_array($value)) {
            return;
        } elseif(is_array($current)) {
            $current = array_replace_recursive($current, $value);
        } else {
            $current = $value;
        }
    }

    public function remove(array &$array, string $path) : void
    {
        $parts = $this->splitPath($path);
        $this->removeValue($array, $parts);
    }

    /**
     * @throws \Bricks\ArrayKeyParser\Exception\KeyNotExistsException 
     */
    protected function removeValue(array &$array, array $parts)
    {
        $current = &$array;
        foreach ($parts as $key) {
            if (!isset($current[$key])) {
                throw new \Bricks\ArrayKeyParser\Exception\KeyNotExistsException('Given key ' . $key . ' not exists');
            }
            $current = &$current[$key];
        }
        unset($current);
    }

    public function has(array &$array, string $path) : bool
    {
        $parts = $this->splitPath($path);
        return $this->hasValue($array, $parts);
    }

    protected function hasValue(array &$array, array $parts) : bool
    {
        $current = &$array;
        foreach ($parts as $key) {
            if (!isset($current[$key])) {
                return false;
            }
        }
        return true;
    }

    protected function splitPath(string $path) : array
    {
        $string = $path;
        $dblEscape = '<dbl-escape>';
        $i = 0;
        while (strstr($string, $dblEscape)) {
            $dblEscape = '<dbl-escape-' . (++$i) . '>';
        }

        $separator = '<separator>';
        $i = 0;
        while (strstr($string, $separator)) {
            $separator = '<separator-' . (++$i) . '>';
        }

        $string = str_replace($this->getEscape() . $this->getEscape(), $dblEscape, $string);
        $string = str_replace($this->getEscape() . $this->getSeparator(), $separator, $string);

        $parts = explode($this->getSeparator(), $string);
        foreach ($parts as &$part) {
            $part = str_replace($separator, $this->getSeparator(), $part);
            $part = str_replace($dblEscape, $this->getEscape(), $part);
        }

        return $parts;
    }

    protected function getNextIndex(array &$current) : int
    {
        $i = 0;
        foreach (array_keys($current) as $_key) {
            if (is_int($_key) && $i <= $_key) {
                $i = $_key + 1;
            }
        }
        return $i;
    }

    protected function isIndex(string $part) : bool
    {
        if ($part === $this->getIndexIdentifier()) {
            return true;
        }
        return false;
    }
}
