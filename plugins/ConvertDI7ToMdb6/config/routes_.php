<?php
use Cake\Routing\Router;

Router::plugin('ConvertDI7ToMdb6', function ($routes) {
    $routes->fallbacks('InflectedRoute');
});
