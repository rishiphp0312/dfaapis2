angular.module('DataAdmin.iusManagement')
.factory('iusManagementService', ['$http', '$q', 'SERVICE_CALL', 'commonService', function ($http, $q, SERVICE_CALL, commonService) {

    var iusManagementService = {};

    iusManagementService.getIUList = function (dbId) {

        var deferred = $q.defer();

        $http(commonService.createHttpRequestObject(SERVICE_CALL.templateManagement.getIUSList, {
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

    iusManagementService.getOnDemandURL = function () {
        return commonService.createServiceCallUrl(SERVICE_CALL.templateManagement.getIUSList);
    }

    iusManagementService.getIUSDetails = function (data) {

        var deferred = $q.defer();

        $http(commonService.createHttpRequestObject(SERVICE_CALL.templateManagement.getIUSDetails, data))
        .success(function (res) {
            if (res.success) {
                deferred.resolve(res.data);
            } else {
                deferred.reject(res.err);
            }
        })

        return deferred.promise;

    }

    iusManagementService.saveIUSValidations = function (data) {

        var deferred = $q.defer();

        $http(commonService.createHttpRequestObject(SERVICE_CALL.templateManagement.saveIUSValidations, data))
        .success(function (res) {
            if (res.success) {
                deferred.resolve(res.success);
            } else {
                deferred.reject(res.err);
            }
        })

        return deferred.promise;

    }

    return iusManagementService;

} ]);