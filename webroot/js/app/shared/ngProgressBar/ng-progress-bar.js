/*
 *   Custom Progress Bar
 */

angular.module('ngProgressBar', [])

.directive('ngProgressBar', function () {
    return {
        restrict: 'A',
        link: function (scope, element, attrs) {

            var progressBarProperties = {
                baseColor: '#fff',
                barColor: '#69E089'
            };

            if (!angular.isUndefined(scope.properties)) {
                progressBarProperties = scope.properties;
            }

            scope.$watch('progressPercent', function (newValue) {
                element.css('background', 'linear-gradient(90deg, ' + progressBarProperties.barColor + ' ' + newValue + '%, ' + progressBarProperties.baseColor + ' 0%)');
            });
        }
    }
})