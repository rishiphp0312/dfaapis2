<?php
use Cake\Routing\Router;

Router::plugin('DevInfoInterface', function ($routes) {
    $routes->fallbacks('InflectedRoute');
});
