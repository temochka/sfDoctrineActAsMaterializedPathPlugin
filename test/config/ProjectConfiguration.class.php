<?php

require_once '/Applications/XAMPP/xamppfiles/lib/php/pear/symfony/autoload/sfCoreAutoload.class.php';
sfCoreAutoload::register();

class ProjectConfiguration extends sfProjectConfiguration
{
  public function setup()
  {
    $this->setPluginPath('sfDoctrineActAsMaterializedPathPlugin', __DIR__.'/../../');

    $this->enablePlugins(array(
      'sfDoctrineActAsMaterializedPathPlugin',
      'sfDoctrinePlugin'
    ));
  }
}
