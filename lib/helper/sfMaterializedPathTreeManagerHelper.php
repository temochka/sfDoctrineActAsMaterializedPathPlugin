<?php

/**
 *
 * @param string $model
 * @param string $field
 * @param int $root
 * @return string
 */
function get_tree_manager($model, $field, $root = 0) {
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