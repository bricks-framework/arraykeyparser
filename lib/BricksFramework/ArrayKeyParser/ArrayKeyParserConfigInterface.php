<?php

/** @copyright Sven Ullmann <kontakt@sumedia-webdesign.de> **/

namespace BricksFramework\ArrayKeyParser;

interface ArrayKeyParserConfigInterface
{
    public function getSeparator() : string;

    public function setEscape(string $escape) : void;

    public function getEscape() : string;

    public function setIndexIdentifier(string $identifier) : void;

    public function getIndexIdentifier() : string;
}
