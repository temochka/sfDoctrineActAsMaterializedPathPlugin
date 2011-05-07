<?php

/*
 * This file is part of the sfDoctrineMaterializedPath package.
 * © Artem Chistyakov <chistyakov.artem@gmail.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

$_test_dir = realpath(__DIR__.'/..');
$_project_dir = realpath(__DIR__.'/../../../..');
$_data_dir = realpath(__DIR__.'/../../data');

// configuration
require_once $_project_dir.'/config/ProjectConfiguration.class.php';
require_once __DIR__.'/fakeAppConfiguration.class.php';
$configuration = ProjectConfiguration::hasActive() ? ProjectConfiguration::getActive() : new ProjectConfiguration($_project_dir);

// autoloader
$autoload = sfSimpleAutoload::getInstance(sfConfig::get('sf_cache_dir').'/project_autoload.cache');
$autoload->loadConfiguration(sfFinder::type('file')->name('autoload.yml')->in(array(
  sfConfig::get('sf_symfony_lib_dir').'/config/config',
  sfConfig::get('sf_config_dir'),
)));
$autoload->register();

// lime initialization
include $configuration->getSymfonyLibDir().'/vendor/lime/lime.php';

$context = sfContext::createInstance(new fakeAppConfiguration('test', true), 'fake');