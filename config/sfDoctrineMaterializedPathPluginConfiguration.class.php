<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfDoctrineMaterializedPathPlugin configuration.
 * 
 * @package    sfDoctrineMaterializedPathPlugin
 * @subpackage config
 * @author     Artem Chistyakov <chistyakov.artem@gmail.com>
 */
class sfDoctrineMaterializedPathPluginConfiguration extends sfPluginConfiguration
{
  /**
   * @see sfPluginConfiguration
   */
  public function initialize()
  {
    /*if (
      sfConfig::get(
        'app_sf_doctrine_materialized_path_plugin_routes_register', 
        true
      ) && 
      in_array(
        'sfMaterializedPathTreeManager', 
        sfConfig::get('sf_enabled_modules', array())
      )
    )
    {
      $this->dispatcher->connect(
        'routing.load_configuration', 
        array('sfGuardRouting', 'listenToRoutingLoadConfigurationEvent')
      );
      $this->dispatcher->connect('routing.load_configuration', array('sfDoctrineMaterializedPathPluginRouting', 'addRouteForTreeManager'));
    }*/
  }
}
