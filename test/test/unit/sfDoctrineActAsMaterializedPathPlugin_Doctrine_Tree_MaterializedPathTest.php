<?php
/**
 * Tests for single root model
 * @author Artem Chistyakov <chistyakov.artem@gmail.com>
 * @copyright © Artem Chistyakov, 2011
 */
require __DIR__.'/../bootstrap/unit.php';

$t = new lime_test();
$manager = new sfDatabaseManager($configuration);
sfMaterializedPath_TestNodeMultipleTable::getInstance()->createQuery()->delete();

dataLoad($_data_dir.'/test_fixtures');

$table = sfMaterializedPath_TestNodeMultipleTable::getInstance();


// *****************************************************************************
// Doctrine_Tree_MaterializedPath::fetchRoots
try {
  $roots = $table->getTree()->fetchRoots();
  $t->pass("Roots have successfully fetched.");
} catch (Exception $e) {
  $t->fail("Roots haven't be fetched.");
  throw $e;
}

$t->is($roots->count(), 2, "There are 2 roots in test fixtures.");
$t->ok($roots->getFirst()->getNode()->isRoot(), "Checking that root is root.");

// *****************************************************************************
// Doctrine_Tree_MaterializedPath::fetchChildrenOf
try {
  $children = $table->getTree()->fetchChildrenOf($roots->getFirst()->getPrimaryKey());
  $t->pass("Children have successfully fetched");
} catch (Exception $e) {
  $t->fail("Children haven't fetched.");
}

$t->ok($children instanceof Doctrine_Collection, "Children are Doctrine_Collection.");
$t->ok($children->count(), "Children count isn't zero.");
$t->is($children->getFirst()->getParentId(), $roots->getFirst()->getPrimaryKey(), "Checking first child's parent_id.");