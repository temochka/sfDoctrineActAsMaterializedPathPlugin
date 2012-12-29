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

// Creating the first tree root
$root_1 = $table->getTree()->fetchRoot();
$t->ok($root_1 instanceof sfMaterializedPath_TestNodeMultiple && $root_1->exists(), 'Root 1 are represented in fixtures.');

// Creating the second tree root
$root_2 = $table->getTree()->fetchRoot(2);
$t->ok($root_2 instanceof sfMaterializedPath_TestNodeMultiple && $root_2->exists(), 'Root 2 are represented in fixtures.');

// Creating new node and inserting it as a child of $root_2
$node = new sfMaterializedPath_TestNodeMultiple();
$node->setName('new_node');
$node->getNode()->insertAsLastChildOf($root_2);

$t->ok($node->getNode()->isValidNode(), 'Node has inserted as last child of $root_2');
$t->is($node->getParentId(), $root_2->getPrimaryKey(), 'Node parent_id has been setted to $root_2 id');

// Trying to treat parent as a child of its descendant
$msg = 'It should not allow moving a node as a child of its descendant.';
try {
  $root_2->getNode()->moveAsFirstChildOf($node);
  $t->fail($msg);
} catch (Exception $e) {
  $t->pass($msg);
}

$root_1->getNode()->moveAsFirstChildOf($node);
$t->ok(!$root_1->getNode()->isRoot(), 'It should not be the root anymore.');
$t->is($root_1->getParentId(), $node->getId(), 'It should have parent');

// Moving $node to root
$node->setName('new_root');
$node->getNode()->makeRoot();
$t->ok($node->getNode()->isValidNode(), 'Node should be valid');
$t->is($node->getParentId(), null, 'Node should have NULL as parent_id');
$t->is($node->getNode()->getParent(), null, 'Node should not have parent');
$t->is($node->getRootId(), $node->getId(), 'Should use id as root_id');

// Making $root_1 a root again using barbarian method
$root_1->setParent(null);
$root_1->save();

$t->ok($root_1->getNode()->isRoot(), 'It should be root');
$t->is($root_1->getRootId(), $root_1->getId(), 'Its root_id should be equal to its id');
$t->is($root_1->getParent(), null, 'It should not have parent');
