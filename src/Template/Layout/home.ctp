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

$cakeDescription = 'CakePHP: the rapid development php framework';
?>
<!DOCTYPE html>
<html>
<head>
    	<?php echo $this->Html->charset() ?>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
	 <meta http-equiv="content-type" content="text/html; charset=UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0" />
        <title>DevInfo Database Administrative Tool</title>
    
	<?php echo $this->Html->meta('icon') ?>
    <?php echo   $this->Html->css(['base','cake','reset','style','responsive','font-awesome.min']) ?>
  
    <?php echo $this->Html->css('font-awesome.min.css') ?>
	<?php echo $this->Html->script(['lib/angular.min','lib/angular-route.min','lib/jquery-1.11.3','custom','app']) ?>
	


	
</head>
<body>
  <?php 
  echo $this->element('header');

  ?>
    <div id="container">

        <div id="content">
            <?= $this->Flash->render() ?>

            <div class="row">
                <?= $this->fetch('content') ?>
            </div>
        </div>
       <?php 
  echo $this->element('footer');
  ?>
    </div>
</body>
</html>