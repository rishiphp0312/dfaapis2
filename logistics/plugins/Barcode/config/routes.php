<?php
use Cake\Routing\Router;

Router::plugin('Barcode', function ($routes) {
    $routes->fallbacks('InflectedRoute');
});
