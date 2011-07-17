<?php
/**
 * Tests for single root model
 * @author Artem Chistyakov <chistyakov.artem@gmail.com>
 * @copyright © Artem Chistyakov, 2011
 */
require __DIR__.'/../bootstrap/unit.php';

$manager = new sfDatabaseManager($configuration);

sfMaterializedPath_TestNodeMultipleTable::getInstance()->createQuery()->delete();
dataLoad($_data_dir.'/test_fixtures');

$table = sfMaterializedPath_TestNodeMultipleTable::getInstance();

$t = new lime_test();

/* @var $root_1 sfMaterializedPath_TestNodeMultiple */
/* @var $root_2 sfMaterializedPath_TestNodeMultiple */
$root_1 = $table->getTree()->fetchRoot();
$t->ok($root_1 instanceof sfMaterializedPath_TestNodeMultiple && $root_1->exists(), 'Root 1 are represented in fixtures.');

$root_2 = $table->getTree()->fetchRoot(2);
$t->ok($root_2 instanceof sfMaterializedPath_TestNodeMultiple && $root_2->exists(), 'Root 2 are represented in fixtures.');

$node = new sfMaterializedPath_TestNodeMultiple();
$node->setName('new_node');

$node->getNode()->insertAsLastChildOf($root_2);
$t->ok($node->getNode()->isValidNode(), 'Node has inserted as last child of $root_2');
$t->is($node->getParentId(), $root_2->getPrimaryKey(), 'Node parent_id has been setted to $root_2 id');

// Trying to make descendant the parent of its ancestor
$msg = 'Root can not be moved as child of its descendant.';
try {
  $root_2->getNode()->moveAsFirstChildOf($node);
  $t->fail($msg);
} catch (Exception $e) {
  $t->pass($msg);
}

$root_1->getNode()->moveAsFirstChildOf($node);

$node->setName('new_root');
$node->getNode()->makeRoot();
$t->ok($node->getNode()->isValidNode(), 'Node has turned into tree root.');
$t->is($node->getParentId(), null, 'Now node is a root and parent_id should be null.');