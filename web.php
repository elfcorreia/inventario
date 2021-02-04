<?php 

require_once "vendor/autoload.php";
require_once "config.php";
require_once "routes.php";

use function routing\find;

try {
    $r = find($_SERVER['REQUEST_URI']);
    if ($r->getRoute() === null) {
        http_response_code(403);
        echo 'Bad Request';
        return;
    }
    call_user_func_array($r->getRoute()->getUserdata(), $r->getArgs());
} catch (Exception $e) {
    http_response_code(500);
    echo "<h1>Internal Error</h1>";
    echo "<p>";
    echo "<h2>";
    echo $e->getMessage();
    echo "</h2>";
    echo "<pre>";
    echo get_class($e);
    echo "</pre>";
    echo "</p>";
    echo "<pre>";
    echo $e->getTraceAsString();
}