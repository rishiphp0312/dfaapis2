angular.module('DataAdmin.home')
.controller('homeController', ['$rootScope', '$scope', '$stateParams', 'USER_ROLES', 'MENU_SECTIONS', 'MENU_SECTIONS_ACCESS', 'authService', 'databaseService', 'errorService',
function ($rootScope, $scope, $stateParams, USER_ROLES, MENU_SECTIONS, MENU_SECTIONS_ACCESS, authService, databaseService, errorService) {

    $scope.showSideBar = false;

    $scope.MENU_SECTIONS = MENU_SECTIONS;

    // set currentDatabase is empty.
    $rootScope.currentDatabase.id = '';

    $rootScope.currentDatabase.name = '';

    databaseService.getDatabaseList()
    .then(function (data) {

        $scope.databaseList = data.dbList;

        angular.forEach($scope.databaseList, function (value) {
            if (value.dbName.length > 20) {
                value.dbNameLabel = value.dbName.slice(0, 19) + '..';
            } else {
                value.dbNameLabel = value.dbName;
            }
            if ($rootScope.currentDatabase.id != '' && $rootScope.currentDatabase.id == value.id) {
                $rootScope.currentDatabase.name = value.dbName;
            }
        })
        $scope.showSideBar = true;
    }, function (fail) {
        errorService.show(fail);
    });

    $scope.setDatabase = function (database) {

        $rootScope.currentDatabase.id = database.id;

        $rootScope.currentDatabase.name = database.dbName;

        return true;
    }

    $scope.showSection = function (sectionType, currentDbRole) {

        var showSection = false;

        var sectionAccessRoles = getAccessRoles(sectionType);

        angular.forEach(currentDbRole, function (dbRole) {
            if (sectionAccessRoles.indexOf(dbRole) >= 0) {
                showSection = true;
                return showSection;
            }
        })

        return showSection;

    }

    function getAccessRoles(sectionType) {
        return MENU_SECTIONS_ACCESS[sectionType];
    }

} ])