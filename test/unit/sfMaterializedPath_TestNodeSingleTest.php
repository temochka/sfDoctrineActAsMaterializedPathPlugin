<?php
/**
 * Test for multiple roots model
 * @author Artem Chistyakov <chistyakov.artem@gmail.com>
 * @copyright © Artem Chistyakov, 2011
 */
require __DIR__.'/../bootstrap/unit.php';

new sfDatabaseManager($configuration);

sfMaterializedPath_TestNodeSingleTable::getInstance()->createQuery()->delete();

Doctrine_Core::loadData($_data_dir.'/test_fixtures');

$table = sfMaterializedPath_TestNodeSingleTable::getInstance();

/* @var $root sfMaterializedPath_TestNodeSingle */
$root = $table->getTree()->fetchRoot();

$t = new lime_test();
$t->is($root->getLevel(), 0, 'Root node level must be equal to zero.');
$t->is($root->getNode()->getLevel(), 0, 'Node level must be equal to record level.');

$children = $root->getNode()->getChildren();
$t->is($children->count(), 2, 'There are two children of the root node in the fixtures.');

/* @var $child sfMaterializedPath_TestNodeSingle */
$child = $children->getFirst();
$t->is($child->getParentId(), $root->getPrimaryKey(), 'Parent id of the children must be equal to root node\'s id');
$t->ok($child->getNode()->isValidNode(), 'Valid node is a node having level equal to path\'s length.');

$t->ok($child->getNode()->hasNextSibling(), '$child node shoud has next sibling.');
$nextSibling = $child->getNode()->getNextSibling();
$t->ok($nextSibling && $nextSibling->exists(), 'Next sibling of first children exists.');
$t->is($nextSibling->getName(), '0_2', 'Next sibling should has name "0_2"');
$t->ok($nextSibling->getNode()->hasPrevSibling(), 'Node should have previous sibling.');
$t->is($nextSibling->getNode()->getPrevSibling(), $child, 'Next node\'s previous sibling is the node');

$branch = $table->getTree()->fetchBranch($child->getPrimaryKey());
$t->is($branch->count(), 4, 'There are three children of the first root\'s child in the fixtures');

$branch = $table->getTree()->fetchBranch($child->getPrimaryKey(), array('depth' => 1));
$t->is($branch->count(), 2, 'There are one children on the first root\'s child\'s next level');

// Adding new node on the level 1
$node = new sfMaterializedPath_TestNodeSingle();
$node->setName('new_node');
$node->setParent($root);
$node->save();

// Testing
$t->is($node->getLevel(), 1, 'New node\'s level is 1.');
$t->is($node->getChildren()->count(), 0, 'New node hasn\'t children.');
$t->is($node->getParentId(), $root->getPrimaryKey(), 'Node\'s parent is the root node.');

// Moving root's child as node's child
$child->getNode()->moveAsFirstChildOf($node);

// Testing
$t->is($child->getParentId(), $node->getPrimaryKey(), 'New $child parent is $node.');
$descendants = $child->getNode()->getDescendants();
$t->is($descendants->count(), 3, 'Node still has 3 child nodes.');

$is_valid = true;
foreach ($descendants as $descendant) {
  $is_valid = $descendant->getNode()->isValidNode() && $descendant->getNode()->isDescendantOf($root);  
}

$t->ok($is_valid, 'All node descendants are valid nodes and they\'re all root node\'s descendants.');

// Deletion
$descendant_id = $descendants->getFirst()->getPrimaryKey();
$node->delete();

$t->ok(!($table->find($descendant_id) instanceof sfDoctrineRecord), 'Children were deleted with theirs parent.');