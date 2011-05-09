<div class="nested_set_manager_holder" id="<?php echo strtolower($model); ?>_nested_set_manager_holder">
    <?php echo get_partial('sfMaterializedPathTreeManager/tree', array('model' => $model, 'field' => $field, 'root' => $root)); ?>
    <div style="clear:both">&nbsp;</div>
</div>

<div class="sf_admin_actions">
  <?php include_partial('sfMaterializedPathTreeManager/list_batch_actions') ?>
  <?php include_partial('sfMaterializedPathTreeManager/list_actions', array('model' => $model, 'field' => $field, 'root' => $root, 'hasManyRoots' => $hasManyRoots)); ?>
</div>