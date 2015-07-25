angular.module(appConfig.appName)

.service('session', function () {

    this.create = function (sessionId, userId, isSuperAdmin) {
        this.id = sessionId;
        this.userId = userId;
        this.isSuperAdmin = isSuperAdmin;
        this.dbRole = '';
    };

    this.destroy = function () {
        this.id = null;
        this.userId = null;
        this.isSuperAdmin = null;
        this.dbRole = null;
    };

    this.updateDbRole = function (dbRole) {
        this.dbRole = dbRole;
    }

    this.setSuperAdmin = function (isSA) {
        this.isSuperAdmin = isSA;
    }

})

.factory('authService', ['$http', '$q', 'session', '$rootScope', '$cookieStore', 'commonService', 'SERVICE_CALL', function ($http, $q, session, $rootScope, $cookieStore, commonService, SERVICE_CALL) {

    var authService = {};

    // login
    authService.login = function (credentials) {

        var deferred = $q.defer();

        $http(commonService.createHttpRequestObject(undefined, credentials, 'users/login')).success(function (res) {
            if (res.isAuthenticated) {

                var data = res.data;

                session.create(data.id, data.user.id, data.user.role);

                $cookieStore.put('globals', data.user);

                deferred.resolve(data.user);

            } else {
                deferred.resolve(res.err);
            }

        });

        return deferred.promise;

    }

    // check if user is logged in
    authService.isAuthenticated = function () {

        var deferred = $q.defer();

        var user = $cookieStore.get('globals');

        // if user id found in cookie than request is authetnicated else check via service call.
        if (angular.isUndefined(user) || angular.isUndefined(user.id)) {

            $http(commonService.createHttpRequestObject(SERVICE_CALL.system.checkSessionDetails))
            .success(function (res) {
                if (res.isAuthenticated) {

                    var data = res.data.usr;

                    session.create(data.id, data.user.id, res.isSuperAdmin);

                    if (res.usrDbRoles != undefined && res.usrDbRoles.length > 0) {
                        session.updateDbRole(res.usrDbRoles);
                    }

                    $rootScope.$broadcast('set-current-user', data.user);

                    $cookieStore.put('globals', data.user);

                    deferred.resolve(true);

                } else {
                    session.destroy();
                    $cookieStore.remove('globals');
                    deferred.resolve(false);
                }
            });
        } else {
            $rootScope.$broadcast('set-current-user', user);
            deferred.resolve(true);
        }

        return deferred.promise;

    };

    /* 
    * check if user is Authorized -- incase of super admin always authorized.
    * input param: authorizedRoles- required role to authorize.
    */
    authService.isAuthorized = function (authorizedRoles, dbId) {

        var deferred = $q.defer();

        var isAuthorized = false;

        if (session.isSuperAdmin || authorizedRoles.indexOf('*') >= 0) {
            isAuthorized = true;
            deferred.resolve(isAuthorized);
        } else {

            if (!angular.isArray(authorizedRoles)) {
                authorizedRoles = [authorizedRoles];
            }

            if (session.dbRole != undefined && session.dbRole.length > 0) {

                angular.forEach(session.dbRole, function (value) {
                    if (authorizedRoles != undefined && authorizedRoles.indexOf(value) >= 0) {
                        isAuthorized = true;
                    }
                })

                deferred.resolve(isAuthorized);

            } else {
                commonService.getUserDbRoles({ dbId: dbId })
                .then(function (res) {

                    session.setSuperAdmin(res.isSuperAdmin);

                    session.updateDbRole(res.data.usrDbRoles);
                    if (!res.isSuperAdmin) {
                        angular.forEach(session.dbRole, function (value) {
                            if (authorizedRoles != undefined && authorizedRoles.indexOf(value) >= 0) {
                                isAuthorized = true;
                            }
                        })
                    } else {
                        isAuthorized = true;
                    }

                    deferred.resolve(isAuthorized);
                },
                function (fail) {
                    isAuthorized = false;
                    deferred.resolve(isAuthorized);
                })
            }

        }

        return deferred.promise;
    };

    // checks for super Admin
    authService.isSuperAdmin = function () {
        return session.isSuperAdmin;
    }

    authService.emptyUserDbRoles = function () {
        session.updateDbRole([]);
    }

    // logout for user.
    authService.logout = function () {

        var deferred = $q.defer();

        $http(commonService.createHttpRequestObject(undefined, undefined, 'users/logout'))
        .then(function (res) {
            session.destroy();
            $cookieStore.remove('globals');
            deferred.resolve(res);
        });

        return deferred.promise;

    }

    return authService;

} ])

