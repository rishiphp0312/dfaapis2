angular.module('DataAdmin.dataEntry')
.factory('dataEntryService', ['$http', '$q', 'SERVICE_CALL', 'commonService', function ($http, $q, SERVICE_CALL, commonService) {

    var dataEntryService = {};

    dataEntryService.getData = function (data) {
        var deferred = $q.defer();

        $http(commonService.createHttpRequestObject(SERVICE_CALL.dataEntryManagement.getData, data))
        .success(function (res) {
            if (res.success) {
                deferred.resolve(res.data);
            } else {
                deferred.reject(res.err);
            }
        })

        return deferred.promise;
    }

    dataEntryService.saveData = function (data) {
        var deferred = $q.defer();

        $http(commonService.createHttpRequestObject(SERVICE_CALL.dataEntryManagement.saveData, data))
        .success(function (res) {
            if (res.success) {
                deferred.resolve(res.success);
            } else {
                deferred.reject(res.err);
            }
        })

        return deferred.promise;
    }

    return dataEntryService;

} ])