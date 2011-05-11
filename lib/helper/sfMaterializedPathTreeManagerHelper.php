<?php

/**
 * Helper function sets up and returns manager component of 
 * sfMaterializedPathTreeManager module.
 * 
 * @author Artem Chistyakov <chistyakov.artem@gmail.com>
 * @param string $model Model name
 * @param string $field Field name to output as name
 * @param int $root
 * @return string
 */
function get_tree_manager($model, $field, $root = null) {
  sfContext::getInstance()->getResponse()->addStylesheet(
    '/sfDoctrineActAsMaterializedPathPlugin/jsTree/themes/default/style.css'
  );
  sfContext::getInstance()->getResponse()->addStylesheet(
    '/sfDoctrineActAsMaterializedPathPlugin/css/screen.css'
  );
  
  sfContext::getInstance()->getResponse()->addJavascript(
    '/sfDoctrineActAsMaterializedPathPlugin/jsTree/jquery.cookie.js'
  );
  sfContext::getInstance()->getResponse()->addJavascript(
    '/sfDoctrineActAsMaterializedPathPlugin/jsTree/jquery.hotkeys.js'
  );
  sfContext::getInstance()->getResponse()->addJavascript(
    '/sfDoctrineActAsMaterializedPathPlugin/jsTree/jquery.jstree.js'
  );

  return get_component(
    'sfMaterializedPathTreeManager', 
    'manager', array(
      'model' => $model, 
      'field' => $field, 
      'root' => $root
    )
  );
}