.factory('commonService', ['$http', '$q', 'SERVICE_CALL', function ($http, $q, SERVICE_CALL) {

    var commonService = {};

    commonService.createServiceCallUrl = function (serviceCall) {
        return appConfig.serviceCallUrl + serviceCall;
    }

    //creates HTTP request Object as per params Passed.
    commonService.createHttpRequestObject = function (serviceCall, data, url, method, headers) {

        var req = {};
        req['method'] = method || 'POST';

        if (serviceCall) {
            req['url'] = commonService.createServiceCallUrl(serviceCall);
        } else if (url) {
            req['url'] = url;
        }

        if (data) {
            req['data'] = $.param(data);
        }

        req['headers'] = headers || { 'Content-Type': 'application/x-www-form-urlencoded' };

        return req;
    }

    //gets list of roles.
    commonService.getUserRolesList = function () {

        var deferred = $q.defer();

        $http(commonService.createHttpRequestObject(SERVICE_CALL.system.getUserRolesList))
        .success(function (res) {
            if (res.success) {
                deferred.resolve(res.data.roleDetails);
            } else {
                deferred.reject(res.err);
            }
        })

        return deferred.promise;

    }

    // gets all users List
    commonService.getAllUsersList = function () {

        var deferred = $q.defer();

        $http(commonService.createHttpRequestObject(SERVICE_CALL.system.getAllUsersList))
        .success(function (res) {
            if (res.success) {
                deferred.resolve(res.data.usersList);
            } else {
                deferred.reject(res.err);
            }
        })

        return deferred.promise;

    }

    // get current database roles for a user
    commonService.getUserDbRoles = function (data) {

        var deferred = $q.defer();

        $http(commonService.createHttpRequestObject(SERVICE_CALL.system.getUserDbRoles, data))
        .success(function (res) {
            if (res.success) {
                deferred.resolve(res);
            } else {
                deferred.reject(res.err);
            }
        })

        return deferred.promise;
    }

    // sets a default value for a property if property does not exist in an object
    commonService.ensureDefault = function (obj, prop, value) {
        if (!obj.hasOwnProperty(prop))
            obj[prop] = value;
    }

    // gets the list of IUS for a dbId
    commonService.getIUSList = function (dbId) {

        var deferred = $q.defer();

        $http(commonService.createHttpRequestObject(SERVICE_CALL.commonService.getIUSList, {
            dbId: dbId,
            type: 'iu',
            onDemand: true
        }))
        .success(function (res) {
            if (res.success) {
                deferred.resolve(res.data);
            } else {
                deferred.reject(res.err);
            }
        })

        return deferred.promise;
    }

    // get areaList.
    commonService.getAreaList = function (dbId) {

        var deferred = $q.defer();

        $http(commonService.createHttpRequestObject(SERVICE_CALL.commonService.getAreaList, {
            dbId: dbId,
            type: 'Area',
            onDemand: true
        }))
        .success(function (res) {
            if (res.success) {
                deferred.resolve(res.data);
            } else {
                deferred.reject(res.err);
            }
        });

        return deferred.promise;

    }

    // gets the timeperiod List for a dbid
    commonService.getTPList = function (dbId) {

        var deferred = $q.defer();

        $http(commonService.createHttpRequestObject(SERVICE_CALL.commonService.getTPList, {
            dbId: dbId,
            type: 'tp'
        }))
        .success(function (res) {
            if (res.success) {
                deferred.resolve(res.data);
            } else {
                deferred.reject(res.err);
            }
        })

        return deferred.promise;
    }

    // gets the source list for a dbid
    commonService.getSourceList = function (dbId) {

        var deferred = $q.defer();

        $http(commonService.createHttpRequestObject(SERVICE_CALL.commonService.getSourceList, {
            dbId: dbId,
            type: 'source'
        }))
        .success(function (res) {
            if (res.success) {
                deferred.resolve(res.data);
            } else {
                deferred.reject(res.err);
            }
        })

        return deferred.promise;
    }

    commonService.getICList = function (dbId) {

        var deferred = $q.defer();

        $http(commonService.createHttpRequestObject(SERVICE_CALL.commonService.getICList, {
            dbId: dbId,
            pnid: '',
            type: 'ICIND',
            onDemand: true
        }))
        .success(function (res) {
            if (res.success) {
                deferred.resolve(res.data);
            } else {
                deferred.reject(res.err);
            }
        })

        return deferred.promise;

    }

    commonService.getIndicatorList = function (dbId) {
        var deferred = $q.defer();

        $http(commonService.createHttpRequestObject(SERVICE_CALL.commonService.getICList, {
            dbId: dbId,
            type: 'ind',
            onDemand: true
        }))
        .success(function (res) {
            if (res.success) {
                deferred.resolve(res.data);
            } else {
                deferred.reject(res.err);
            }
        })

        return deferred.promise;
    }

    // gets on demand url for area
    commonService.getAreaOnDemandURL = function () {
        return commonService.createServiceCallUrl(SERVICE_CALL.commonService.getAreaList);
    }

    // gets on demand url for IUS
    commonService.getIUSOnDemandUrl = function () {
        return commonService.createServiceCallUrl(SERVICE_CALL.commonService.getIUSList);
    }

    // gets on demand url for area
    commonService.getICOnDemandURL = function () {
        return commonService.createServiceCallUrl(SERVICE_CALL.commonService.getICList);
    }

    return commonService;

} ])

