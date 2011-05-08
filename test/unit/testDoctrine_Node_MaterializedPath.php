<?php
/**
 * Tests for single root model
 * @author Artem Chistyakov <chistyakov.artem@gmail.com>
 * @copyright © Artem Chistyakov, 2011
 */
require __DIR__.'/../bootstrap/unit.php';

$t = new lime_test();

$root = new sfMaterializedPath_TestNodeMultiple();
$root->setName('test');

// *****************************************************************************
// Doctrine_Node_MaterializedPath::getPathSeparator
$t->ok(
  null !== $root->getNode()->getPathSeparator() && 
  '' != $root->getNode()->getPathSeparator()
);

$t->is(
  $root->getNode()->getPathSeparator(), 
  $root->getTable()->getTree()->getAttribute('pathSeparator'), 
  'Checking the path separator'
);

// *****************************************************************************
// Doctrine_Node_MaterializedPath::setLevel
// Doctrine_Node_MaterializedPath::getLevel
$t->is($root->getNode()->getLevel(), null, 'New node\'s level should be null.');
$root->getNode()->setLevel(5);
$t->is($root->getNode()->getLevel(), 5, 'Now the node\'s level is 5');
$root->getNode()->setLevel(null);

// *****************************************************************************
// Doctrine_Node_MaterializedPath::getRootValue
// Doctrine_Node_MaterializedPath::setRootValue
$t->is($root->getNode()->getRootValue(), null, 'New node\'s root value should be null.');
$root->getNode()->setRootValue(2);
$t->is($root->getNode()->getRootValue(), 2, 'Now the node\'s root value is 2');
$root->getNode()->setRootValue(null);

// *****************************************************************************
// Doctrine_Node_MaterializedPath::isValidNode

$t->ok(!$root->getNode()->isValidNode(), 'Unfixed new node shouldn\'t be valid');
$root->save();
$t->ok(!$root->getNode()->isValidNode(), 'Even saved record isn\'t valid.');

// *****************************************************************************
// Doctrine_Node_MaterializedPath::makeRoot
try {
  $root->getNode()->makeRoot();
  $t->pass('Test object is now the root node.');
} catch (Exception $e) {
  $t->fail('Test object should became the root node.');
}

$t->ok($root->getNode()->isValidNode(), 'The node should be valid');
$t->is($root->getNode()->getLevel(), 0, 'Level of the root node shoud be 0.');
$t->is($root->getNode()->getParentId(), null, 'There isn\'t parent of the root node.');
$t->is($root->getNode()->getPath(), '', 'Path\'s prefix should be empty for the root node.');
$t->is(
  $root->getNode()->getPath($root->getNode()->getPathSeparator(), true), 
  $root->getPrimaryKey(), 'Full root node\'s path consists from its ID.'
);

// *****************************************************************************
// Doctrine_Node_MaterializedPath::addChild
$child = new sfMaterializedPath_TestNodeMultiple();
$child->setName('child');

try {
  $root->getNode()->addChild($child);
  $t->pass('New node is now the first root\'s child ');
} catch (Exception $e) {
  $t->fail('The child node should be successfully saved.');
}

$t->ok($child->getNode()->isValidNode(), 'The node should be valid');
$t->is($child->getNode()->getLevel(), 1, 'Level of the child node should be 1.');
$t->is($child->getParentId(), $root->getPrimaryKey(), 'Comparing the parent\'s ID and parent_id');
$t->is($child->getParent(), $root, 'Comparing the parent and returned by getParent() value');
$t->is($child->getPath(), $root->getPrimaryKey().$root->getNode()->getPathSeparator().$child->getPrimaryKey(), 'Checking path\'s value');

// *****************************************************************************
// Doctrine_Node_MaterializedPath::isDescendantOf
$t->ok($child->getNode()->isDescendantOf($root), 'A child is a descendant too.');
$t->ok(!$root->getNode()->isDescendantOf($child), "A parent isn't a descendant.");

