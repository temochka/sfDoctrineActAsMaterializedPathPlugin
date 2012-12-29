<?php

/**
 * BasesfMaterializedPath_TestNodeSingle
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @property string $name
 * 
 * @method string                            getName() Returns the current record's "name" value
 * @method sfMaterializedPath_TestNodeSingle setName() Sets the current record's "name" value
 * 
 * @package    acts_as_materialized_path
 * @subpackage model
 * @author     Your name here
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
abstract class BasesfMaterializedPath_TestNodeSingle extends sfDoctrineRecord
{
    public function setTableDefinition()
    {
        $this->setTableName('sf_materialized_path__test_node_single');
        $this->hasColumn('name', 'string', 63, array(
             'type' => 'string',
             'notnull' => true,
             'length' => 63,
             ));
    }

    public function setUp()
    {
        parent::setUp();
        $materializedpath0 = new Doctrine_Template_MaterializedPath(array(
             'sortBy' => 'name',
             'sortOrder' => 'ASC',
             ));
        $this->actAs($materializedpath0);
    }
}