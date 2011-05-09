<?php
/**
 * BasesfMaterializedPathTreeManagerActions
 * Actions of sfMaterializedPathTreeManager
 * 
 * @author Artem Chistyakov <chistyakov.artem@gmail.com>
 */
class BasesfMaterializedPathTreeManagerActions extends sfActions
{
  /**
   * Gets node's children
   * @return int
   */
  public function executeGet_children(sfWebRequest $request)
  {
    $this->forward404Unless(
      $request->isXmlHttpRequest() && $request->hasParameter('model') &&
      $request->hasParameter('parent_id')
    );
    $parent_id = $request->getParameter('parent_id');
    $model = $request->getParameter('model');
  
    if (0 != $parent_id) {
      $nodes = Doctrine_Core::getTable($model)->getTree()
        ->fetchChildrenOf($parent_id);
    } elseif ($request->hasParameter('root_id')) {
      $root_id = $request->getParameter('root_id');
      $record = Doctrine_Core::getTable($model)->getTree()->fetchRoot($root_id);
      $nodes = $record ? array($record) : array();
    }
    $answer = array();
    foreach ($nodes as $node) {
      $answer[] = $this->decorateNode($node);
    }
    return $this->echoJSON($answer);
  }
  
  /**
   * Adds a new child
   * @return type 
   */
  public function executeAdd_child(sfWebRequest $request)
  {
    $this->forward404Unless($request->isXmlHttpRequest());
    $parent_id = $request->getParameter('parent_id');
    $model = $request->getParameter('model');
    
    try {
      $record = Doctrine_Core::getTable($model)->find($parent_id);
      $child = new $model;
      $child->set(
        $request->getParameter('field'),
        $request->getParameter('value')
      );
      $child->getNode()->insertAsLastChildOf($record);

      $answer = $this->createJsTreeAnswer(
        true, 
        array('id' => $child->getPrimaryKey())
      );
    } catch (Exception $e) {
      $this->createJsTreeAnswer(false);
    }
    
    return $this->echoJSON($answer);
  }
  
  /**
   * Adds a new root to tree
   * @param sfWebRequest $request 
   */
  public function executeAdd_root(sfWebRequest $request)
  {
    $model = $request->getParameter('model');
    $data = $request->getParameter(strtolower($model));
    $tree = $this->getTree($model);
    
    try {
      $root = new $model;
      $root->synchronizeWithArray($data);
      $root->save();

      $root->getNode()->makeRoot();
      $this->getUser()->setFlash(
        'notice', 
        sprintf('The root %s has successfully added.', (string)$root)
      );
    } catch (Exception $e) {
      $this->getUser()->setFlash(
        'error', 
        sprintf('Error while adding new root.', (string)$root)
      );
    }
    $this->redirect($request->getReferer());
  }

  /**
   * Edits the node's name field
   * @return int
   */
  public function executeEdit_field(sfWebRequest $request)
  {
    $this->forward404Unless($request->isXmlHttpRequest());
    $id = $request->getParameter('id');
    $model = $request->getParameter('model');
    $field = $request->getParameter('field');
    $value = $request->getParameter('value');

    try {
      $record = Doctrine_Core::getTable($model)->find($id);
      $record->set($field, $value);
      $record->save();
      $answer = $this->createJsTreeAnswer(true);
    } catch (Exception $e) {
      $answer = $this->createJsTreeAnswer(false);
    }

    return $this->echoJSON($answer);
  }

  /**
   * Deletes the node and its descendants
   * @return int
   */
  public function executeDelete(sfWebRequest $request)
  {
    $this->forward404Unless($request->isXmlHttpRequest());
    $id = $request->getParameter('id');
    $model = $request->getParameter('model');
    
    try {
      $record = Doctrine_Core::getTable($model)->find($id);      
      if ($record) $record->delete(); else throw new Exception;
      $answer = $this->createJsTreeAnswer(true);
    } catch (Exception $e) {
      $answer = $this->createJsTreeAnswer(false);
    }
    
    return $this->echoJSON($answer);
  }

  /**
   * Moves the node with given id as target_id's child
   * @param sfWebRequest $request
   * @return int
   */
  public function executeMove(sfWebRequest $request)
  {
    $this->forward404Unless($request->isXmlHttpRequest());
    $id = $request->getParameter('id');
    $target_id = $request->getParameter('target_id');
    $model = $request->getParameter('model');
    
    try {
      $record = Doctrine_Core::getTable($model)->find($id);
      $dest = Doctrine_Core::getTable($model)->find($target_id);      
      $record->getNode()->moveAsLastChildOf($dest);
      
      $answer = $this->createJsTreeAnswer(
        true, 
        array('id' => $record->getPrimaryKey())
      );
    } catch (Exception $e) {      
      $answer = $this->createJsTreeAnswer(false);
    }
    
    return $this->echoJSON($answer);
  }
  
  /**
   * Fetches the model tree with given $root_id
   * @param string $model
   * @param int $root_id
   * @return Doctrine_Collection
   */
  protected function getTree($model, $root_id = null)
  {
    $tree = Doctrine_Core::getTable($model)->getTree();
    $options = null !== $root_id ? array('root_id' => $root_id) : array();    
    return $tree->fetchTree($options);
  }
  
  /**
   * Decorates node for jsTree
   * @param Doctrine_Record $model
   * @return array
   */
  protected function decorateNode(Doctrine_Record $model)
  {
    $is_leaf = $model->getNode()->isLeaf();
    $rel = !$model->getNode()->getLevel() ? 'root' : 'node';
    return array(
      'attr' => array(
        'id' => $model->getPrimaryKey(),
        'rel' => $rel
      ),
      'data' => (string)$model,
      'state' => $is_leaf ? 'leaf' : 'closed'
    );
  }
  
  /**
   * Creates and returns well-formed jsTree answer
   * @param bool $status
   * @param array $data
   * @return array 
   */
  protected function createJsTreeAnswer($status=true, $data=array())
  {
    return array_merge(
      array('status' => $status),
      $data
    );
  }
  
  /**
   * Sets the template for JSON answer and encodes data to JSON
   * @param mixed $data
   * @return int
   */
  protected function echoJSON($data)
  {
    $this->getResponse()->setHttpHeader('Content-type', 'application/json');
    $this->json = json_encode($data);
    $this->setTemplate('json');
    return sfView::SUCCESS;
  }
}
