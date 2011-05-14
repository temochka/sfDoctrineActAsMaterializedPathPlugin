# sfDoctrineActAsMaterializedPathPlugin #

## Introduction ##
Plugin sets up the behavior that allows you to store Materialized Path's trees 
in your database. It packaged with sfMaterializedPathTreeManager module for 
managing your trees.

## Features ##
 * fully implemented Doctrine_Node_Interface and Doctrine_Tree_Interface interfaces
 * handling multiple trees in one table
 * automatic updation of node paths on changes
 * horizontal ordering support via sortOrder and sortBy options
 * customizable root_id column's name and path separator string
 * "dead nodes" protection with native database constraints
 * all tree and node logic are partially unit-tested

## Philosophy ##

 * Classical Materialized Path extended by "level" and "parent_id" fields to improve perfomance
 * Plugin uses only DQL.
 * Only new nodes can be inserted in the tree
 * Only existing tree nodes can be moved across the tree

## Get it installed ##

 * go to your project's root
 * Install the plugin (via a Git clone):

        git clone git://github.com/temochka/sfDoctrineActAsMaterializedPathPlugin.git plugins/sfDoctrineActAsMaterializedPathPlugin

 * Or install the plugin (via version-tagged package from [github](https://github.com/temochka/sfDoctrineActAsMaterializedPathPlugin "Github"))

 * Activate the plugin in the config/ProjectConfiguration.class.php

        [php]
        class ProjectConfiguration extends sfProjectConfiguration
        {
          public function setup()
          {
            $this->enablePlugins(array(
              'sfDoctrinePlugin', 
              'sfDoctrineActAsMaterializedPathPlugin',
              '...'
            ));
          }
        }

 * edit config/doctrine/schema.yml and add the Materialized path behavior to the model you want to be taggable.
 
        [yml]
        Post:
          actAs:  { Timestampable: ~ , MaterializedPath: ~ }
 
 * rebuild the model: 
 
        php symfony doctrine:build --all
 
 * clear cache:
 
        php symfony cc

For using sfMaterializedPathTreeManager module:

 * download [jQuery library](http://docs.jquery.com/Downloading_jQuery)
 * enable it in application's view.yml:

        [yml]
        default:
          # ...
          # For example
          javascripts:    [jquery.min.js]
          # ...

 * enable the sfMaterializedPathTreeManagerModule in application's settings.yml:
        
        [yml]
        all:
          .settings:
            # ...
            # modules
            enabled_modules:        [sfMaterializedPathTreeManager]
            # ...

## Usage ##

Usage is similar to NestedSet trees. Interfaces and behaviour's options should be almost compatible.

Example of model with multiple roots and horizontal ordering by "name" field:

    DbFile:
      actAs:
        MaterializedPath:
          hasManyRoots: true
          sortBy: name
      columns:
        name: { type: string(63), notnull: true }

Sort order can be defined by using sortOrder option (ASC, DESC):

    DbFile:
      actAs:
        MaterializedPath:
          hasManyRoots: true
          sortBy: name
          sortOrder: DESC
      columns:
        name: { type: string(63), notnull: true }

You can set up a custom separator for MaterializedPath "path" string by using "pathSeparator" option:

    Node:
      actAs:
        MaterializedPath:
          pathSeparator: '@'
      # ...

To include the sfMaterializedTreeManager's component with all view-related static in your template use sfMaterializedPathTreeManagerHelper helper:

        [php]
        use_helper('sfMaterializedPathTreeManager');
        echo get_tree_manager('DbFile', 'name', $root);

Other examples will appear soon in [project wiki](https://github.com/temochka/sfDoctrineActAsMaterializedPathPlugin/wiki).