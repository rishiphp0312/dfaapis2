angular.module(appConfig.appName)
.run(['$rootScope', '$state', '$urlRouter', 'AUTH_EVENTS', 'authService', function ($rootScope, $state, $urlRouter, AUTH_EVENTS, authService) {
    $rootScope.$on('$stateChangeStart', function (event, next, params) {

        // check for authentication
        authService.isAuthenticated()
        .then(function (isAuthenticated) {

            if (next.data != undefined && next.data.authenticationRequired != undefined) {
                // check if root url and is authenticated then redirect to datbase list.
                if (next.url == '/') {
                    if (isAuthenticated) {
                        event.preventDefault();
                        $state.go('DataAdmin.home');
                    }
                } else if (next.data.authenticationRequired) {
                    // if is authetnicated then 
                    if (isAuthenticated) {

                        var authorizedRoles = next.data.authorizedRoles;

                        // check for authorization
                        authService.isAuthorized(authorizedRoles, params.dbId)
                        .then(function (isAuth) {
                            if (!isAuth) {
                                event.preventDefault();
                                $rootScope.$broadcast(AUTH_EVENTS.notAuthorized);
                                $state.go('DataAdmin.notAuthorized');
                            }
                        })
                    } else {
                        event.preventDefault();
                        $rootScope.$broadcast(AUTH_EVENTS.notAuthenticated);
                        location.href = _WEBSITE_URL + '#/?loggedOut=true';
                    }
                }
            }
        });

    });

} ])

.config(['$stateProvider', '$urlRouterProvider', '$httpProvider', 'USER_ROLES', function ($stateProvider, $urlRouterProvider, $httpProvider, USER_ROLES) {

    $httpProvider.interceptors.push('httpInterceptor');

    $urlRouterProvider.otherwise('/');


    $stateProvider

        .state('DataAdmin', {
            url: '/',
            views: {
                'header': {
                },
                'content': {
                    templateUrl: 'js/app/components/core/views/login.html'
                },
                'footer': {
                    templateUrl: 'js/app/components/core/views/footer.html'
                }
            },
            data: {
                authenticationRequired: false
            }
        })

        .state('DataAdmin.home', {
            url: 'Home',
            views: {
                'header@': {
                    templateUrl: 'js/app/components/core/views/header.html'
                },
                'content@': {
                    templateUrl: 'js/app/components/home/views/home.html',
                    controller: 'homeController'
                }
            }
        })

        .state('DataAdmin.home.userManagement', {
            url: '/:dbId/UserManagement/',
            views: {
                'ManagementView@DataAdmin.home': {
                    templateUrl: 'js/app/components/userManagement/views/userManagement.html',
                    controller: 'userManagementController'
                }
            },
            data: {
                authorizedRoles: [USER_ROLES.all],
                authenticationRequired: true
            }
        })

        .state('DataAdmin.home.userManagement.modifyUser', {
            url: 'ModifyUser/:userId',
            views: {
                'ManagementView@DataAdmin.home': {
                    templateUrl: 'js/app/components/userManagement/views/addModifyUser.html',
                    controller: 'addModifyUserController'
                }
            },
            data: {
                authorizedRoles: [USER_ROLES.admin, USER_ROLES.superAdmin],
                authenticationRequired: true
            }
        })

        .state('DataAdmin.home.userManagement.addNewUser', {
            url: 'AddUser',
            views: {
                'ManagementView@DataAdmin.home': {
                    templateUrl: 'js/app/components/userManagement/views/addModifyUser.html',
                    controller: 'addModifyUserController'
                }
            },
            data: {
                authorizedRoles: [USER_ROLES.admin, USER_ROLES.superAdmin],
                authenticationRequired: true
            }
        })

        .state('DataAdmin.home.manageDatabases', {
            url: '/ManageDatabases',
            views: {
                'ManagementView@DataAdmin.home': {
                    templateUrl: 'js/app/components/database/views/database.html',
                    controller: 'databaseController'
                }
            },
            data: {
                authorizedRoles: [USER_ROLES.all],
                authenticationRequired: true
            }
        })

        .state('DataAdmin.home.manageDatabases.addDatabase', {
            url: '/AddDatabase',
            views: {
                'ManagementView@DataAdmin.home': {
                    templateUrl: 'js/app/components/database/views/addDatabase.html',
                    controller: 'newDatabaseConnectionController'
                }
            },
            data: {
                authenticationRequired: true,
                authorizedRoles: [USER_ROLES.superAdmin]
            }
        })

        .state('DataAdmin.home.templateImportExport', {
            url: '/:dbId/Template/importExport',
            views: {
                'ManagementView@DataAdmin.home': {
                    templateUrl: 'js/app/components/importExportManagement/views/templateImportExport.html',
                    controller: 'templateImportExportController'
                }
            },
            data: {
                authorizedRoles: [USER_ROLES.superAdmin, USER_ROLES.admin, USER_ROLES.templateUser],
                authenticationRequired: true
            }
        })

        .state('DataAdmin.home.templateIUS', {
            url: '/:dbId/Template/IUS',
            views: {
                'ManagementView@DataAdmin.home': {
                    templateUrl: 'js/app/components/iusManagement/views/iusManagement.html',
                    controller: 'iusManagementController'
                }
            },
            data: {
                authorizedRoles: [USER_ROLES.superAdmin, USER_ROLES.admin, USER_ROLES.templateUser],
                authenticationRequired: true
            }
        })

        .state('DataAdmin.home.templateIUS.iusValidations', {
            url: '/Validations/:iusId',
            views: {
                'ManagementView@DataAdmin.home': {
                    templateUrl: 'js/app/components/iusManagement/views/iusValidations.html',
                    controller: 'iusValidationsController'
                }
            },
            data: {
                authorizedRoles: [USER_ROLES.superAdmin, USER_ROLES.admin, USER_ROLES.templateUser],
                authenticationRequired: true
            }
        })

        .state('DataAdmin.home.dataEntry', {
            url: '/:dbId/Data',
            views: {
                'ManagementView@DataAdmin.home': {
                    templateUrl: 'js/app/components/dataEntry/views/dataEntry.html',
                    controller: 'dataEntryController'
                }
            },
            data: {
                authorizedRoles: [USER_ROLES.admin, USER_ROLES.superAdmin, USER_ROLES.dataUser],
                authenticationRequired: true
            }

        })

    /** NEW ROUTING **/

        .state('DataAdmin.notAuthorized', {
            url: 'NotAuthorized',
            views: {
                'content@': {
                    templateUrl: 'js/app/components/core/views/notAuthorized.html'
                }
            }
        })

        .state('DataAdmin.confirmPassword', {
            url: 'UserActivation/:key',
            views: {
                'content@': {
                    templateUrl: 'js/app/components/userManagement/views/confirmPassword.html',
                    controller: 'confirmPasswordController'
                }
            },
            data: {
                authenticationRequired: false
            }
        })

        .state('DataAdmin.databaseManagement.dataEntry', {
            url: '/DataEntry',
            views: {
                'ManagementView@DataAdmin.databaseManagement': {
                    templateUrl: 'js/app/components/dataEntry/views/dataEntry.html',
                    controller: 'dataEntryController'
                }
            },
            data: {
                authorizedRoles: [USER_ROLES.admin, USER_ROLES.superAdmin, USER_ROLES.dataUser],
                authenticationRequired: true
            }
        })
} ])