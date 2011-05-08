<?php if (!$records) :?>
	<?php include_partial('sfMaterializedPathTreeManager/list_actions_no_root', array('model' => $model, 'field' => $field, 'root' => $root)) ?>
<?php else : ?>
	<?php include_partial('sfMaterializedPathTreeManager/list_actions_tree', array('model' => $model, 'field' => $field, 'root' => $root, 'hasManyRoots' => $hasManyRoots)) ?>
<?php endif; ?>