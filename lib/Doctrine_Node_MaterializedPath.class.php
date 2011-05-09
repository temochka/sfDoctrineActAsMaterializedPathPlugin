<?php
/*
 * Doctrine_Node_MaterializedPath.class.php
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the LGPL.
 */

/**
 * Doctrine_Node_MaterializedPath
 * @package doctrine
 * @subpackage sfDoctrineMaterializedPlugin
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @author Artem Chistyakov <chistyakov.artem@gmail.com>
 * 
 * @property sfDoctrineRecord $record
 * @property Doctrine_Tree_MaterializedPath $_tree
 */
class Doctrine_Node_MaterializedPath extends Doctrine_Node implements Doctrine_Node_Interface {
    
  /**
   * Method sets $record parent to this node and takes care about path updation
   * @param DbItem $record 
   */
  public function addChild(Doctrine_Record $record) {
    if (!$this->isValidNode()) {
      throw new Doctrine_Node_Exception("Trying to add child for unexisting node.");
    }
    if ($record->getNode()->isValidNode() && $this->isDescendantOf($record)) {
      throw new Doctrine_Node_Exception("Trying to add parent node as child.");
    }
    
    $old_root_id = 
      $this->_tree->hasManyRoots() && 
      $this->getRootValue() != $record->getNode()->getRootValue() ? 
        $record->getNode()->getRootValue() : null;
    
    $record->getNode()->setRootValue($this->getRootValue());    
    $record->setParent($this->record);

    $conn = $this->record->getTable()->getConnection();
    $conn->beginTransaction();
    try {
      $record->getNode()->setPath(
        $this->getPath($this->getPathSeparator(), true).
        ($record->getNode()->isValidNode() ? 
        $this->getPathSeparator().
        $record->getPrimaryKey() : ''),
        $old_root_id
      );
      $record->save();
    } catch (Exception $e) {
      $conn->rollback();
      throw $e;
    }
    $conn->commit();
  }

  /**
   * Method updates node's self path and children's pathes
   * Warning! Method doesn't use transaction.
   * @param string $new_path 
   * @param int $old_root_id
   * @param bool $and_save
   */
  public function setPath($new_path, $old_root_id = null, $and_save = false) {
    $old_path = $this->getPath();
    $full_old_path = $this->getPath($this->getPathSeparator(), true);
    $d_level = (
      count(explode($this->getPathSeparator(), $new_path)) -
      count(explode($this->getPathSeparator(), $full_old_path))
    );
    
    if (!$this->record->isNew() && $this->hasChildren()) {      
      /* @var $q Doctrine_Query */
      $q = $this->record->getTable()->createQuery()->update()
        ->set('path', 'CONCAT(?, ?, SUBSTRING(path, ?, (LENGTH(path)) - ?))',
          array(
            $new_path,
            $this->getPathSeparator(),
            strlen($full_old_path)+ (!strlen($full_old_path) ? 1 : 2),
            strlen($full_old_path)
          )
        )
        ->set('level', '? + level', $d_level)
        ->where(
          'path LIKE ?', 
          array($full_old_path.$this->getPathSeparator().'%')
        );
      if ($this->_tree->getAttribute('hasManyRoots')) {
        if (null === $old_root_id) {
          $q = $this->_tree->getQueryWithRootId($this->getRootValue(), $q);         
        } else {
          $this->_tree->getQueryWithRootId($old_root_id, $q)
            ->set(
              $this->_tree->getAttribute('rootColumnName'), 
              $this->getRootValue()
            );
        }
      }
      $q->execute();
    }
    $this->record->setPath($new_path);
    if ($and_save) $this->record->save();
  }
  
  /**
   * Function returns path string with custom separator
   * @param string $separator
   * @param bool $includeNode
   * @return string
   */
  public function getPath($separator = Doctrine_Tree_MaterializedPath::PATH_SEPARATOR, $includeNode = false) {
    $path = $this->getPathArray($includeNode);
    return implode($separator, $path);
  }
  
  /**
   * Function returns path as array
   * @param bool $includeNode
   * @return array
   */
  public function getPathArray($includeNode = false)
  {
    $path = explode($this->getPathSeparator(), $this->record->getPath());
    return array_slice($path, 0, count($path)-!(int)$includeNode);
  }
  
