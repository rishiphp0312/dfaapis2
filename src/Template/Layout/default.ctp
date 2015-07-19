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
        <?php echo $this->Html->css(['reset','styleguide','style','responsive','font-awesome.min','ngDialog', 'loading-bar']) ?>
        <?php
            echo $this->Html->script(['lib/jquery-2.1.4.min','lib/angular.min','lib/angular-cookies.min',
            'app/shared/loadingBar/loading-bar', 'app/shared/angular-ui-router.min','app/shared/ngDialog.min',
            'app/shared/ngFileUpload/ng-file-upload-shim','app/shared/ngFileUpload/ng-file-upload', 
            'app/shared/ngProgressBar/ng-progress-bar', 'app/shared/ngFileUploader/ng-file-uploader',
            'app/shared/ngTreeView/ng-tree-view','site',
            'app/components/login/login.module', 'app/components/login/login.controller', 
            'app/components/database/database.module', 'app/components/database/database.controller', 'app/components/database/database.service',
            'app/components/databaseManagement/databaseManagement.module','app/components/databaseManagement/databaseManagement.controller','app/components/databaseManagement/databaseManagement.service',
            'app/components/userManagement/userManagement.module','app/components/userManagement/userManagement.controller','app/components/userManagement/userManagement.service',
            'app/components/iusManagement/iusManagement.module','app/components/iusManagement/iusManagement.controller','app/components/iusManagement/iusManagement.service',
            'app/components/importExportManagement/templateImportExport.module','app/components/importExportManagement/templateImportExport.controller','app/components/importExportManagement/templateImportExport.service',
            'app/components/dataEntry/dataEntry.module','app/components/dataEntry/dataEntry.controller','app/components/dataEntry/dataEntry.service',
            'app/appConfig','app/app','app/components/core/core.controller','app/components/core/core.service','app/components/core/core.constant','app/components/core/core.config'])
        ?>
    </head>
    <body ng-controller="appController">
        <div id="wrapper">
        <header class="main-header darkblue" ui-view="header">
        </header>
        <section class="main">
            <div ui-view="content">
            </div>
        </section>
        <footer class="footer darkblue" ui-view="footer">
        </footer>
        </div>
    </body>
</html>
