<?php

/** @copyright Sven Ullmann <kontakt@sumedia-webdesign.de> **/

namespace BricksFramework\ArrayKeyParser;

interface ArrayKeyParserInterface
{
    public function escape(string $key) : string;

    public function unescape(string $key) : string;

    public function get(array &$array, string $path);

    public function set(array &$array, string $path, $value) : void;

    public function remove(array &$array, string $path) : void;

    public function has(array &$array, string $path) : bool;
}
