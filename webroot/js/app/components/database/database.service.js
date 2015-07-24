angular.module('DataAdmin.database')
.factory('databaseService', ['$http', '$q', 'SERVICE_CALL', 'commonService', function ($http, $q, SERVICE_CALL, commonService) {

    var databaseService = {};

    databaseService.getDbTypeList = function () {

        var deferred = $q.defer();

        deferred.resolve([{
            id: 'mssql',
            name: 'MS SQL'
        }, {
            id: 'mysql',
            name: 'My SQL'
        }])

        return deferred.promise;

    }

    // Adds new database connection - (also tests for valid connection)
    databaseService.addNewDatabaseConnection = function (connectionDetails) {

        var deffered = $q.defer();

        $http(commonService.createHttpRequestObject(SERVICE_CALL.database.addNewDatabaseConnection, connectionDetails))
        .success(function (res) {
            if (res.success) {
                deffered.resolve(res.data);
            } else {
                deffered.reject(res.err);
            }
        });

        return deffered.promise;

    }

    // Checks for duplicate Connection Name
    databaseService.verifyConnectionName = function (connectionName) {
        var deffered = $q.defer();

        $http(commonService.createHttpRequestObject(SERVICE_CALL.database.verifyConnectionName, { connectionName: connectionName }))
        .success(function (res) {
            if (res.success == true) {
                deffered.resolve(res.data);
            } else {
                deffered.reject(res.err);
            }
        })

        return deffered.promise;
    }

    // gets list of database as per user.
    databaseService.getDatabaseList = function () {

        var deffered = $q.defer();

        $http(commonService.createHttpRequestObject(SERVICE_CALL.database.getDatabaseList))
        .success(function (res) {
            if (res.success == true) {
                deffered.resolve(res.data);
            } else {
                deffered.reject(res.err);
            }
        })

        return deffered.promise;

    }

    // deletes database connection
    databaseService.deleteDatabaseConnection = function (databaseId) {

        var deffered = $q.defer();

        $http(commonService.createHttpRequestObject(SERVICE_CALL.database.deleteDatabaseConnection, { dbId: databaseId }))
        .success(function (res) {
            if (res.success == true) {
                deffered.resolve(res.success);
            } else {
                deffered.reject(res.err);
            }
        })

        return deffered.promise;


    }

    // test the database connection details
    databaseService.testDatabaseConnection = function (connectionDetails) {

        var deffered = $q.defer();

        $http(commonService.createHttpRequestObject(SERVICE_CALL.database.testDatabaseConnection, connectionDetails))
        .success(function (res) {
            if (res.success == true) {
                deffered.resolve(res.success);
            } else {
                deffered.reject(res.err);
            }
        })

        return deffered.promise;

    }

    return databaseService;

} ])