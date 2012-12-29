# sfDoctrineActAsMaterializedPathPlugin #

## Introduction ##
This Symfony 1.4 framework plugin introduces new Doctrine behavior allowing you 
to store trees using strengthened Materialized Path algorithm in your database.
It packaged with sfMaterializedPathTreeManager module for managing your trees.

## Features ##

* supports multiple trees in one table
* keeps track of your changes and automatically updates materialized paths
* supports horizontal ordering via sortOrder and sortBy options
* allows customizing root_id column's name and materialized path separator
* uses native database constraints to protect from "dead nodes"
* supports data-dump and data-load tasks
* uses only DQL syntax to stay portable
* unit tested

## Get it installed ##

* go to your project's root
* Install the plugin (via a Git clone):

```shell
git clone git://github.com/temochka/sfDoctrineActAsMaterializedPathPlugin.git plugins/sfDoctrineActAsMaterializedPathPlugin
```

* Or install the plugin (via version-tagged package from [github](https://github.com/temochka/sfDoctrineActAsMaterializedPathPlugin "Github"))

* Activate the plugin in the config/ProjectConfiguration.class.php

```php
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
```

* edit config/doctrine/schema.yml and add the Materialized path behavior to the model you want to be taggable.
 
```yaml
Post:
  actAs:  { Timestampable: ~ , MaterializedPath: ~ }
```
 
* rebuild the model: 

```bash
php symfony doctrine:build --all
```
 
* clear cache:

```bash 
php symfony cc
```

For using sfMaterializedPathTreeManager module:

* download [jQuery library](http://docs.jquery.com/Downloading_jQuery)
* enable it in application's view.yml:

```yaml
default:
  # ...
  # For example
  javascripts:    [jquery.min.js]
  # ...
```

 * enable the sfMaterializedPathTreeManagerModule in application's settings.yml:
        
```yaml
all:
  .settings:
    # ...
    # modules
    enabled_modules:        [sfMaterializedPathTreeManager]
    # ...
```

## Usage ##

Usage is similar to NestedSet trees. Interfaces and behaviour's options should be almost compatible.

Example of model with multiple roots and horizontal ordering by "name" field:

```yaml
DbFile:
  actAs:
    MaterializedPath:
      hasManyRoots: true
      sortBy: name
  columns:
    name: { type: string(63), notnull: true }
```

Sort order can be defined by using sortOrder option (ASC, DESC):

```yaml
DbFile:
  actAs:
    MaterializedPath:
      hasManyRoots: true
      sortBy: name
      sortOrder: DESC
  columns:
    name: { type: string(63), notnull: true }
```

You can set up a custom separator for MaterializedPath "path" string by using "pathSeparator" option:

```yaml
Node:
  actAs:
    MaterializedPath:
      pathSeparator: '@'
  # ...
```

To include the sfMaterializedTreeManager's component with all view-related static in your template use sfMaterializedPathTreeManagerHelper helper:

```php
use_helper('sfMaterializedPathTreeManager');
echo get_tree_manager('DbFile', 'name', $root);
```

Other examples will appear soon in [project wiki](https://github.com/temochka/sfDoctrineActAsMaterializedPathPlugin/wiki).

## Run Unit tests ##

In order to run unit tests clone the repository, proceed to `test` dir, load
database schema by running:

```bash
./symfony doctrine:build --db
```

then run tests using:

```bash
./symfony test:unit
```

Feel free to file pull requests.