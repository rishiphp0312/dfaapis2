<?php
/**
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @since         0.10.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
use Cake\Cache\Cache;
use Cake\Core\Configure;
use Cake\Datasource\ConnectionManager;
use Cake\Error\Debugger;
use Cake\Network\Exception\NotFoundException;

$this->layout = false;

if (!Configure::read('debug')):
    throw new NotFoundException();
endif;

$cakeDescription = 'CakePHP: the rapid development php framework';
?>
<!DOCTYPE html>
<html>
<head>
    <?php echo  $this->Html->charset() ?>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
	 <link href="css/reset.css" rel="stylesheet">
        <link href="css/styleguide.css" rel="stylesheet">
        <link href="css/style.css" rel="stylesheet">
        <link href="css/responsive.css" rel="stylesheet">
        <link href="css/font-awesome.min.css" rel="stylesheet" type="text/css">
        <script type="text/javascript" src="js/lib/angular.min.js"></script>
        <script type="text/javascript" src="js/lib/angular-route.min.js"></script>
        <script type="text/javascript" src="js/lib/jquery-1.11.3.js"></script>
        <script type="text/javascript" src="js/custom.js"></script>
        <script type="text/javascript" src="js/app.js"></script>
    <title>
        <?php echo $cakeDescription ?>
    </title>
    <?php echo $this->Html->meta('icon') ?>
    <?php echo  $this->Html->css('base.css') ?>
    <?php echo $this->Html->css('cake.css') ?>
</head>
<body class="home">
   <?php 
  echo $this->element('header');
  ?>
    <div id="content">
        <?php
        if (Configure::read('debug')):
            Debugger::checkSecurityKeys();
        endif;
        ?>
        <p id="url-rewriting-warning" style="background-color:#e32; color:#fff;display:none">
            URL rewriting is not properly configured on your server.
            1) <a target="_blank" href="http://book.cakephp.org/3.0/en/installation/url-rewriting.html" style="color:#fff;">Help me configure it</a>
            2) <a target="_blank" href="http://book.cakephp.org/3.0/en/development/configuration.html#general-configuration" style="color:#fff;">I don't / can't use URL rewriting</a>
        </p>

        <div class="row">
           
           
        <div class="row">
           dfdfdff
        </div>
        <div class="row">
            <div class="columns large-6">
                <h3>Editing this Page</h3>
                <ul>
                    <li>To change the content of this page, edit: src/Template/Pages/home.ctp.</li>
                    <li>You can also add some CSS styles for your pages at: webroot/css/.</li>
                </ul>
            </div>
            <div class="columns large-6">
                <h3>Getting Started</h3>
                <ul>
                    <li><a target="_blank" href="http://book.cakephp.org/3.0/en/">CakePHP 3.0 Docs</a></li>
                    <li><a target="_blank" href="http://book.cakephp.org/3.0/en/tutorials-and-examples/bookmarks/intro.html">The 15 min Bookmarker Tutorial</a></li>
                    <li><a target="_blank" href="http://book.cakephp.org/3.0/en/tutorials-and-examples/blog/blog.html">The 15 min Blog Tutorial</a></li>
                </ul>
                <p>
            </div>
        </div>

        <hr/>
        <div class="row">
            <div class="columns large-12">
                <h3 class="">More about Cake</h3>
                <p>
                    CakePHP is a rapid development framework for PHP which uses commonly known design patterns like Front Controller and MVC.
                </p>
                <p>
                    Our primary goal is to provide a structured framework that enables PHP users at all levels to rapidly develop robust web applications, without any loss to flexibility.
                </p>

                <ul>
                    <li><a href="http://cakefoundation.org/">Cake Software Foundation</a>
                    <ul><li>Promoting development related to CakePHP</li></ul></li>
                    <li><a href="http://www.cakephp.org">CakePHP</a>
                    <ul><li>The Rapid Development Framework</li></ul></li>
                    <li><a href="http://book.cakephp.org/3.0/en/">CakePHP Documentation</a>
                    <ul><li>Your Rapid Development Cookbook</li></ul></li>
                    <li><a href="http://api.cakephp.org/3.0/">CakePHP API</a>
                    <ul><li>Quick Reference</li></ul></li>
                    <li><a href="http://bakery.cakephp.org">The Bakery</a>
                    <ul><li>Everything CakePHP</li></ul></li>
                    <li><a href="http://plugins.cakephp.org">CakePHP plugins repo</a>
                    <ul><li>A comprehensive list of all CakePHP plugins created by the community</li></ul></li>
                    <li><a href="https://groups.google.com/group/cake-php">CakePHP Google Group</a>
                    <ul><li>Community mailing list</li></ul></li>
                    <li><a href="irc://irc.freenode.net/cakephp">irc.freenode.net #cakephp</a>
                    <ul><li>Live chat about CakePHP</li></ul></li>
                    <li><a href="https://github.com/cakephp/">CakePHP Code</a>
                    <ul><li>For the Development of CakePHP Git repository, Downloads</li></ul></li>
                    <li><a href="https://github.com/cakephp/cakephp/issues">CakePHP Issues</a>
                    <ul><li>CakePHP issues and pull requests</li></ul></li>
                </ul>
            </div>
        </div>
    </div>
	<?php 
  echo $this->element('footer');
  ?>
</body>
</html>