  /**
   * Function fixes the materialized path string
   * Returns true when changes were
   * @return bool
   */
  public function fixPath()
  {
    if ($this->record->isNew()) return false;    
    
    $path_string = $this->record->getPath();    
    
    if ('' === $path_string || null === $path_string) {      
      if ($this->hasParent())
        $this->record->setPath(
          $this->getParent()->getPath().
          $this->getPathSeparator().
          $this->record->getPrimaryKey()
        );
      else
        $this->record->setPath($this->record->getPrimaryKey());
    } else {
      $path = $this->record->getNode()->getPathArray(true);      
      if (array_pop($path) != $this->record->getPrimaryKey()) {
        $this->record->setPath(
          $this->record->getPath().$this->getPathSeparator().$this->record->getPrimaryKey()
        );
      } else return false;
    }
    
    return true;
  }
  
  /**
   * Works like fixPath() but saves record on changes
   */
  public function fixPathAndSave()
  {
    if ($this->fixPath()) {
      $this->fixLevel();
      $this->record->save();
    }
  }
  
  /**
   * Fixes the node's level.
   * Returns true when changes have maden.
   * @return bool
   */
  public function fixLevel()
  {
    if (null === $this->getLevel()) {            
      if (null === $this->getParentId()) {        
        $this->record->setLevel(0);
      } else {
        $this->record->setLevel($this->getParent()->getLevel() + 1);
      }
    } else {
      if (($c = count($this->getPathArray())) != $this->getLevel()) {
        $this->setLevel(null);
      } else return false;
    }
    return true;
  }
  
  /**
   * Function returns level
   * @return int
   */
  public function getLevel() {
    return $this->record->getLevel();
  }  
  
  public function setLevel($level) {
    $this->record->setLevel($level);
  }
  
  /**
   * Checks if the node is descendant of $subj
   * @param Doctrine_Record $subj
   * @return bool
   */
  public function isDescendantOf(Doctrine_Record $subj) {
    return in_array($subj->getPrimaryKey(), $this->getPathArray());
  }
  
  /**
   * Gets node's parent ID
   * @return int
   */
  public function getParentId() {
    return $this->record->getParentId();
  }
  
  /**
   * Checks if node is valid
   * Valid node is a node that exists and has valid path and level
   * @return bool
   */
  public function isValidNode()
  {
    return 
      $this->record->exists() && 
      null !== $this->record->getPath() &&
      $this->getLevel() == count($this->getPathArray()) &&
      $this->hasValidRootValue();
  }
  
  /**
   * Gets node's children
   * @return Doctrine_Collection
   */
  public function getChildren() {    
    return $this->record->getChildren();
  }
  
  /**
   * Gets node's ancestors
   * @return Doctrine_Collection
   */
  public function getAncestors() {
    return $this->getAncestorsQuery()->execute();
  }
  
  /**
   * Gets node's descendants
   * @return Doctrine_Collection
   */
  public function getDescendants() {
    return $this->getDescendantsQuery()->execute();
  }
  
  /**
   * Gets the first node's child
   * @return Doctrine_Node
   */
  public function getFirstChild() {
    return $this->record->getChildren()->getFirst();
  }

  /**
   * Gets the last node's child
   * @return Doctrine_Node
   */
  public function getLastChild() {
    return $this->record->getChildren()->getLast();
  }  
  
  /**
   * Gets next sibling for trees with horizontal orientation
   * @return Doctrine_Node
   */
  public function getNextSibling() {    
    return $this->getNextSiblingsQuery()->fetchOne();    
  }
  
  /**
   * Prepares query for node's next siblings fetching
   * @return Doctrine_Query
   */
  public function getNextSiblingsQuery() {
    if (($field = $this->_tree->getAttribute('sortBy')) === null) {
      $this->unsupportedMethod();
    }
    $rel = $this->_tree->getAttribute('sortOrder') == 'ASC' ? '>' : '<';
    return $this->getSiblingsQuery()
      ->andWhere("$field $rel ?", array($this->record->get($field)));
  }
  
  /**
   * Prepares query for node's previous siblings fetching
   * @return type 
   */
  public function getPrevSiblingsQuery() {
    if (($field = $this->_tree->getAttribute('sortBy')) === null) {
      $this->unsupportedMethod();
    }
    $rel = $this->_tree->getAttribute('sortOrder') == 'ASC' ? '<' : '>';
    return $this->getSiblingsQuery()
      ->andWhere("$field $rel ?", array($this->record->get($field)));
  }
  
  /**
   * Prepares query for node's siblings fetching
   * @return type 
   */
  public function getSiblingsQuery() {
    if (!$this->isValidNode()) {
      throw new Doctrine_Node_Exception('Can not run method on unexisting node.');
    }
    return $this->_tree->getQuery()
      ->andWhere("parent_id=?", array(
        $this->getParentId(),        
      ));
  }
  