// *****************************************************************************
// Doctrine_Node_MaterializedPath::isDescendantOfOrEqualTo
$t->ok($child->getNode()->isDescendantOfOrEqualTo($root), 'A child is a descendant too.');
$t->ok(!$root->getNode()->isDescendantOfOrEqualTo($child), "A parent isn't a descendant.");
$t->ok($child->getNode()->isDescendantOfOrEqualTo($child), 'A child is equal to itself.');

// *****************************************************************************
// Doctrine_Node_MaterializedPath::isEqualTo
$t->ok(!$child->getNode()->isEqualTo($root), "Nodes aren't equal.");
$t->ok($child->getNode()->isEqualTo($child), "Child is equal to itself.");

// *****************************************************************************
// Doctrine_Node_MaterializedPath::getPathArray
$t->is(
  array($root->getPrimaryKey()),
  $child->getNode()->getPathArray(), 
  "Path prefix converted in array consist from one element: root_id"
);

$t->is(
  array($root->getPrimaryKey(), $child->getPrimaryKey()),
  $child->getNode()->getPathArray(true), 
  "Path converted in array consist from two elements: root_id, child_id"
);

// *****************************************************************************
// Doctrine_Node_MaterializedPath::insertAsNextSiblingOf
// NOTE: sfDoctrineMaterializedPathPlugin doesn't really handle horizontal 
// orientation of trees. Horizontal orientation defined by sort order stored in
// special configuration value (sortBy option).
// So the insertAsNextSibling and insertAsPrevSibling are equal. Order will be
// dinamically calculated in next uses.
$sibling = new sfMaterializedPath_TestNodeMultiple();

try {
  $child->getNode()->insertAsNextSiblingOf($root);
  $t->fail("Fixed nodes can't be inserted.");
} catch (Exception $e) {
  $t->pass("Fixed nodes can't be inserted.");
}

// The name makes $sibling next
$sibling->setName('child sibling');
$sibling->getNode()->insertAsNextSiblingOf($child);

$t->ok($sibling->getNode()->isValidNode(), "The sibling node should be valid.");
$t->is($sibling->getNode()->getLevel(), $child->getNode()->getLevel(), "Levels of siblings should be equal.");
$t->is($sibling->getNode()->getParentId(), $child->getNode()->getParentId(), "Parents of siblings should be equal.");
$t->is($sibling->getNode()->getPath(), $child->getNode()->getPath(), "Path prefixes of siblings should be equal.");

// *****************************************************************************
// Doctrine_Node_MaterializedPath::hasPrevSibling
$t->ok($sibling->getNode()->hasPrevSibling(), "The previous sibling of \$sibling is \$child");
$t->ok(!$child->getNode()->hasPrevSibling(), "\$child hasn't previous sibling");

// *****************************************************************************
// Doctrine_Node_MaterializedPath::hasNextSibling
$t->ok($child->getNode()->hasNextSibling(), "\$child has next sibling (\$sibling).");
$t->ok(!$sibling->getNode()->hasNextSibling(), "\$sibling hasn't next sibling.");

// *****************************************************************************
// Doctrine_Node_MaterializedPath::isLeaf

// Saying to Doctrine: "I've change your children, refresh!"
$root->refresh(1);

$t->ok($child->getNode()->isLeaf(), '$child is a leaf.');
$t->ok(!$root->getNode()->isLeaf(), 'Root isn\'t a leaf.');

// *****************************************************************************
// Doctrine_Node_MaterializedPath::isRoot
$t->ok($root->getNode()->isRoot(), '$root is a root.');
$t->ok(!$child->getNode()->isRoot(), '$child isn\'t a root');

// *****************************************************************************
// Doctrine_Node_MaterializedPath::hasParent
$t->ok(!$root->getNode()->hasParent(), "The root hasn't parent");
$t->ok($child->getNode()->hasParent(), "\$child has parent");

// *****************************************************************************
// Doctrine_Node_MaterializedPath::delete
try {
  $sibling->delete();
  $root->delete();
  $t->pass('Deleting created nodes.');
} catch (Exception $e) {
  $t->fail('Deleting created nodes.');
}

$t->ok(
  !sfMaterializedPath_TestNodeMultipleTable::getInstance()->find($child->getPrimaryKey()), 
  "The child should not exist in database after parent deletion."
);

