 <?php if( isset($records) && is_object($records) && count($records) > 0 ): ?>
    <div id="<?php echo strtolower($model);?>-tree">
<?php /*<ul class="nested_set_list">
        <?php $prevLevel = 0;?>      
        <?php foreach($records as $record):?>
            <?php if($prevLevel > 0 && $record['level'] == $prevLevel)  echo '</li>';
            if($record['level'] > $prevLevel)  echo '<ul>'; 
            elseif ($record['level'] < $prevLevel) echo str_repeat('</ul></li>', $prevLevel - $record['level']); ?>
            <li id ="phtml_<?php echo $record->id ?>">
                <a href="#"><ins>&nbsp;</ins><?php echo $record->$field;?></a>
            <?php $prevLevel = $record['level'];
        endforeach; ?>        
        </ul> */ ?>
    </div>
<?php endif;?>
<?php echo javascript_tag();?>
$(function () {
	// Settings up the tree - using $(selector).jstree(options);
	// All those configuration options are documented in the _docs folder
	$("#<?php echo strtolower($model);?>-tree")
		.jstree({ 
			// the list of plugins to include
			"plugins" : [ "themes", "json_data", "ui", "crrm", "cookies", "dnd", "search", "types", "hotkeys", "contextmenu" ],
			// Plugin configuration
      "themes" : {
        "theme" : "apple"
      },
      "contextmenu" : {
        "items" : {
          "edit" : {
            "label" : "<?php echo __('Edit '.$model); ?>",
            "action" : function (n) {
              document.location.href='item/'+(n.attr ? n.attr("id").replace("node_", "") : 0)+'/edit';
            }
          }        
        }
      },
			// I usually configure the plugin that handles the data first - in this case JSON as it is most common
			"json_data" : { 
				// I chose an ajax enabled tree - again - as this is most common, and maybe a bit more complex
				// All the options are the same as jQuery's except for `data` which CAN (not should) be a function
				"ajax" : {
					// the URL to fetch the data
					"url" : "<?php echo url_for('sfMaterializedPathTreeManager/get_children'); ?>",
					// this function is executed in the instance's scope (this refers to the tree instance)
					// the parameter is the node being loaded (may be -1, 0, or undefined when loading the root nodes)
					"data" : function (n) { 
						// the result is fed to the AJAX request `data` option
						return { 
							"model" : "<?php echo $model; ?>", 
							"parent_id" : n.attr ? n.attr("id").replace("node_","") : 0 
						}; 
					}
				}
			},
			// Configuring the search plugin
			"search" : {
				// As this has been a common question - async search
				// Same as above - the `ajax` config option is actually jQuery's object (only `data` can be a function)
				"ajax" : {
					"url" : "<?php echo url_for('sfMaterializedPathTreeManager/search'); ?>",
					// You get the search string as a parameter
					"data" : function (str) {
						return { 
							"model" : "<?php echo $model; ?>", 
							"search_str" : str 
						}; 
					}
				}
			},
			// Using types - most of the time this is an overkill
			// Still meny people use them - here is how
			"types" : {
				// I set both options to -2, as I do not need depth and children count checking
				// Those two checks may slow jstree a lot, so use only when needed
				"max_depth" : -2,
				"max_children" : -2,
				// I want only `drive` nodes to be root nodes 
				// This will prevent moving or creating any other type as a root node
				"valid_children" : [ "drive" ],
				"types" : {
					// The default type
					"default" : {
						// I want this type to have no children (so only leaf nodes)
						// In my case - those are files
						"valid_children" : "none",
						// If we specify an icon for the default type it WILL OVERRIDE the theme icons
						"icon" : {
							"image" : "/sfMaterializedPathTreeManagerPlugin/images/file.png"
						}
					},
					// The `folder` type
					"folder" : {
						// can have files and other folders inside of it, but NOT `drive` nodes
						"valid_children" : [ "default", "folder" ],
						"icon" : {
							"image" : "/sfMaterializedPathTreeManagerPlugin/images/folder.png"
						}
					},
					// The `drive` nodes 
					"drive" : {
						// can have files and folders inside, but NOT other `drive` nodes
						"valid_children" : [ "default", "folder" ],
						"icon" : {
							"image" : "/sfMaterializedPathTreeManagerPlugin/images/root.png"
						},
						// those options prevent the functions with the same name to be used on the `drive` type nodes
						// internally the `before` event is used
						"start_drag" : false,
						"move_node" : false,
						"delete_node" : false,
						"remove" : false
					}
				}
			},
			// For UI & core - the nodes to initially select and open will be overwritten by the cookie plugin

			// the UI plugin - it handles selecting/deselecting/hovering nodes
			"ui" : {
				// this makes the node with ID node_4 selected onload
				"initially_select" : [ "node_4" ]
			},
			// the core plugin - not many options here
			"core" : { 
				// just open those two nodes up
				// as this is an AJAX enabled tree, both will be downloaded from the server
				"initially_open" : [ "node_2" , "node_3" ] 
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
					}
					else {
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