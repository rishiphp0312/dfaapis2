<?php
    use Cake\Routing\Router;
    Router::scope('/ConvertDI7ToMdb6', ['plugin' => 'ConvertDI7ToMdb6'], function ($routes) {
    //Router::connect('/ConvertDI7ToMdb6', ['plugin' => 'ConvertDI7ToMdb6', 'controller' => 'Jobs']);
    Router::connect('/ConvertDI7ToMdb6/:controller/:action/*', ['plugin' => 'ConvertDI7ToMdb6', 'controller' => 'Jobs']);
});