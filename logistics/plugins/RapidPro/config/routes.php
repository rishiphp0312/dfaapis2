<?php
use Cake\Routing\Router;

Router::plugin('RapidPro', function ($routes) {
    $routes->fallbacks('InflectedRoute');
});
