<?php
/**
 * Test for data-load, data-dump features
 * @author Artem Chistyakov <chistyakov.artem@gmail.com>
 * @copyright © Artem Chistyakov, 2011
 */
require __DIR__.'/../bootstrap/unit.php';
new sfDatabaseManager($configuration);
$t = new lime_test(5);

// 1
try {
  dataLoad($_data_dir.'/test_fixtures/fixtures.yml');
  $t->pass('Initial data has been successfully loaded.');
} catch (Exception $e) {
  $t->fail('Can not load initial data.');
}

// 2
try {
  $table = sfMaterializedPath_TestNodeSingleTable::getInstance();
  /* @var $root sfMaterializedPath_TestNodeSingle */
  $root = $table->getTree()->fetchRoot();

  // Adding new node on the level 1
  $node = new sfMaterializedPath_TestNodeSingle();
  $node->setName('TEST');
  $node->getNode()->insertAsLastChildOf($root);
  $t->pass('Inserting a test node.');
} catch (Exception $e) {
  $t->fail('Error while inserting a test node.');
}

// 3
$temp_file = sys_get_temp_dir() . DIRECTORY_SEPARATOR . getmypid().'.yml';
try {
  dataDump($temp_file);
  $t->pass('Data has been successfully dumped.');
} catch (Exception $e) {
  $t->fail('Can not dump data.');
}

// 4
try {
  dataLoad($temp_file);
  $t->pass('Dumped data has been successfully loaded.');
} catch (Exception $e) {
  $t->fail('Can not load dumped data: '.$e->getMessage());
}

// 5
$node = sfMaterializedPath_TestNodeSingleTable::getInstance()->findOneByName('TEST');
$t->ok($node->getNode()->isValidNode(), 'Tree is not corrupted');