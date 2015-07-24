angular.module('DataAdmin.login')
.controller('loginController', ['$scope', '$rootScope', '$state', 'ngDialog', 'authService', 'AUTH_EVENTS', function ($scope, $rootScope, $state, ngDialog, authService, AUTH_EVENTS) {

    $scope.credentials = {
        email: '',
        password: ''
    }

    $scope.loginFailed = false;

    $scope.login = function () {
        authService.login($scope.credentials).then(function (user) {
            if (user) {
                $rootScope.$broadcast(AUTH_EVENTS.loginSuccess);
                $scope.setCurrentUser(user);
                $state.go('DataAdmin.database')
                ngDialog.close();
            } else {
                $scope.loginFailed = true;
            }
        });
    }

    $scope.logout = function () {
        $scope.setCurrentUser('');
        authService.logout().then(function () {
            location.href = _WEBSITE_URL;
        });
    }
} ])