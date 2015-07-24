angular.module('ngFileUploader', [])
.factory('ngFileUploaderService', ['Upload', '$q', '$http', function (Upload, $q, $http) {

    var ngFileUploaderService = {};

    ngFileUploaderService.uploadFile = function (file, fileData, progressCallBack) {

        var deffered = $q.defer();

        Upload.upload({
            url: fileData.url,
            file: file,
            fields: fileData.fields, // { 'dbId': dbId, 'type': type },
            sendFieldsAs: fileData.sendFieldAs //'form'
        }).progress(function (evt) {
            var progressPercentage = parseInt(100.0 * evt.loaded / evt.total);
            progressCallBack(progressPercentage);
        }).success(function (res) {
            if (res.success) {
                deffered.resolve(res);
            } else {
                deffered.reject(res);
            }
        })

        return deffered.promise;

    }

    return ngFileUploaderService;


} ])
.directive('ngFileUploader', function () {
    return {
        restrict: 'E',
        scope: {
            acceptExt: '=',
            fileData: '=',
            onFileSuccess: '=',
            onFileFail: '=',
            loadingMsg: '=?',
            buttonText: '=?',
            onUploadStart: '=?'
        },
        controller: ['$scope', 'ngFileUploaderService', function ($scope, ngFileUploaderService) {
            $scope.isLoading = false;
            $scope.file = '';
            $scope.progressPercent = 0;
            $scope.enableSelect = true;
            if (angular.isUndefined($scope.buttonText) || $scope.buttonText == '') {
                $scope.buttonText = 'Upload';
            }
            $scope.uploadFile = function () {

                $scope.enableSelect = false;

                $scope.isLoading = true;

                if ($scope.onUploadStart) {
                    $scope.onUploadStart();
                }

                ngFileUploaderService
                .uploadFile($scope.file[0], $scope.fileData, function (progressPercent) {
                    $scope.progressPercent = progressPercent;
                })
                .then(function (success) {
                    $scope.onFileSuccess(success, $scope.fileData);
                    $scope.isLoading = false;
                    $scope.enableSelect = true;
                    $scope.progressPercent = 0;
                }, function (err) {
                    $scope.onFileFail(err, $scope.fileData);
                    $scope.isLoading = false;
                    $scope.enableSelect = true;
                    $scope.progressPercent = 0;
                })
            }
        } ],
        template: ('<div class="upload">' +

                    '<div ng-progress-bar class="upload-box" ngf-select="enableSelect" ngf-accept="acceptExt" ng-model="file">' + // 

                        '<div>' +

                             '<span ng-show="!file[0]">' +
                                    'Upload file' +
                            '</span>' +

                             '<span ng-repeat="file in file">' +
                                    '{{file.name}}' +
                             '</span>' +

                        '</div>' +

                    '</div>' +

                    '<a class="btn btn-default" ng-click="file[0] && uploadFile()"><i class="kd-upload"></i> {{buttonText}} </a>' +

                     '<div class="upload-status">' +

                        '<span class="loading small-text" ng-if="loadingMsg && isLoading">' +

                            '<i class="fa fa-spinner fa-spin"></i> {{loadingMsg}}' +

                        '</span>' +

                    '</div>' +


                  '</div>')
    }

})