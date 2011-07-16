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
class sfDoctrineActAsMaterializedPathPluginConfiguration extends sfPluginConfiguration
{
  /**
   * @see sfPluginConfiguration
   */
  public function initialize()
  {
    $this->dispatcher->connect('command.pre_command', array($this, 'listenToCommandPreCommandEvent'));
    $this->dispatcher->connect('command.post_command', array($this, 'listenToCommandPostCommandEvent'));
  }

  public function listenToCommandPreCommandEvent(sfEvent $event)
  {
    $invoker = $event->getSubject();
    if ($invoker instanceof sfDoctrineDataLoadTask)
    {
      Doctrine_MaterializedPath_Listener::isDisabled(true);
    }
  }

  public function listenToCommandPostCommandEvent(sfEvent $event)
  {
    $invoker = $event->getSubject();
    if ($invoker instanceof sfDoctrineDataLoadTask)
    {
      Doctrine_MaterializedPath_Listener::isDisabled(false);
    }
  }
}
