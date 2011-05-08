<?php

class BasesfMaterializedPathTreeManagerComponents extends sfComponents
{
  public function executeManager(){
		
		if ($this->records = $this->executeControl())
    {
      $this->hasManyRoots = $this->modelHasManyRoots();
			$request = $this->getRequest();			
  		$this->records = $this->getTree($this->model, $request->getParameter('root'));
		}
	}

	/*
	 * return exception if Model is not defined as Tree
	*/
	private function executeControl() {
		if ( ! Doctrine_Core::getTable($this->model)->isTree() )
    {
			throw new Exception('Model "'.$this->model.'" is not a tree');
			return false;
		}
    
		return true;		
	}
	
	
	/*
	* Returns the roots
	*/
	private function getRoots($model){
    $tree = Doctrine_Core::getTable($model)->getTree();
    return $tree->fetchRoots();
  }
	
  private function getTree($model, $rootId = null){
    $tree = Doctrine_Core::getTable($model)->getTree();
    $options = array();
    if($rootId !== null)
    {
      $options['root_id'] = $rootId;
    }
    return $tree->fetchTree($options);
  }
	
	private function modelHasManyRoots(){
    return false;
	}
}
