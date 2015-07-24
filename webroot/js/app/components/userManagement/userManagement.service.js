angular.module('DataAdmin.userManagement')
.factory('userManagementService', ['$http', '$q', '$filter', 'SERVICE_CALL', 'commonService', function ($http, $q, $filter, SERVICE_CALL, commonService) {

    var userManagementService = {};

    userManagementService.getUsersList = function (databaseId) {

        var deferred = $q.defer();

        $http(commonService.createHttpRequestObject(SERVICE_CALL.userManagement.getUserList, { dbId: databaseId }))
        .success(function (res) {
            if (res.success) {
                deferred.resolve(res.data.userList);
            } else {
                deferred.reject(res.err);
            }
        })

        return deferred.promise;

    }

    userManagementService.getUserDetails = function (data) {

        var deferred = $q.defer();

        $http(commonService.createHttpRequestObject(SERVICE_CALL.userManagement.getUserList, { dbId: data.dbId }))
        .success(function (res) {
            if (res.success) {
                deferred.resolve($filter('filter')(res.data.userList, { id: data.userId })[0]);
            } else {
                deferred.reject(res.err);
            }
        })

        return deferred.promise;

    }

    userManagementService.addModifyUser = function (userDetails) {

        var deferred = $q.defer();

        $http(commonService.createHttpRequestObject(SERVICE_CALL.userManagement.addModifyUser, userDetails))
        .success(function (res) {
            if (res.success) {
                deferred.resolve(res.success);
            } else {
                deferred.reject(res.err);
            }
        })

        return deferred.promise;

    }

    userManagementService.deleteUsers = function (data) {

        var deferred = $q.defer();

        $http(commonService.createHttpRequestObject(SERVICE_CALL.userManagement.deleteUsers, data))
        .success(function (res) {
            if (res.success) {
                deferred.resolve(res.success);
            } else {
                deferred.reject(res.err);
            }
        })

        return deferred.promise;

    }

    userManagementService.confirmPassword = function (data) {

        var deferred = $q.defer();

        $http(commonService.createHttpRequestObject(SERVICE_CALL.userManagement.confirmPassword, data))
        .success(function (res) {
            if (res.success) {
                deferred.resolve(res.success);
            } else {
                deferred.reject(res.err);
            }
        })

        return deferred.promise;

    }

    userManagementService.resetPassword = function (data) {

        var deferred = $q.defer();

        $http(commonService.createHttpRequestObject(SERVICE_CALL.userManagement.resetPassword, data))
        .success(function (res) {
            if (res.success) {
                deferred.resolve(res.success);
            } else {
                deferred.reject(res.err);
            }
        })

        return deferred.promise;

    }

    return userManagementService;

} ])