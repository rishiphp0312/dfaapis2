<div class="users form">
<?= $this->Form->create($user) ?>
<?= $this->Flash->render('auth') ?>
Add User 
<?php
//echo '<pre>';
//print_r($this->Auth);
 ?>
    <fieldset>
        <legend><?= __('Add User') ?></legend>
        <?= $this->Form->input('username') ?>
        <?= $this->Form->input('password') ?>
        
   </fieldset>
<?= $this->Form->button(__('Submit')); ?>
<?= $this->Form->end() ?>
</div>