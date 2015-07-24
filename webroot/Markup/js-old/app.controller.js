.controller('loginController', ['$scope', '$rootScope', 'ngDialog', 'authService', 'AUTH_EVENTS', function ($scope, $rootScope, ngDialog, authService, AUTH_EVENTS) {

    $scope.credentials = {
        email: '',
        password: ''
    }

    $scope.login = function () {
        authService.login($scope.credentials).then(function (user) {
            if (user) {
                $rootScope.$broadcast(AUTH_EVENTS.loginSuccess);
                $scope.setCurrentUser(user);
                //redirect to Dashboard;          
                ngDialog.close();
            } else {
                $rootScope.$broadcast(AUTH_EVENTS.loginFailed);
            }
        });
    }
} ])