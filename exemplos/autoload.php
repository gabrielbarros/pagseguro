<?php
require 'token.php';

spl_autoload_register(function($fullClass) {
    list($namespace, $class) = explode('\\', $fullClass);
    require dirname(__DIR__) . "/src/{$namespace}/{$class}.php";
});
