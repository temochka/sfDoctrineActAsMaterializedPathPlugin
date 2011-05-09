<?php

/**
 * BasesfMaterializedPathTreeManagerComponents 
 * @author Artem Chistyakov <chistyakov.artem@gmail.com>
 * @property bool $hasManyRoots
 * @property string $model
 */
class BasesfMaterializedPathTreeManagerComponents extends sfComponents
{
  /**
   * Manager component
   */
  public function executeManager() {    
    if (!($table = Doctrine_Core::getTable($this->model)) || !$table->isTree()) {
			throw new InvalidArgumentException(
        'Model "'.$this->model.'" does not exist or isn\'t a tree'
      );
		}
    
    if ($this->hasManyRoots = $this->modelHasManyRoots()) {
      $this->roots = $this->getRoots();
    } else {
      $this->root = ($root = $table->getTree()->fetchRoot()) ? $root->getPrimaryKey() : null;
    }
    
    $request = $this->getRequest();    
	}
	
	/*
	 * Returns the roots
   * @return Doctrine_Collection
	 */
	private function getRoots()
  {
    $tree = Doctrine_Core::getTable($this->model)->getTree();
    return $tree->fetchRoots();
  }
	
  /**
   * Fetches the tree
   * @param int $root_id
   * @return Doctrine_Collection
   */
  private function getTree($root_id = null)
  {
    $tree = Doctrine_Core::getTable($model)->getTree();    
    $options = null !== $rootId ? array('root_id' => $root_id) : array();
    return $tree->fetchTree($options);
  }
	
  /**
   * Checks if model has many roots
   * @return bool
   */
	private function modelHasManyRoots(){
    return Doctrine_Core::getTable($this->model)->getTree()->hasManyRoots();
	}
}
