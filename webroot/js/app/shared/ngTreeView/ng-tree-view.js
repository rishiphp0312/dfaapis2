/*
# Module: Tree View Gererator
# Input Params: treeListModel, properties, ngModel

######## Tree List ########
Structure: 
Node: 
{
id: '',
fields: {},
returnData: {},
isChildAvailable: true/false,
nodes: [
Node,
Node,
Node
]
}
            
Overview: 
[ Node, Node, Node ]

Definition: 
Node - 
fields - 
returnData -
isChildAvailable -
nodes - 

###########################

######## Properties ########
Structure:
{
onDemand: true,
onDemandOptions: {
url: '',
responseDataKey: ['data', 'area'],
requestDataKey: 'returnData'
},
selectionOptions: {
multiSelection: true,
showCheckBox: true,
checkBoxClass: '',
selectedClass: '',
selectedHTML: ''    
},
nodeOptions: {
showNodeOpenCloseClass: true,
nodeOpenClass: 'fa fa-plus',
nodeCloseClass: 'fa fa-minus',
nodeLeafClass: '',
showLoader: true,
loaderClass: ''
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

############################

*/

angular.module('ngTreeView', [])
    .factory('ngTreeViewService', ['$q', '$http', function ($q, $http) {

        var treeViewService = {};

        treeViewService.getOnDemandResult = function (options, data) {

            var deferred = $q.defer();

            $http({
                url: options.url,
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                data: $.param(data),
                method: 'POST'
            }).success(function (success) {
                angular.forEach(options.responseDataKey, function (value) {
                    if (success[value] != undefined) {
                        success = success[value];
                    }
                })
                deferred.resolve(success);
            })

            return deferred.promise;

        }

        return treeViewService;

    } ])
    .directive('ngTreeView', ['$compile', function ($compile) {

        function ensureDefault(obj, prop, value) {
            if (!obj.hasOwnProperty(prop))
                obj[prop] = value;
        }

        return {
            restrict: 'E',
            transclude: true,
            require: '^ngTreeView',
            scope: {
                treeListModel: '=',
                options: '=treeViewOptions',
                selectedNodes: "=ngModel",
                expandedNodes: "=?",
                searchText: "=?"
            },
            controller: ['$scope', '$filter', 'ngTreeViewService', function ($scope, $filter, ngTreeViewService) {

                // contains all options for a tree view.
                $scope.options = $scope.options || {};

                setDefaults();

                // list of all the nodes that are selected
                $scope.selectedNodes = $scope.selectedNodes || [];

                // list of all the expanded nodes.
                $scope.expandedNodes = $scope.expandedNodes || [];

                $scope.expandNode = function (node) {

                    var expanding = ($scope.expandedNodes.indexOf(node.id) < 0);

                    // if node is to be expanded than add expandedNodes else remove from expandedNodes
                    if (expanding) {
                        if (node.isChildAvailable && $scope.options.onDemand && (!node.nodes || node.nodes.length <= 0)) {
                            node.loading = true;
                            ngTreeViewService.getOnDemandResult($scope.options.onDemandOptions, node[$scope.options.onDemandOptions.requestDataKey]).then(function (data) {
                                node.nodes = data;
                                node.loading = false;
                                $scope.expandedNodes.push(node.id);
                            })
                        } else {
                            $scope.expandedNodes.push(node.id);
                        }

                    } else {
                        $scope.expandedNodes.splice($scope.expandedNodes.indexOf(node.id), 1);
                    }

                }

                $scope.nodeExpanded = function () {
                    return ($scope.expandedNodes.indexOf(this.node.id) >= 0);
                };

                $scope.selectNode = function (selectedNode) {

                    var selected = false;

                    var pos = -1;

                    for (var i = 0; i < $scope.selectedNodes.length; i++) {
                        if (selectedNode.id === $scope.selectedNodes[i].id) {
                            pos = i;
                            break;
                        }
                    }

                    if (!$scope.options.selectionOptions.multiSelection) {
                        $scope.selectedNodes = [];
                    }

                    if (pos === -1) {

                        $scope.selectedNodes.push({
                            id: selectedNode.id,
                            returnData: selectedNode.returnData,
                            fields: selectedNode.fields
                        });
                        selected = true;
                    } else {
                        if ($scope.options.selectionOptions.multiSelection) {
                            $scope.selectedNodes.splice(pos, 1);
                        }
                    }

                }

                $scope.nodeSelected = function (node) {

                    var pos = -1;

                    for (var i = 0; i < $scope.selectedNodes.length; i++) {
                        if (node.id === $scope.selectedNodes[i].id) {
                            pos = i;
                            if ($scope.selectedNodes[i].fields == undefined) {
                                $scope.selectedNodes[i].fields = node.fields;
                            }
                            if ($scope.selectedNodes[i].returnData == undefined) {
                                $scope.selectedNodes[i].returnData = node.returnData;
                            }
                            break;
                        }
                    }

                    return !(pos === -1);

                }

                /***** Private Functions *****/

                function setDefaults() {
                    // set defaults
                    ensureDefault($scope.options, 'onDemand', false);
                    ensureDefault($scope.options, 'onDemandOptions', {});
                    ensureDefault($scope.options, 'selectionOptions', {});
                    ensureDefault($scope.options, 'nodeOptions', {});
                    ensureDefault($scope.options, 'labelOptions', {});

                    ensureDefault($scope.options.selectionOptions, 'selectedClass', 'nodeSelected');
                    ensureDefault($scope.options.selectionOptions, 'multiSelection', false);
                    ensureDefault($scope.options.selectionOptions, 'showCheckBox', false);
                    ensureDefault($scope.options.selectionOptions, 'checkBoxClass', '');
                    ensureDefault($scope.options.selectionOptions, 'selectedHTML', '');

                    ensureDefault($scope.options.nodeOptions, 'showNodeOpenCloseClass', true);
                    ensureDefault($scope.options.nodeOptions, 'nodeOpenClass', 'fa fa-plus');
                    ensureDefault($scope.options.nodeOptions, 'nodeCloseClass', 'fa fa-close');
                    ensureDefault($scope.options.nodeOptions, 'showNodeLeafClass', false);
                    ensureDefault($scope.options.nodeOptions, 'nodeLeafClass', '');
                    ensureDefault($scope.options.nodeOptions, 'showLoader', true);
                    ensureDefault($scope.options.nodeOptions, 'loaderClass', 'fa fa-spinner fa-spin');

                    ensureDefault($scope.options.labelOptions, 'fields', []);
                    ensureDefault($scope.options.labelOptions, 'prefix', '');
                    ensureDefault($scope.options.labelOptions, 'suffix', '');
                    ensureDefault($scope.options.labelOptions, 'class', '');
                }

                function buildLabelHtml() {

                    var html = '';

                    html = (
                            '<span class="lblTxt ' + ($scope.options.labelOptions.class) + '">' +
                                selectedHtml() +
                                checkBoxHtml() +
                                '<span class="nodeText">' +
                                    buildLabelString() +
                                '</span>' +
                            '</span>'
                    )
                    return html;

                }

                function buildLabelString() {

                    var html = '';

                    var fields = [];

                    if ($scope.options.labelOptions.fields.length > 0) {
                        fields = $scope.options.labelOptions.fields;


                        for (i = 0; i < fields.length; i++) {

                            html += '<span ng-if="node.fields.' + fields[i].id + '" class="' + (fields[i].css || '') + '">' +
                                '{{node.fields.' + fields[i].id + ' + "' + fields[i].seperator + '"}}' +
                                '</span>';

                        }


                    }

                    return html;

                }

                function checkBoxHtml() {

                    var html = '';

                    html = ($scope.options.selectionOptions.showCheckBox ? '<input type="checkbox" stop-event="click" class="' + ($scope.options.selectionOptions.checkBoxClass) + '"  ng-checked="nodeSelected(node)">' : ''); //ng-click="selectNode(node)" 

                    return html;

                }

                function selectedHtml() {

                    var html = '';
                    if ($scope.options.selectionOptions.selectedHTML != '') {
                        html = '<span ng-show="nodeSelected(node)">' + $scope.options.selectionOptions.selectedHTML + '</span>';
                    }

                    return html;

                }

                function loaderHtml() {

                    var html = '';

                    if ($scope.options.nodeOptions.showLoader && $scope.options.onDemand) {
                        html = '<i class="' + $scope.options.nodeOptions.loaderClass + '" ng-show="node.loading"></i>';
                    }

                    return html;

                }

                function nodeOpenCloseHtml() {

                    var html = '';
                    if ($scope.options.nodeOptions.showNodeOpenCloseClass) {
                        html = (
                            '<span class="control-box">' +
                                '<span>' +
                                    '<i class="' + $scope.options.nodeOptions.nodeOpenClass + '" ng-show="node.isChildAvailable && !nodeExpanded()" stop-event="click" ng-click="expandNode(node)" ></i>' +
                                    '<i class="' + $scope.options.nodeOptions.nodeCloseClass + '" ng-show="node.isChildAvailable && nodeExpanded()" stop-event="click" ng-click="expandNode(node)" ></i>' +
                                    '<i class="' + $scope.options.nodeOptions.nodeLeafClass + ' ng-show="!node.isChildAvailable""></i>' +
                                '</span>' +
                            '</span>'
                        )
                    }

                    return html;
                }

                var template = (
                        '<ul>' +
                            '<li ng-repeat="node in node.nodes | filter: searchText">' +
                                '<div class="list-container">' +
                                    '<div class="list-header" ng-click="selectNode(node)" ng-class="{' + $scope.options.selectionOptions.selectedClass + ': nodeSelected(node)}">' +
                                        nodeOpenCloseHtml() +
                                        '<span class="lableText">' +
                                            buildLabelHtml() +
                                            '<span class="control-box">' +
                                                loaderHtml() +
                                            '</span>' +
                                        '</span>' +
                                    '</div>' +
                                    '<tree-item class="list-child" ng-if="nodeExpanded()">' +
                                    '</tree-item>' +
                                '</div>' +
                            '</li>' +
                        '</ul>'
                );

                this.template = $compile(template);


            } ],
            link: function link(scope, element, attrs, controller, transcludeFn) {

                scope.$watch('treeListModel', function (newValue) {
                    if (angular.isArray(newValue)) {
                        if (angular.isDefined(scope.node) && angular.equals(scope.node['nodes'], newValue))
                            return;
                        scope.node = {};
                        scope.synteticRoot = scope.node;
                        scope.node['nodes'] = newValue;
                    } else {
                        if (angular.equals(scope.node, newValue))
                            return;
                        scope.node = newValue;
                    }
                });

                controller.template(scope, function (clone) {
                    element.html('').append(clone);
                });
            }
        }
    } ])
    .directive("treeItem", function () {
        return {
            restrict: 'E',
            require: "^ngTreeView",
            link: function (scope, element, attrs, controller) {
                // Rendering template for the current node
                controller.template(scope, function (clone) {
                    element.html('').append(clone);
                });
            }
        }
    })