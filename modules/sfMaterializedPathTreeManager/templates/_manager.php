<?php use_helper('I18N', 'Date', 'JavascriptBase') ?>
<?php include_partial('sfMaterializedPathTreeManager/assets') ?>
<div id="sf_admin_container">
  <h1><?php echo sfInflector::humanize(sfInflector::underscore($model)); ?> <?php echo __('Tree manager'); ?></h1>
  <?php include_partial('sfMaterializedPathTreeManager/flashes') ?>
  <?php include_partial('sfMaterializedPathTreeManager/manager_tree', array('model' => $model, 'field' => $field, 'root' => $root, 'records' => $records, 'hasManyRoots' => $hasManyRoots)) ?>
</div>



