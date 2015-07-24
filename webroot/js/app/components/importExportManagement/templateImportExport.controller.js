angular.module('DataAdmin.importExportManagement')
.controller('templateImportExportController', ['$scope', '$rootScope', '$stateParams', 'templateImportService', 'commonService', 'SERVICE_CALL', 'errorService', 'onSuccessDialogService',
function ($scope, $rootScope, $stateParams, templateImportService, commonService, SERVICE_CALL, errorService, onSuccessDialogService) {

    $rootScope.currentDatabase.id = $stateParams.dbId;

    $scope.showIusImportLog = false;

    $scope.showAreaImportLog = false;

    $scope.files = {
        iusFile: '',
        areaFile: ''
    }

    $scope.dbId = $stateParams.dbId;

    $scope.generateFileDataIcIus = {
        url: commonService.createServiceCallUrl(SERVICE_CALL.templateManagement.importFile),
        fields: { 'dbId': $stateParams.dbId, type: 'ICIUS' },
        sendFieldsAs: 'form'
    };

    $scope.generateFileDataArea = {
        url: commonService.createServiceCallUrl(SERVICE_CALL.templateManagement.importFile),
        fields: { 'dbId': $stateParams.dbId, type: 'AREA' },
        sendFieldsAs: 'form'
    };

    $scope.onFileSuccess = function (successObj, fileData) {

        var uploadType = fileData.fields.type;

        var msg = (uploadType == 'ICIUS' ? 'IUS and Indicator Classifications have been imported successfully.' : 'Geographic Areas have been imported successfully.');

        onSuccessDialogService.show(msg, function () {
            if (uploadType == 'ICIUS') {
                $scope.showIusImportLog = true;
                $scope.iusImportLog = successObj.data.importLog;

            }

            if (uploadType == 'AREA') {
                $scope.showAreaImportLog = true;
                $scope.areaImportLog = successObj.data.importLog;
            }

        });

    }

    $scope.onFileFail = function (response, fileData) {

        var uploadType = fileData.fields.type;

        errorService.show(response.err);

        if (uploadType == 'ICIUS') {
            $scope.files.iusFile = '';
        }

        if (uploadType == 'AREA') {
            $scope.files.areaFile = '';
        }

    }

    $scope.exportUrl = function (type) {

        return _WEBSITE_URL + commonService.createServiceCallUrl(SERVICE_CALL.templateManagement.exportFile) + '?dbId=' + $stateParams.dbId + '&type=' + type;
    }

    $scope.hideIUSLog = function () {
        $scope.showIusImportLog = false;
    }

    $scope.hideAreaLog = function () {
        $scope.showAreaImportLog = false;
    }

} ])