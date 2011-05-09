<?php if (null !== $root): ?>
  <div id="<?php echo strtolower($model);?>-tree"></div>
  <?php echo javascript_tag();?>
  $(function () {	
    $("#<?php echo strtolower($model);?>-tree")
      .jstree({ 
        "plugins" : [ "themes", "json_data", "ui", "crrm", "cookies", "dnd", "search", "types", "hotkeys", "contextmenu" ],
        "themes" : {
          "theme" : "apple"
        },
        "contextmenu" : {
          "items" : {
            create : false,
            "edit_node" : {
              "label" : "<?php echo __('Edit '.sfInflector::humanize($model)); ?>",
              "action" : function (n) {
              document.location.href='<?php echo sfInflector::tableize($model);?>/'+(n.attr ? n.attr("id").replace("node_", "") : 0)+'/edit';
              }
            }
          }
        },
        "json_data" : { 
          "ajax" : {
            "url" : "<?php echo url_for('sfMaterializedPathTreeManager/get_children'); ?>",
            "data" : function (n) { 
              return { 
                "model" : "<?php echo $model; ?>", 
                "parent_id" : n.attr ? n.attr("id").replace("node_","") : 0,
                "root_id" : "<?php echo $root; ?>"
              }; 
            }
          }
        },
        "search" : {
          "ajax" : {
            "url" : "<?php echo url_for('sfMaterializedPathTreeManager/search'); ?>",
            "data" : function (str) {
              return { 
                "model" : "<?php echo $model; ?>", 
                "search_str" : str 
              }; 
            }
          }
        },
        "types" : {
          "max_depth" : -2,
          "max_children" : -2,
          "valid_children" : [ "drive" ],
          "types" : {
            "node" : {
              "valid_children" : [ "node" ],
              "icon" : {
                "image" : "/sfDoctrineMaterializedPathPlugin/images/folder.png"
              }
            },
            "root" : {
              "valid_children" : [ "node" ],
              "icon" : {
                "image" : "/sfDoctrineMaterializedPathPlugin/images/root.png"
              },
              "start_drag" : false,
              "move_node" : false,
            }
          }
        }
      })
      .bind("create.jstree", function (e, data) {
        $.post(
          "<?php echo url_for('sfMaterializedPathTreeManager/add_child'); ?>", 
          { 
            "model" : "<?php echo $model; ?>", 
            "parent_id" : data.rslt.parent.attr("id").replace("node_",""), 
            "position" : data.rslt.position,
            "field" : "<?php echo $field; ?>",
            "value" : data.rslt.name,
            "type" : data.rslt.obj.attr("rel")
          }, 
          function (r) {
            if(r.status) {
              $(data.rslt.obj).attr("id", "node_" + r.id);
            } else {
              $.jstree.rollback(data.rlbk);
            }
          }
        );
      })
      .bind("remove.jstree", function (e, data) {
        data.rslt.obj.each(function () {
          $.ajax({
            async : false,
            type: 'POST',
            url: "<?php echo url_for('sfMaterializedPathTreeManager/delete'); ?>",
            data : { 
              "model" : "<?php echo $model; ?>", 
              "id" : this.id.replace("node_","")
            },
            success : function (r) {
              if(!r.status) {
                data.inst.refresh();
              }
            }
          });
        });
      })
      .bind("rename.jstree", function (e, data) {
        $.post(
          "<?php echo url_for('sfMaterializedPathTreeManager/edit_field'); ?>", 
          { 
            "model" : "<?php echo $model; ?>",           
            "id" : data.rslt.obj.attr("id").replace("node_",""),
            "field" : "<?php echo $field; ?>",
            "value" : data.rslt.new_name
          }, 
          function (r) {
            if(!r.status) {
              $.jstree.rollback(data.rlbk);
            }
          }
        );
      })
      .bind("move_node.jstree", function (e, data) {
        data.rslt.o.each(function (i) {
          $.ajax({
            async : false,
            type: 'POST',
            url: "<?php echo url_for('sfMaterializedPathTreeManager/move'); ?>",
            data : { 
              "model" : "<?php echo $model; ?>", 
              "id" : $(this).attr("id").replace("node_",""), 
              "target_id" : data.rslt.np.attr("id").replace("node_",""), 
              "position" : data.rslt.cp + i,
              "title" : data.rslt.name,
              "copy" : data.rslt.cy ? 1 : 0
            },
            success : function (r) {
              if(!r.status) {
                $.jstree.rollback(data.rlbk);
              }
              else {
                $(data.rslt.oc).attr("id", "node_" + r.id);
                if(data.rslt.cy && $(data.rslt.oc).children("UL").length) {
                  data.inst.refresh(data.inst._get_parent(data.rslt.oc));
                }
              }
              $("#analyze").click();
            }
          });
        });
      });
  });
  <?php echo end_javascript_tag();?>
<?php endif; ?>