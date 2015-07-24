// Routing for the Different Pages
angular.module('D3AMarkup', ['ngRoute'])
.config(['$routeProvider', function ($routeProvider)
{
    $routeProvider
    .when('/', {
        templateUrl: 'js/templates/home.html',
        controller: 'loginController'
    })
    .when('/databaselist', {
        templateUrl: 'js/templates/databaselist.html'
    })
    .when('/database', {
        templateUrl: 'js/templates/database.html'
    })
    .when('/adduser', {
        templateUrl: 'js/templates/adduser.html'
    })
  .when('/usermanagement', {
      templateUrl: 'js/templates/usermanagement.html'
  })
    .when('/connection', {
        templateUrl: 'js/templates/newconnection.html'
    })
     .when('/importexport', {
        templateUrl: 'js/templates/importexport.html'
    })
    .when('/templateius', {
        templateUrl: 'js/templates/templateius.html'
    })
    .when('/iusvalidation', {
        templateUrl: 'js/templates/iusvalidation.html'
    })
    .otherwise({
        redirectTo: '/'
    });
} ]);
