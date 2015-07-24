angular.module(appConfig.appName, appConfig.appDependencies)

angular.element(document).ready(function () {
    angular.bootstrap(document, [appConfig.appName]);
});