<?php

use App\Http\Controllers\MainController;

$controller = new MainController();

switch ($uri) {
    case '/':
        echo $controller->loginView();
        break;

    case '/login':
        echo $controller->login($_POST);
        break;

    case '/polls':
        echo $controller->polls();
        break;

    case (preg_match('/\/poll\/(\d+)/', $uri, $m) ? true : false):
        echo $controller->pollView($m[1]);
        break;

    case '/vote':
        echo $controller->vote($_POST);
        break;

    case (preg_match('/\/results\/(\d+)/', $uri, $m) ? true : false):
        echo $controller->results($m[1]);
        break;

    case '/admin':
        echo $controller->admin();
        break;

    case '/release':
        echo $controller->release($_POST);
        break;

    default:
        http_response_code(404);
        echo "404 Not Found";
}
