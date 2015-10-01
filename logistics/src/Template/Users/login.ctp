<div class="users form">
<?= $this->Form->create($user) ?>
<?= $this->Flash->render('auth') ?>
Login 
<?php
//echo '<pre>';
//print_r($this->Auth);
 ?>
    <fieldset>
        <legend><?= __('login  User') ?></legend>
        <?= $this->Form->input('username') ?>
        <?= $this->Form->input('password') ?>
        
   </fieldset>
<?= $this->Form->button(__('Submit')); ?>
<?= $this->Form->end() ?>
</div>