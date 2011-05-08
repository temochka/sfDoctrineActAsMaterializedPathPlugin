<ul id="tree_menu_<?php echo strtolower($model); ?>" class="sf_admin_actions">
	<li class="sf_admin_action_insert_file">
		<button id="insert_default" class="button">
        <img alt="" src="/sfDoctrineMaterializedPathPlugin/images/node-insert-next.png"/><?php echo __('Insert File');?>
    </button>
	</li>
  <li class="sf_admin_action_insert_folder">
		<button id="insert_folder" class="button">
        <img alt="" src="/sfDoctrineMaterializedPathPlugin/images/node-insert-next.png"/><?php echo __('Insert Folder');?>
    </button>
	</li>
	<li class="sf_admin_action_delete_node">
	<button id="delete_node" class="button">
      <img alt="" src="/sfDoctrineMaterializedPathPlugin/images/node-delete-next.png"/><?php echo __('Delete Node');?>
  </button>
	</li>
</ul>

<script type="text/javascript">
$(function(){
  $("#tree_menu_<?php echo strtolower($model); ?> button").click(function() {    
    switch (this.id) {
      case 'insert_default':
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