  /**
   * Gets the number of node's children
   * @return int
   */
  public function getNumberChildren() {
    return $this->getChildren()->count();
  }
  
  /**
   * Gets the Doctrine_Query for fetching node's ancestors
   * @return Doctrine_Query
   */
  public function getAncestorsQuery($includeNode=false)
  {
    return $this->_tree->getQueryWithRootId($this->getRootValue())
      ->whereIn('id', $this->getPathArray($includeNode));
  }

  /**
   * Gets the Doctrine_Query for fetching node's descendants
   * @return Doctrine_Query
   */
  public function getDescendantsQuery($depth = null, $includeNode=false)
  {
    $q = $this->_tree->getQueryWithRootId($this->getRootValue())
      ->addWhere('path LIKE ?',
        $this->getPath(
          $this->getPathSeparator(), 
          true
        ).$this->getPathSeparator().'%'
      );
    
    if (null !== $depth) 
      $q->andWhere('level BETWEEN ? AND ?', array(
        $this->getLevel(), 
        $this->getLevel()+$depth
      ));
    
    return $includeNode ? $q->orWhere('id=?', $this->record->getPrimaryKey()) : $q;
  }
  
  /**
   * Gets the number of descendants
   * @return int
   */
  public function getNumberDescendants() {
    return $this->getDescendantsQuery()->count();
  }
  
  /**
   * Gets node's parent if exists else null
   * @return Doctrine_Node
   */
  public function getParent() {
    return $this->record->getParent();
  }
  
  /**
   * Gets the node's previous sibling
   * @return Doctrine_Node
   */
  public function getPrevSibling() {
    if (($field = $this->_tree->getAttribute('sortBy')) === null) {
      $this->unsupportedMethod();
    }
    return $this->getPrevSiblingsQuery()->fetchOne();
  }
  
  /**
   * Throws Doctrine_Node_Exception. Call in methods hasn't be implemented yet.
   * @throws Doctrine_Node_Exception
   */
  private function unsupportedMethod() {
    throw new Doctrine_Node_Exception('Method is unsupported in MaterializedPath');
  }
  
  /**
   * Gets node's siblings
   * @param type $includeNode
   * @return Doctrine_Collection
   */
  public function getSiblings($includeNode = false) {
    return $this->getSiblingsQuery()->execute();
  }
  
  /**
   * Checks if the node has children
   * @return bool
   */
  public function hasChildren() {
    return (bool)$this->record->getChildren()->count();
  }
  
  /**
   * Checks if the node has next sibling in horizontal sorted tree.
   * @return bool
   */
  public function hasNextSibling() {
    if (($field = $this->_tree->getAttribute('sortBy')) === null) {
      $this->unsupportedMethod();
    }
    return $this->getNextSiblingsQuery()->count() > 0;
  }
  
  /**
   * Checks if the node's parent exists
   * @return bool
   */
  public function hasParent() {
    return null !== $this->record->getParentId();
  }  
  
  /**
   * Checks if the node has previous sibling in horizontal sorted tree.
   * @return bool
   */
  public function hasPrevSibling() {
    if (($field = $this->_tree->getAttribute('sortBy')) === null) {
      $this->unsupportedMethod();
    }
    return $this->getPrevSiblingsQuery()->count();
  }
  
  /**
   * Inserts node as first child of $dest
   * @param Doctrine_Record $dest 
   */
  public function insertAsFirstChildOf(Doctrine_Record $dest)
  {
    if ($this->isValidNode()) {
      throw new Doctrine_Node_Exception('Can not insert an existing node.');
    }
    $dest->getNode()->addChild($this->record);
  }
  
  /**
   * Inserts node as last child of $dest
   * @param Doctrine_Record $dest 
   */
  public function insertAsLastChildOf(Doctrine_Record $dest) {
    $this->insertAsFirstChildOf($dest);
  }
  
  /**
   * Makes node a next sibling of $dest.
   * If $dest is a tree's root, makes node tree's root too.
   * @param Doctrine_Record $dest 
   */
  public function insertAsNextSiblingOf(Doctrine_Record $dest)
  {
    if ($this->isValidNode()) {
      throw new Doctrine_Node_Exception('Can not insert existing nodes.');
    }
    if (!$dest->getNode()->getLevel()) {
      $this->makeRoot();
    }
    $dest->getParent()->getNode()->addChild($this->record);
  }
  
