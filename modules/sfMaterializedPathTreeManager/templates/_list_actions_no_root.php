<div>
  <form id="<?php echo strtolower($model);?>_root_create" action="<?php echo url_for('sfMaterializedPathTreeManager/Add_root');?>" method="post">
    <input type="hidden" name="model" value="<?php echo $model;?>"/>
    <label for="<?php echo strtolower($model);?>_<?php echo $field;?>"><?php echo ucfirst($field);?> : </label>
    <input type="text" id="<?php echo strtolower($model);?>_<?php echo $field;?>" value="" name="<?php echo strtolower($model);?>[<?php echo $field;?>]"/>    
    <button type="submit">
      <img class="actionImage" src="/sfDoctrineMaterializedPathPlugin/images/node-insert-next.png"/><?php echo __('Create Root');?>
    </button>
  </form>
</div>