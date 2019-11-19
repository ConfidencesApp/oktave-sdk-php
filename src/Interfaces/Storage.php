<?php

namespace Oktave\Interfaces;

interface Storage
{

    public function getKey(string $key, $default = null);

    public function setKey(string $key, $value);

    public function removeKey(string $key);

}
