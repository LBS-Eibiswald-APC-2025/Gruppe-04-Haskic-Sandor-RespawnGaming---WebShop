<?php

ini_set('display_errors', 1);         // Nur während der Entwicklung
ini_set('error_reporting', E_ALL);
ini_set('error_prepend_string', '<div class="php-error">');
ini_set('error_append_string', '</div>');

require '../vendor/autoload.php';

// start our app
new Application();