<?php
//define constants in the enviroment
define('__PATH__', __DIR__ . '/../');

require_once "../lib/vendor/autoload.php";
require_once "../lib/utils.php";
require_once "../app/config.php";

$klein = new \Klein\Klein();

$klein->service()->layout(__PATH__ . "/app/view/layouts/default.phtml");

//set home site
$klein->respond("GET", "/?", function ($request, $response, $service) {
    //header params
    $service->pageTitle = "My title Application";
    
    //content params
    $service->title = "My simple App with PHP";
    
    //render
//    $service->layout(__PATH__ . "/app/view/layouts/default.phtml");
    $service->render(__PATH__ . "/app/view/home/home.phtml");
});

//set namespaces
foreach(app_configs::getNamespaces() as $controller) {
    // Include all routes defined in a file under a given namespace
    $klein->with("/{$controller}", __PATH__ . "/app/controller/{$controller}.php");
}

// Using exact code behaviors via switch/case
$klein->onHttpError(function ($code, $router) {
    //$router is a Klein object
    switch ($code) {
        case 404:
            $router->service()->layout(__PATH__ . "/app/view/layouts/empty.phtml");
            $router->service()->render(__PATH__ . "/public/404.html");
            break;
        case 405:
            $router->response()->body(
                'You can\'t do that!'
            );
            break;
        default:
            $router->response()->body(
                'Oh no, a bad error happened that caused a '. $code
            );
    }
});

$klein->dispatch();