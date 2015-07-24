angular.module('DataAdmin.userManagement')
.controller('userManagementController', ['$scope', '$rootScope', 'userManagementService', 'commonService', 'modalService', '$stateParams', '$filter',
function ($scope, $rootScope, userManagementService, commonService, modalService, $stateParams, $filter) {

    $scope.deleteSingleUserId;

    $scope.allUsersSelected = false;

    $scope.selectedUsers = [];

    $scope.usersList = [];

    $scope.searchOption = "name";

    $scope.advancedSearch = false;

    $rootScope.currentDatabase.id = $stateParams.dbId;

    userManagementService.getUsersList($rootScope.currentDatabase.id).then(function (data) {
        $scope.usersList = data;
    }, function (fail) {
        alert(fail);
    });

    $scope.search = {
        roles: '',
        name: '',
        email: ''
    };

    $scope.resetSearch = function () {
        $scope.search.name = '';
    }

    $scope.resetAdvSearch = function () {
        $scope.search.email = '';
        $scope.search.roles = '';
    }

    $scope.toggleAdvancedSearch = function () {
        $scope.advancedSearch = !$scope.advancedSearch;
    }

    $scope.hideAdvancedSearch = function () {
        $scope.advancedSearch = false;
    }

    $scope.selectAllUsers = function () {
        var data = [];

        if ($scope.allUsersSelected) {
            angular.forEach($scope.usersList, function (user) {
                data.push(user.id);
            });
        }

        $scope.selectedUsers = data;
    }

    $scope.userSelected = function (id) {
        if ($scope.selectedUsers.indexOf(id) < 0) {
            $scope.selectedUsers.push(id);
        } else {
            $scope.selectedUsers.splice($scope.selectedUsers.indexOf(id), 1);
        }
    }

    $scope.deleteSelectedUsers = function () {
    }

    $scope.deleteUser = function (userId) {

        $scope.deleteSingleUserId = userId;

        modalService.show({}, {
            closeButtonText: 'Cancel',
            actionButtonText: 'Delete',
            headerText: 'Users',
            bodyText: 'Are you sure you want to delete this record.'
        })
        .then(function (result) {
            confirmDelete();
        });
    }

    function confirmDelete() {

        var usersList;

        if ($scope.deleteSingleUserId) {
            usersList = [$scope.deleteSingleUserId];
        } else {
            usersList = $scope.selectedUsers;
        }

        var data = {
            dbId: $rootScope.currentDatabase.id,
            userIds: usersList
        }

        userManagementService.deleteUsers(data)
        .then(function (res) {

            $scope.usersList = $filter('filter')($scope.usersList, function (value, index) {
                return (usersList.indexOf(value.id) < 0);
            });

            $scope.deleteSingleUserId = '';

            $scope.selectedUsers = [];

        }, function (fail) {
            alert('fail');
        });

    }

} ])
.controller('addModifyUserController', ['$scope', '$rootScope', '$stateParams', '$state', '$timeout', '$filter', 'USER_ROLES', 'userManagementService', 'commonService', 'modalService', 'errorService',
function ($scope, $rootScope, $stateParams, $state, $timeout, $filter, USER_ROLES, userManagementService, commonService, modalService, errorService) {

    $rootScope.currentDatabase.id = $stateParams.dbId;

    $scope.modifyUser = $stateParams.userId ? true : false;

    $scope.createAnother = {
        checked: false
    };

    $scope.showEmailSuggestion = false;

    $scope.showNameSuggestion = false;

    $scope.onBlur = function (objType) {
        $timeout(function () {
            if (objType == 'Email') {
                $scope.showEmailSuggestion = false;
            }
            if (objType == 'Name') {
                $scope.showNameSuggestion = false;
            }
        }, 200);
    }

    $scope.roleClicked = function (roleId) {
        if ($scope.userDetails.roles.indexOf(roleId) < 0) {
            $scope.userDetails.roles.push(roleId);
        } else {
            $scope.userDetails.roles.splice($scope.userDetails.roles.indexOf(roleId), 1);
        }
    }

    if ($scope.modifyUser) {

        userManagementService.getUserDetails({ userId: $stateParams.userId, dbId: $rootScope.currentDatabase.id })
        .then(function (data) {
            $scope.userDetails = {
                id: data.id,
                name: data.name,
                email: data.email,
                roles: data.roles,
                access: data.access,
                dbId: $rootScope.currentDatabase.id
            };
        })
    } else {

        $scope.userDetails = {
            id: '',
            name: '',
            email: '',
            roles: [],
            access: '',
            dbId: $rootScope.currentDatabase.id
        };

        commonService.getAllUsersList().then(function (res) {
            $scope.suggestionUsersList = res;
        }, function (fail) {
            alert(fail);
        })

    }

    $scope.saveUser = function (userDetails) {

        if (!$scope.modifyUser) {
            var suggestedUser = $filter('filter')($scope.suggestionUsersList, { id: userDetails.id })[0];
            if (!(suggestedUser.name === userDetails.name && suggestedUser.email === userDetails.email)) {
                userDetails.id = '';
            }
        }

        userDetails['isModified'] = $scope.modifyUser;

        var msg = $scope.modifyUser ? 'User modified successfully.' : 'User added successfully.';

        userManagementService.addModifyUser(userDetails)
        .then(function (res) {
            if (res) {
                modalService.show({}, {
                    actionButtonText: 'OK',
                    headerText: 'Users',
                    bodyText: msg,
                    showCloseButton: false
                }).then(function (result) {
                    if ($scope.createAnother.checked) {
                        $state.go($state.current, { dbId: $rootScope.currentDatabase.id }, { reload: true });
                    } else {

                        $state.go('DataAdmin.home.userManagement', { dbId: $rootScope.currentDatabase.id });
                    }
                })
            }
        }, function (fail) {
            errorService.show(fail);
        });

    }

    $scope.autoSuggestionSelected = function (suggestedUserDetails) {
        $scope.userDetails.id = suggestedUserDetails.id;
        $scope.userDetails.name = suggestedUserDetails.name;
        $scope.userDetails.email = suggestedUserDetails.email;
    }

    $scope.$watch('userDetails.email', function (newValue) {
        var EMAIL_REGEXP = /^[a-z0-9!#$%&'*+=?^_`{|}~.-]+@[a-z0-9-]+(\.[a-z0-9-]+)*$/i;
        if (newValue != undefined && newValue != '' && newValue.match(EMAIL_REGEXP) == null) {
            $scope.emailInvalid = true;
        } else {
            $scope.emailInvalid = false;
        }
    })

    $scope.resetPassword = function () {
        userManagementService.resetPassword({ userId: $stateParams.userId })
        .then(function (success) {
            onSuccessDialogService.show('A mail was sent to reset the password.');
        }, function (error) {
            errorService.show(error);
        });
    }

} ])
.controller('confirmPasswordController', ['$scope', '$stateParams', '$state', 'userManagementService', 'onSuccessDialogService', 'errorService',
function ($scope, $stateParams, $state, userManagementService, onSuccessDialogService, errorService) {

    $scope.key = $stateParams.key;

    $scope.password = '';

    $scope.confirmPassword = '';

    $scope.savePassword = function (password) {
        if (password !== $scope.confirmPassword) {
            return false;
        } else {
            userManagementService.confirmPassword({ password: password, key: $scope.key })
            .then(function (res) {
                onSuccessDialogService.show('Activation successful.', function () {
                    $state.go('DataAdmin');
                })
            }, function (err) {
                errorService.show(err);
            });
        }
    }

} ])