  /**
   * Makes node a new tree_root with $root_id
   * @param type $root_id 
   */
  public function makeRoot($root_id=null)
  {
    if (!$this->record || !$this->record->exists()) {
      throw new Doctrine_Node_Exception('Only saved records can be maked tree\'s roots.');
    }
    
    $root_id = null === $root_id ? $this->record->getPrimaryKey() : $root_id;    
    if ($this->_tree->hasManyRoots()) {
      $old_root_id = $this->record->get($this->_tree->getAttribute('rootColumnName'));
    } else {
      $old_root_id = null;
    }
    
    $this->setRootValue($root_id);
    $this->setLevel(0);
    $this->record->setParentId(null);
    
    $conn = $this->record->getTable()->getConnection();
    $conn->beginTransaction();
    try {
      $this->setPath($this->record->getPrimaryKey(), $old_root_id);
      $this->record->save();
    } catch (Exception $e) {
      $conn->rollback();
      throw $e;
      return false;
    }
    $conn->commit();
    return true;
  }
  
  /**
   * @deprecated
   * @param Doctrine_Record $dest 
   */
  public function insertAsParentOf(Doctrine_Record $dest) {
    throw new Doctrine_Node_Exception('Method is unsupported in Materialized Path');
  }
  
  /**
   * Inserts $this on $dest node's level
   * @param Doctrine_Record $dest 
   */
  public function insertAsPrevSiblingOf(Doctrine_Record $dest) {
    $this->insertAsNextSiblingOf($dest);
  }
  
  /**
   * Checking if node is equal to or descendant of $subj
   * @param Doctrine_Record $subj
   * @return bool
   */
  public function isDescendantOfOrEqualTo(Doctrine_Record $subj) {
    return in_array($subj->getPrimaryKey(), $this->getPathArray(true));
  }
  
  /**
   * Checking if node is equal to $subj
   * @param Doctrine_Record $subj
   * @return bool
   */
  public function isEqualTo(Doctrine_Record $subj) {
    return $subj->getPrimaryKey() == $this->record->getPrimaryKey();
  }
  
  /**
   * Checking if node hasn't any children
   * @return bool 
   */
  public function isLeaf() {
    return !$this->getNumberChildren();
  }
  
  /**
   * Checking if node is a tree's root
   * @return bool
   */
  public function isRoot() {
    return $this->isValidNode() && $this->getLevel() == 0;
  }
  
  /**
   *
   * @param Doctrine_Record $dest 
   */
  public function moveAsFirstChildOf(Doctrine_Record $dest)
  {
    if (!$this->isValidNode()) {
      throw new Doctrine_Node_Exception('Can not move unexisting nodes.');
    }
    $dest->getNode()->addChild($this->record);
  }
  
  /**
   *
   * @param Doctrine_Record $dest 
   */
  public function moveAsLastChildOf(Doctrine_Record $dest) {
    $this->moveAsFirstChildOf($dest);
  }
  
  /**
   *
   * @param Doctrine_Record $dest 
   */
  public function moveAsNextSiblingOf(Doctrine_Record $dest) {
    if (!$this->isValidNode()) {
      throw new Doctrine_Node_Exception('Can not move unexisting nodes.');
    }
    $this->insertAsNextSiblingOf($dest);
  }
  
  /**
   *
   * @param Doctrine_Record $dest 
   */
  public function moveAsPrevSiblingOf(Doctrine_Record $dest) {
    $this->moveAsNextSiblingOf($dest);
  }
  
  /**
   * Deletes a node an its descendants
   * Taking advantage of using database constraints
   */
  public function delete() {
    $this->record->delete();
  }
  
  /**
   * Getting records root id value
   */     
  public function getRootValue()
  {
    if ($this->_tree->hasManyRoots()) {
      return $this->record->get($this->_tree->getAttribute('rootColumnName'));
    }
    return 1;
  }
  
  /**
   * sets records root id value
   *
   * @param int            
   */
  public function setRootValue($value)
  {
    if ($this->_tree->hasManyRoots()) {
      $this->record->set($this->_tree->getAttribute('rootColumnName'), $value);
    }
  }
  
  /**
   * Checks if the node has valid root value
   * @return bool
   */
  public function hasValidRootValue()
  {
    if ($this->_tree->hasManyRoots() && null === $this->getRootValue()) {
      return false;
    }
    return true;
  }
  
  /**
   * Gets defined path separator
   * @return string
   */
  public function getPathSeparator()
  {
    return $this->_tree->getPathSeparator();
  }
}