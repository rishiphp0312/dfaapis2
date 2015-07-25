angular.module(appConfig.appName)
.controller('appController', ['$scope', '$rootScope', '$stateParams', '$state', 'USER_ROLES', 'AUTH_EVENTS', 'ngDialog', 'authService', 'databaseService', 'commonService', 'session',
function ($scope, $rootScope, $stateParams, $state, USER_ROLES, AUTH_EVENTS, ngDialog, authService, databaseService, commonService, session) {

    // stores the current database id.
    $rootScope.currentDatabase = {
        id: '',
        name: ''
    };

    // key value pair for display of user Roles 
    commonService.getUserRolesList().then(function (res) {
        $rootScope.userRoles = res;
    }, function (fail) {
        alert('fail');
    })

    // current logged in user details 
    $scope.currentUser = null;

    // all the possible roles for a user.
    $scope.userRoles = USER_ROLES;

    // checks if user is authorized.
    $scope.isAuthorized = authService.isAuthorized;

    // sets the current user.
    $scope.setCurrentUser = function (user) {
        $scope.currentUser = user;
    };

    // listens to set current user.
    $scope.$on(AUTH_EVENTS.setCurrentUser, function (event, user) {
        $scope.currentUser = user;
    });

    // Listens to not authenticated event.
    $scope.$on(AUTH_EVENTS.notAuthenticated, function () {

        $scope.currentUser = null;

        session.destroy();

    });

    // when data base is changed.
    $scope.changeDatabase = function (dbId) {
        authService.emptyUserDbRoles();
        $state.go('DataAdmin.databaseManagement', { dbId: dbId })
    }

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
                $state.go('DataAdmin.home')
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

    $scope.closeLoginFailedAlert = function () {
        $scope.loginFailed = false;
    }

    // check if user is super Admin -- hide/show of add database button.
    $scope.isSuperAdmin = function () {
        return authService.isSuperAdmin();
    }

} ]);