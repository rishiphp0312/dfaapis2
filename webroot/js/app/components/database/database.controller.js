angular.module('DataAdmin.database')
.controller('databaseController', ['$scope', '$rootScope', '$filter', 'authService', 'USER_ROLES', 'databaseService', 'errorService', 'modalService',
function ($scope, $rootScope, $filter, authService, USER_ROLES, databaseService, errorService, modalService) {

    $scope.currentDatabase = null;

    // selected database to be deleted.
    $scope.selectedDatabase = '';

    $scope.search = {
        dbName: ''
    };

    databaseService.getDatabaseList().then(function (data) {
        $scope.userDatabaseList = data.dbList;
    }, function (err) {
        errorService.show(err);
    });

    // event bind to give popup for confirmation of deletion
    $scope.deleteDatabaseConnection = function (database) {

        $scope.selectedDatabase = database;

        modalService.show({}, {
            closeButtonText: 'Cancel',
            actionButtonText: 'Delete',
            headerText: 'Database',
            bodyText: 'Are you sure you want to delete this record.'
        })
        .then(function () {
            confirmDelete();
        })

    }

    // confirms delete of database connection.
    function confirmDelete() {
        databaseService.deleteDatabaseConnection($scope.selectedDatabase.id)
        .then(function (res) {
            if (res) {
                $scope.userDatabaseList = $filter('filter')($scope.userDatabaseList, function (value, index) {
                    return (value.id != $scope.selectedDatabase.id);
                })
                $scope.selectedDatabase = '';
            }
        }, function (err) {
            errorService.show(err);
        });
        return true;
    }

} ])
.controller('newDatabaseConnectionController', ['$scope', '$state', 'databaseService', 'errorService',
function ($scope, $state, databaseService, errorService) {

    $scope.testConnectionVerified;

    $scope.hideConnectionVerified = true;

    $scope.isConnectionNameChanged = false;

    $scope.connectionDetails = {
        connectionName: '',
        databaseType: 'mssql',
        hostAddress: '',
        databaseName: '',
        userName: '',
        password: '',
        port: '1433'
    }

    databaseService.getDbTypeList().then(function (dbtypeList) {
        $scope.dbTypeDetails = dbtypeList;
    })

    $scope.saveConnection = function (connectionDetails) {
        databaseService.addNewDatabaseConnection(connectionDetails).then(function (res) {
            $state.go('DataAdmin.home.manageDatabases');
        }, function (err) {
            errorService.show(err);
        })
    }

    $scope.verifyConnectionName = function (connectionName) {
        if ($scope.isConnectionNameChanged) {
            databaseService.verifyConnectionName(connectionName).then(function (res) {
                $scope.isConnectionNameChanged = false;
                $scope.connectionNameUnique = true;
            }, function (fail) {
                $scope.connectionNameUnique = false;
            })
        }
    }

    $scope.testConnection = function (formValid, connectionDetails) {
        if (formValid) {
            databaseService.testDatabaseConnection(connectionDetails)
            .then(function (res) {
                $scope.testConnectionVerified = true;
                $scope.hideConnectionVerified = true;
            }, function (fail) {
                $scope.testConnectionVerified = false;
                $scope.hideConnectionVerified = false;
                //errorService.show(fail);
            });
            return false;
        } else {
            $scope.showValidations = true;
        }

    }

    $scope.$watch('connectionDetails.connectionName', function (oldValue, newValue) {
        if (oldValue !== newValue) { $scope.isConnectionNameChanged = true; }
    })

    $scope.setDbDefaultPort = function () {
        if ($scope.connectionDetails.databaseType == 'mssql') {
            $scope.connectionDetails.port = '1433';
        } else {
            $scope.connectionDetails.port = '3306';
        }
    }

} ])