.factory('httpInterceptor', ['$rootScope', '$q', '$cookieStore', '$stateParams', 'session', 'AUTH_EVENTS', function ($rootScope, $q, $cookieStore, $stateParams, session, AUTH_EVENTS) {
    return {
        'request': function (request) {
            //alert($stateParams.dbId);
            return request;
        },

        // check if response is not authenticated.
        'response': function (response) {

            if (response.data != undefined) {

                // check for super admin and update session.
                if (response.data.isSuperAdmin != undefined) {
                    session.setSuperAdmin(response.data.isSuperAdmin);
                }

                // check for authentiacted and remove cookie if not authenticated
                if (response.data.isAuthenticated != undefined && response.data.isAuthenticated == false) {
                    $cookieStore.remove('globals')
                }

                // check for roles assigned to a user for a particalar DB.
                if (response.data.data != undefined && response.data.data.usrDbRoles != undefined) {
                    session.updateDbRole(response.data.data.usrDbRoles);
                }
            }

            return response;
        },

        //// optional method
        'responseError': function (rejection) {
            alert('Something went wrong: ' + rejection.statusText);
            return $q.reject(rejection);
        }
    };
} ])

.factory('errorService', ['ERROR_CODE', 'modalService', function (ERROR_CODE, modalService) {

    var errorService = {};

    errorService.resolve = function (errObj) {

        var errorMessage = '';

        if (errObj.code != undefined) {
            errorMessage = ERROR_CODE[errObj.code];
        } else if (errObj.msg) {
            errorMessage = errObj.msg;
        }

        return errorMessage;

    }

    errorService.show = function (errObj) {
        modalService.show({}, {
            actionButtonText: 'OK',
            headerText: 'Error',
            bodyText: errorService.resolve(errObj),
            showCloseButton: false
        })
    }

    return errorService;

} ])

.factory('onSuccessDialogService', ['modalService', function (modalService) {

    var onSuccessDialogService = {};

    onSuccessDialogService.show = function (msg, callBack) {
        modalService.show({}, {
            actionButtonText: 'OK',
            headerText: 'Success',
            bodyText: msg,
            showCloseButton: false
        }).then(function (result) {
            if (callBack != undefined) {
                callBack();
            }
        })
    }

    return onSuccessDialogService;

} ])

.service('modalService', ['$modal',
function ($modal) {

    var modalOptions = {
        closeButtonText: 'Close',
        actionButtonText: 'OK',
        headerText: 'Confirmation',
        bodyText: 'Are you sure you want to perform this action?',
        showCloseButton: true
    };

    var modalDefaults = {
        backdrop: true,
        keyboard: true,
        templateUrl: 'js/app/components/core/views/modal.html'
    };

    this.show = function (customModalDefaults, customModalOptions) {
        //Create temp objects to work with since we're in a singleton service
        var tempModalDefaults = {};
        var tempModalOptions = {};

        //Map angular-ui modal custom defaults to modal defaults defined in service
        angular.extend(tempModalDefaults, modalDefaults, customModalDefaults);

        //Map modal.html $scope custom properties to defaults defined in service
        angular.extend(tempModalOptions, modalOptions, customModalOptions);

        if (!tempModalDefaults.controller) {
            tempModalDefaults.controller = function ($scope, $modalInstance) {
                $scope.modalOptions = tempModalOptions;
                $scope.confirm = function (result) {
                    $modalInstance.close(result);
                };
                $scope.close = function (result) {
                    $modalInstance.dismiss('cancel');
                };
            }
        }

        return $modal.open(tempModalDefaults).result;
    };

} ])

.service('treeViewModalService', ['$modal', '$http', '$q',
function ($modal, $http, $q) {

    var modalDefaults = {
        backdrop: true,
        keyboard: true,
        templateUrl: 'js/app/components/core/views/treeViewModal.html'
    };
    var modalOptions = {
        closeButtonText: 'Close',
        actionButtonText: 'OK',
        headerText: 'Confirmation',
        bodyText: 'Are you sure you want to perform this action?',
        showCloseButton: true
    };

    this.show = function (options) {

        modalDefaults.controller = function ($scope, $modalInstance) {
            $scope.headerText = options.header;
            $scope.selectedList = options.selectedList;
            $scope.treeViewList = options.treeViewList;
            $scope.treeViewOptions = options.treeViewOptions;
            $scope.confirm = function () {
                $modalInstance.close($scope.selectedList);
            };
            $scope.close = function () {
                $modalInstance.dismiss('cancel');
            };
        }

        return $modal.open(modalDefaults).result;
    }

} ])