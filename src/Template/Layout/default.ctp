<!DOCTYPE html>
<html>
    <head>
        <?php echo $this->Html->charset() ?>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="content-type" content="text/html; charset=UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0" />
        <title>DevInfo Database Administrative Tool</title>
        <script>
            var _WEBSITE_URL = '<?php echo _WEBSITE_URL; ?>';
            var _SCREENHEIGHT = window.innerHeight || document.documentElement.clientHeight || document.body.clientHeight;
        </script>
        <?php echo $this->Html->meta('icon') ?>

        <?php
            echo $this->Html->css([
                _WEBSITE_URL.'DFA/plugins/fuelux/css/fuelux.css',
                _WEBSITE_URL.'DFA/plugins/icheck/skins/minimal/grey.css',
                _WEBSITE_URL.'DFA/plugins/bootstrap/css/bootstrap.min.css',
                _WEBSITE_URL.'DFA/plugins/font-awesome/css/font-awesome.min.css',
                _WEBSITE_URL.'DFA/plugins/menusidebar/css/simple-sidebar.css',
                _WEBSITE_URL.'DFA/plugins/scrolltabs/css/scrolltabs.css',
                _WEBSITE_URL.'js/app/shared/ngTreeView/treeView.css',
                _WEBSITE_URL.'js/app/shared/loadingBar/loading-bar.css',
                _WEBSITE_URL.'DFA/css/kordit/kordit.css',
                _WEBSITE_URL.'DFA/css/layout.css',
                _WEBSITE_URL.'DFA/css/themes/layout.purple.css'
            ])
        ?>

        <?php
            echo $this->Html->script([
            _WEBSITE_URL.'DFA/js/jquery-2.1.4.min.js',
            _WEBSITE_URL.'DFA/plugins/bootstrap/js/bootstrap.js',
            _WEBSITE_URL.'DFA/js/angular.min.js',
            _WEBSITE_URL.'DFA/js/css_browser_selector.js',
            _WEBSITE_URL.'DFA/plugins/icheck/icheck.js',
            _WEBSITE_URL.'DFA/plugins/icheck/icheck.js',
            'app/shared/uiBootstrap/ui-bootstrap',
            'app/shared/angular-cookies.min',
            'app/shared/angular-ui-router.min','app/shared/ngDialog.min',
            'app/shared/ngFileUpload/ng-file-upload-shim','app/shared/ngFileUpload/ng-file-upload', 
            'app/shared/ngProgressBar/ng-progress-bar', 'app/shared/ngFileUploader/ng-file-uploader',
            'app/shared/ngTreeView/ng-tree-view', 'app/shared/loadingBar/loading-bar.js',
            'app/shared/iCheck/iCheck.js',
            'app/components/database/database.module', 'app/components/database/database.controller', 'app/components/database/database.service',
            'app/components/home/home.module', 'app/components/home/home.controller', 'app/components/home/home.service',
            'app/components/userManagement/userManagement.module','app/components/userManagement/userManagement.controller','app/components/userManagement/userManagement.service',
            'app/components/iusManagement/iusManagement.module','app/components/iusManagement/iusManagement.controller','app/components/iusManagement/iusManagement.service',
            'app/components/importExportManagement/templateImportExport.module','app/components/importExportManagement/templateImportExport.controller','app/components/importExportManagement/templateImportExport.service',
            'app/components/dataEntry/dataEntry.module','app/components/dataEntry/dataEntry.controller','app/components/dataEntry/dataEntry.service',
            'app/appConfig','app/app','app/components/core/core.controller','app/components/core/core.service','app/components/core/core.constant','app/components/core/core.config'])
        ?>

    </head>

    <body ng-controller="appController">

        <header ui-view="header">
        </header>

        <div ui-view="content">
        </div>

        <footer ui-view="footer">
        </footer>

    </body>
</html>
