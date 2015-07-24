angular.module('DataAdmin.iusManagement')
.controller('iusManagementController', ['$scope', '$rootScope', '$stateParams', '$state', 'iusManagementService', 'commonService', 'errorService',
function ($scope, $rootScope, $stateParams, $state, iusManagementService, commonService, errorService) {

    $rootScope.currentDatabase.id = $stateParams.dbId;

    commonService.getIUSList($rootScope.currentDatabase.id)
    .then(function (success) {
        $scope.iuList = success.iu;
    }, function (fail) {
        errorService.show(fail);
    })

    $scope.treeViewOptions = {
        search: true,
        onDemand: true,
        onDemandOptions: {
            url: commonService.getIUSOnDemandUrl(),
            responseDataKey: ['data', 'iu'],
            requestDataKey: 'returnData'
        },
        selectionOptions: {
            multiSelection: false,
            showCheckBox: false,
            checkBoxClass: '',
            selectedClass: 'nodeSelected',
            selectedHTML: ''
        },
        nodeOptions: {
            showNodeOpenCloseClass: true,
            nodeOpenClass: 'fa fa-plus',
            nodeCloseClass: 'fa fa-minus',
            showNodeLeafClass: false,
            nodeLeafClass: '',
            showLoader: true,
            loaderClass: 'fa fa-spinner fa-spin'
        },
        labelOptions: {
            fields: [{
                id: 'iName',
                css: '',
                seperator: ', '
            }, {
                id: 'uName',
                css: '',
                seperator: ' '
            }, {
                id: 'sName',
                css: '',
                seperator: ''
            }],
            prefix: '',
            suffix: '',
            class: ''
        }
    }

    $scope.selectedIUS = [];

    $scope.updateIUSValidations = function () {

        $state.go('DataAdmin.home.templateIUS.iusValidations', { iusId: $scope.selectedIUS[0].id })

    }

} ])
.controller('iusValidationsController', ['$scope', '$rootScope', '$stateParams', '$state', 'iusManagementService', 'commonService', 'errorService', 'onSuccessDialogService',
function ($scope, $rootScope, $stateParams, $state, iusManagementService, commonService, errorService, onSuccessDialogService) {

    $scope.iusId = $stateParams.iusId;

    $rootScope.currentDatabase.id = $stateParams.dbId;

    iusManagementService.getIUSDetails({ iusId: $scope.iusId, dbId: $rootScope.currentDatabase.id })
    .then(function (success) {
        $scope.iusDetails = success.iusValidations;
        commonService.ensureDefault($scope.iusDetails, 'isTextual', false);
        commonService.ensureDefault($scope.iusDetails, 'minimumValue', '');
        commonService.ensureDefault($scope.iusDetails, 'maximumValue', '');
    }, function (err) {
        errorService.show(err);
    });

    $scope.selectedIUS = [];

    $scope.iuList = [];

    $scope.applyOnOthers = false;

    $scope.saveAndApplyOnOthers = function () {

        var data = createDataObject([$scope.iusDetails.iusGid]);

        iusManagementService.saveIUSValidations(data)
        .then(function (success) {
            if (success) {
                //onSuccessDialogService.show('Validations have been saved successfully.');
                $scope.applyOnOthers = true;
                $scope.applyOnOthersLoader = true;
                if ($scope.iuList.length <= 0) {

                    commonService.getIUSList($rootScope.currentDatabase.id)
                    .then(function (success) {
                        $scope.applyOnOthersLoader = false;
                        $scope.iuList = success.iu;
                    }, function (err) {
                        errorService.show(err);
                    })
                } else {
                    $scope.applyOnOthersLoader = false;
                }
            }
        }, function (err) {
            errorService.show(err);
        });
    };

    $scope.treeViewOptions = {
        search: true,
        onDemand: true,
        onDemandOptions: {
            url: commonService.getIUSOnDemandUrl(),
            responseDataKey: ['data', 'iu'],
            requestDataKey: 'returnData'
        },
        selectionOptions: {
            multiSelection: true,
            showCheckBox: true,
            checkBoxClass: '',
            selectedHTML: '',
            selectedClass: 'sel'
        },
        nodeOptions: {
            showNodeOpenCloseClass: true,
            nodeOpenClass: 'fa fa-plus',
            nodeCloseClass: 'fa fa-minus',
            showNodeLeafClass: false,
            nodeLeafClass: '',
            showLoader: true,
            loaderClass: 'fa fa-spinner fa-spin'
        },
        labelOptions: {
            fields: [{
                id: 'iName',
                css: '',
                seperator: ', '
            }, {
                id: 'uName',
                css: '',
                seperator: ' '
            }, {
                id: 'sName',
                css: '',
                seperator: ''
            }],
            prefix: '',
            suffix: '',
            class: ''
        }
    }

    $scope.save = function () {

        var iusId = [];

        if ($scope.applyOnOthers) {
            angular.forEach($scope.selectedIUS, function (value) {
                iusId.push(value.id);
            })
        } else {
            iusId.push($scope.iusDetails.iusGid);
        }

        var data = createDataObject(iusId);

        iusManagementService.saveIUSValidations(data)
       .then(function (success) {
           if (success) {
               onSuccessDialogService.show('Validations have been saved successfully.');
           }
       }, function (err) {
           errorService.show(err);
       })

    }

    $scope.cancel = function () {

        if ($scope.applyOnOthers) {
            $scope.applyOnOthers = !$scope.applyOnOthers;
        } else {
            $state.go('DataAdmin.home.templateIUS');
        }

    }

    function createDataObject(iusId) {

        var data = {
            iusId: iusId,
            dbId: $rootScope.currentDatabase.id,
            isTextual: $scope.iusDetails.isTextual,
            minimumValue: ($scope.iusDetails.isTextual ? '' : $scope.iusDetails.minimumValue),
            maximumValue: ($scope.iusDetails.isTextual ? '' : $scope.iusDetails.maximumValue)
        };

        return data;
    }

} ])