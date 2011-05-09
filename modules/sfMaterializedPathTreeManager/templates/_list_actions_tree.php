<ul id="tree_menu_<?php echo strtolower($model); ?>" class="sf_admin_actions">
  <li class="sf_admin_action_insert_folder">
		<button id="insert_folder" class="button">
        <img alt="" src="/sfDoctrineMaterializedPathPlugin/images/node-insert-next.png"/><?php echo __('Insert Node');?>
    </button>
	</li>
	<li class="sf_admin_action_delete_node">
    <button id="delete_node" class="button">
        <img alt="" src="/sfDoctrineMaterializedPathPlugin/images/node-delete-next.png"/><?php echo __('Delete Node');?>
    </button>
	</li>
  <li class="sf_admin_action_back_to_list">or <?php echo link_to(__('back to list'), $sf_context->getRouting()->getCurrentInternalUri()); ?></li>
</ul>

<script type="text/javascript">
$(function(){
  $("#tree_menu_<?php echo strtolower($model); ?> button").click(function() {    
    switch (this.id) {
      case 'insert_folder':
        $("#<?php echo strtolower($model);?>-tree").jstree("create", null, "last", { "attr" : { "rel" : this.id.toString().replace("insert_", "") } });
      break;
      case 'delete_node':
        $("#<?php echo strtolower($model);?>-tree").jstree("remove");
      break;
    }
  });
});
</script>