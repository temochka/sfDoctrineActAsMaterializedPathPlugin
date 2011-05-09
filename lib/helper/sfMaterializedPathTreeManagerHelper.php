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
    '/sfDoctrineMaterializedPathPlugin/jsTree/themes/default/style.css'
  );
  sfContext::getInstance()->getResponse()->addStylesheet(
    '/sfDoctrineMaterializedPathPlugin/css/screen.css'
  );
  
  sfContext::getInstance()->getResponse()->addJavascript(
    '/sfDoctrineMaterializedPathPlugin/jsTree/jquery.cookie.js'
  );
  sfContext::getInstance()->getResponse()->addJavascript(
    '/sfDoctrineMaterializedPathPlugin/jsTree/jquery.hotkeys.js'
  );
  sfContext::getInstance()->getResponse()->addJavascript(
    '/sfDoctrineMaterializedPathPlugin/jsTree/jquery.jstree.js'
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