<?php

class BasesfMaterializedPathTreeManagerActions extends sfActions
{
  public function getTree($model, $rootId = null)
  {
    $tree = Doctrine_Core::getTable($model)->getTree();

    $options = array();
    if($rootId !== null)
    {
      $options['root_id'] = $rootId;
    }

    return $tree->fetchTree($options);
  }
  
  public function decorateNode(Doctrine_Record $model)
  {
    $is_leaf = $model->getNode()->isLeaf();
    $rel = $model->getLevel() == 0 ? 'drive' : ($is_leaf ? 'file' : 'folder');
    return array(
      'attr' => array(
        'id' => $model->getPrimaryKey(),
        'rel' => $rel
      ),
      'data' => (string)$model,
      'state' => $is_leaf ? 'leaf' : 'closed'
    );
  }

  public function executeGet_children()
  {
    $parent_id = $this->getRequestParameter('parent_id');
    $model = $this->getRequestParameter('model');
    
    /* @var $record DbItem */
    if ($parent_id != 0) {
      $nodes = Doctrine_Core::getTable($model)->getTree()->fetchChildrenOf($parent_id);
    } else {
      $record = Doctrine_Core::getTable($model)->getTree()->fetchRoot();
      $nodes = array($record);
    }
    $json = array();
    foreach ($nodes as $node) {
      $json[] = $this->decorateNode($node);
    }
    return $this->echoJSON(json_encode($json));
  }
  
  public function executeAdd_child()
  {
    $parent_id = $this->getRequestParameter('parent_id');
    $model = $this->getRequestParameter('model');
    
    try {
      $record = Doctrine_Core::getTable($model)->find($parent_id);
      $child = new $model;
      $child->set(
        $this->getRequestParameter('field'),
        $this->getRequestParameter('value')
      );
      $record->getNode()->addChild($child);
      $child->save();

      $this->json = json_encode(array('status' => 1, 'id' => $child->getPrimaryKey()));
    } catch (Exception $e) {
      $this->json = json_encode(array('status' => 0));
    }
    
    return $this->echoJSON($this->json);
  }
  
  public function executeAdd_root(sfWebRequest $request)
  {
    $model = $this->getRequestParameter('model');
    $data = $this->getRequestParameter(strtolower($model));
    $tree = $this->getTree($model);
    
    $root = new $model;
    $root->synchronizeWithArray($data);
		$root->save();
    
    $root->getNode()->makeRoot();    
    $this->redirect($request->getReferer());
  }

  public function executeEdit_field()
  {
    $id = $this->getRequestParameter('id');
    $model = $this->getRequestParameter('model');
    $field = $this->getRequestParameter('field');
    $value = $this->getRequestParameter('value');

    $record = Doctrine_Core::getTable($model)->find($id);
    $record->set($field, $value);
    $record->save();

    return $this->echoJSON(json_encode(array('status' => 1)));
  }

  public function executeDelete()
  {
    $id = $this->getRequestParameter('id');
    $model = $this->getRequestParameter('model');
    
    $record = Doctrine_Core::getTable($model)->find($id);
    $record->delete();
    
    return $this->echoJSON(array('status' => 1));
  }

  public function executeMove(sfWebRequest $request)
  {
    //var_dump($request->getRequestParameters());die;
    $id = $this->getRequestParameter('id');
    $target_id = $this->getRequestParameter('target_id');
    $model = $this->getRequestParameter('model');
    
    $record = Doctrine_Core::getTable($model)->find($id);
    $dest = Doctrine_Core::getTable($model)->find($target_id);
    
    $dest->getNode()->addChild($record);
    $record->save();
    
    return $this->echoJSON(json_encode(array('status' => 1, 'id' => $record->getPrimaryKey())));
  }
  
  function echoJSON($content)
  {
    $this->getResponse()->setHttpHeader('Content-type', 'application/json');
    $this->getResponse()->setContent($content);
    return sfView::NONE;
  }
}
