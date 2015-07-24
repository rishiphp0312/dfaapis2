angular.module('DataAdmin.importExportManagement')
.factory('templateImportService', ['$q', 'SERVICE_CALL', 'commonService', 'Upload', function ($q, SERVICE_CALL, commonService, Upload) {

    var templateImportService = {};

    templateImportService.getImportUrl = function () {
        return commonService.createServiceCallUrl(SERVICE_CALL.templateManagement.importFile);
    }

    return templateImportService;

} ])