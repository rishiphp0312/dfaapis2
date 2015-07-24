angular.module('DataAdmin.dataEntry')
.controller('dataEntryController', ['$scope', '$rootScope', '$stateParams', 'dataEntryService', 'commonService', 'onSuccessDialogService', 'errorService',
function ($scope, $rootScope, $stateParams, dataEntryService, commonService, onSuccessDialogService, errorService) {

    $rootScope.currentDatabase.id = $stateParams.dbId

    $scope.showSearchList = {
        tp: false,
        ius: false,
        area: false
    }

    $scope.selectedOptions = {
        area: [],
        ius: [],
        tp: []
    }

    $scope.toggleSearchList = function (selectionType) {
        $scope.showSearchList[selectionType] = !$scope.showSearchList[selectionType];
    }

    $scope.timePeriodSelected = function (timePeriod) {
        $scope.selectedOptions.tp = [timePeriod];
        $scope.showSearchList.tp = false;
    }

    $scope.resetSearchResult = function () {
        $scope.selectedOptions.area = [];
        $scope.selectedOptions.ius = [];
        $scope.selectedOptions.tp = [];
    }

    $scope.selectedSearchData = {
        areaNid: '',
        tp: ''
    }

    commonService.getAreaList($stateParams.dbId)
    .then(function (data) {
        $scope.areaList = data.Area;
    }, function (err) {
        errorService.show(err);
    })

    $scope.treeViewAreaOptions = {
        search: false,
        onDemand: true,
        onDemandOptions: {
            url: commonService.getAreaOnDemandURL(),
            responseDataKey: ['data', 'Area'],
            requestDataKey: 'returnData'
        },
        selectionOptions: {
            multiSelection: false,
            showCheckBox: false,
            checkBoxClass: '',
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
                id: 'aname',
                css: '',
                seperator: ''
            }],
            prefix: '',
            suffix: '',
            class: ''
        }
    }

    commonService.getIUSList($stateParams.dbId)
    .then(function (data) {
        $scope.iusList = data.iu;
    }, function (err) {
        errorService.show(err);
    })

    $scope.treeViewIUSOptions = {
        search: false,
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
                seperator: ''
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

    $scope.toggleSourceList = function (subgroup) {
        subgroup.showSourceList = !subgroup.showSourceList;
    }

    $scope.sourceSelected = function (subgroup, source) {
        subgroup.src = source.id;
        subgroup.showSourceList = false;
        subgroup.srcText = source.name;
    }

    commonService.getTPList($stateParams.dbId)
    .then(function (data) {
        $scope.tpList = data.tp;
    }, function (err) {
        errorService.show(err);
    })

    commonService.getSourceList($stateParams.dbId)
    .then(function (data) {
        $scope.sourceList = data.source;
    }, function (err) {
        errorService.show(err);
    });

    $scope.searchData = function () {
        if ($scope.selectedOptions.area.length <= 0) {

        } else if ($scope.selectedOptions.ius.length <= 0) {

        } else if ($scope.selectedOptions.tp.length <= 0) {

        } else {
            var data = {
                areaNid: $scope.selectedOptions.area[0].id,
                tp: $scope.selectedOptions.tp[0].id,
                iusGids: [],
                dbId: $stateParams.dbId
            };

            $scope.selectedSearchData.areaNid = data.areaNid;
            $scope.selectedSearchData.tp = data.tp;

            angular.forEach($scope.selectedOptions.ius, function (value) {
                data.iusGids.push(value.id);
            })

            dataEntryService.getData(data)
            .then(function (data) {
                $scope.iusDataList = data.iusData;
            }, function (err) {
                errorService.show(err);
            });
        }
    }

    $scope.saveData = function () {

        var dataList = [];

        angular.forEach($scope.iusDataList, function (iu) {

            angular.forEach(iu.subgrps, function (subgroup) {

                var data = {
                    dNid: subgroup.dNid,
                    iusId: subgroup.iusnid,
                    iGid: iu.iGid,
                    uGid: iu.uGid,
                    sGid: subgroup.sGid,
                    dataValue: subgroup.dv,
                    source: subgroup.src,
                    footnote: subgroup.footnote,
                    timeperiod: $scope.selectedSearchData.tp,
                    areaId: $scope.selectedSearchData.areaNid
                };

                dataList.push(data);

            })

        })


        dataEntryService.saveData({ dbId: $stateParams.dbId, dataEntry: dataList })
        .then(function (success) {
            onSuccessDialogService.show('Data saved successfully');
        }, function (err) {
            errorService.show(err);
        })

    }

} ])