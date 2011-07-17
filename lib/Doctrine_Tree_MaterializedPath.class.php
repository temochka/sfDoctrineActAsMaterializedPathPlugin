<?php
/*
 * Doctrine_Tree_MaterializedPath.class.php
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
 * Doctrine_Tree_MaterializedPath
 * @package doctrine
 * @subpackage sfDoctrineMaterializedPlugin
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @author Artem Chistyakov <chistyakov.artem@gmail.com>
 * 
 * @property Doctrine_Table $table
 */
class Doctrine_Tree_MaterializedPath extends Doctrine_Tree implements Doctrine_Tree_Interface {
  /**
   * Default path separator
   */
  const PATH_SEPARATOR = '>';
  
  /**
   * Constructor, creates tree with reference to table and sets default root options
   *
   * @param object $table instance of Doctrine_Table
   * @param array $options options
   */
  public function __construct(Doctrine_Table $table, $options)
  {
    // set default many root attributes
    $options['hasManyRoots'] = 
      isset($options['hasManyRoots']) ? 
        $options['hasManyRoots'] : 
        false;

    if ($options['hasManyRoots']) {
      $options['rootColumnName'] = isset($options['rootColumnName']) ? 
        $options['rootColumnName'] : 'root_id';
    }
    
    if (
      isset($options['sortOrder']) && 
      !in_array($options['sortOrder'], array('ASC', 'DESC'))
    )
    {
      throw new InvalidArgumentException('Sort order should has ASC or DESC value.');
    }
    
    $options['sortOrder'] = 
      isset($options['sortOrder']) ? $options['sortOrder'] : 'ASC';
    
    $options['pathSeparator'] = 
      isset($options['pathSeparator']) ? 
        $options['pathSeparator'] : self::PATH_SEPARATOR;
    
    if (
      is_numeric($options['pathSeparator']) || 
      strlen($options['pathSeparator']) > 1
    )
    {
      throw new InvalidArgumentException(
        'Unexpected path separator format. '.
        'Path separator should be a single non-numeric character.'
      );
    }

    parent::__construct($table, $options);
    
    if (
      isset($options['sortBy']) && null !== $options['sortBy'] &&
      !$this->table->hasColumn($options['sortBy'])
    )
    {
      throw new InvalidArgumentException(sprintf(
        'Column "%s" does not exist in table.',
        (string)$options['sortBy']
      ));
    }
    $dispatcher = sfProjectConfiguration::getActive()->getEventDispatcher();
    $dispatcher->connect('command.post_command', array($this, 'listenToCommandPostCommandEvent'));
  }  
  
  /**
   * Used to define table attributes required for the MaterializedPath implementation
   * adds path columns for corresponding left and right values
   *
   */
  public function setTableDefinition()
  {
    if (
      ($root = $this->getAttribute('rootColumnName')) && 
      (!$this->table->hasColumn($root))
    )
    {
      $this->table->setColumn($root, 'integer');
    }

    $this->table->setColumn('path', 'string', 255);
    
    if ($level = $this->getAttribute('levelColumnName')) {
      $this->table->setColumn($level . ' AS level', 'integer', 2);
    } else {
      $this->table->setColumn('level', 'integer', 2);
    }
        
    $this->table->setColumn('parent_id', 'integer', 5);
    $this->table->hasOne($this->table->getComponentName().' as Parent', array(
      'local' => 'parent_id',
      'foreign' => 'id',
      'onDelete' => 'CASCADE'
    ));
    $this->table->hasMany($this->table->getComponentName().' as Children', array(
      'local' => 'id',
      'foreign' => 'parent_id'
    ));
  }  
  
  /**
   * Makes the $record tree's root
   * @throws Doctrine_Tree_Exception
   * @param Doctrine_Record $record 
   * @return Doctrine_Record
   */
  public function createRoot(Doctrine_Record $record = null) {
    if ($this->getAttribute('hasManyRoots')) {
      if (!$record || (!$record->exists() && ! $record->getNode()->getRootValue())
            || $record->getTable()->isIdentifierComposite()
      )
      {
        throw new Doctrine_Tree_Exception(
          "Node must have a root id set or must "
          . " be persistent and have a single-valued numeric primary key in order to"
          . " be created as a root node. Automatic assignment of a root id on"
          . " transient/new records is no longer supported.");
      }

      if ($record->exists() && !$record->getNode()->getRootValue()) {
        // Default: root_id = id
        $identifier = $record->getTable()->getIdentifier();
        $record->getNode()->setRootValue($record->get($identifier));
      }
    }
    
    if (!$record) {
      $record = $this->table->create();
    }
    
    $record->set('level', 0);

    $record->save();
    return $record;
  }
  
  /**
   * Fetches the tree's branch started with given primary key
   * Supported options:
   * - depth
   * 
   * @param int $pk
   * @param array $options 
   * @param int $hydrationMode
   * @return Doctrine_Collection
   */
  public function fetchBranch($pk, $options = array(), $hydrationMode = null)
  {
    $record = $this->table->find($pk);
    if (!($record instanceof Doctrine_Record) || !$record->exists()) {
      throw new Doctrine_Tree_Exception('Record with given id does not exists.');
    }

    $depth = isset($options['depth']) ? $options['depth'] : null;

    $q = $record->getNode()->getDescendantsQuery($depth, true);
    
    return $this->getQueryWithRootId(
        $record->getNode()->getRootValue(), 
        $q
      )->execute(array(), $hydrationMode);
  }
  
  /**
   *
   * @param int $pk
   * @param array $options
   * @param int $hydrationMode
   * @return Doctrine_Collection
   */
  public function fetchChildrenOf($pk, $options = array(), $hydrationMode = null)
  {
    return $this->getQuery()->where('parent_id=?', $pk)
      ->execute(array(), $hydrationMode);
  }
  
  /**
   * Fetches tree's root with given root_id
   * @param int $root_id
   * @return Doctrine_Record
   */
  public function fetchRoot($root_id = 1) {
    return $this->getQueryWithRootId($root_id)->andWhere('level=?', 0)->fetchOne();
  }
  
  /**
   * Fetches all roots in table
   * @return Doctrine_Collection
   */
  public function fetchRoots() {
    return $this->table
      ->createQuery()->where('parent_id IS NULL')
      ->orderBy($this->getOrderBy())->execute();
  }
  
  /**
   * Fetches the tree with given root_id,
   * @param type $options
   * @param type $hydrationMode
   * @return type 
   */
  public function fetchTree($options = array(), $hydrationMode = null)
  {
    $root_id = isset($options['root_id']) ? $options['root_id'] : 1;
    $depth = isset($options['depth']) ? $options['depth'] : 1;
    
    return $this->getQueryWithRootId($root_id)
      ->where('level <= ?', $depth)->execute(array(), $hydrationMode);
  }

  /**
   * Fixes the tree nodes pathes
   * @param int $root_id
   */
  public function fixTree($root_id=1)
  {
    $root = $this->fetchRoot($root_id);
    if (!$root || !$root->exists()) {
      throw new Doctrine_Tree_Exception('Given root does not exist');
    }
    $root->getNode()->fixNodeRecursive();
  }

  /**
   * All records fix
   */
  public function fixAll()
  {
    foreach ($this->fetchRoots() as $root) {
      $root->getNode()->fixNodeRecursive();
    }
  }

  /**
   * Expands query with root_id control
   * @param int $root_id
   * @param Doctrine_Query $query
   * @return Doctrine_Query
   */
  public function getQueryWithRootId($root_id=1, $query=null)
  {
    if (null === $query) $query = $this->getQuery();
    if ($root = $this->getAttribute('rootColumnName')) {
      if (is_array($root_id))
        $query->andWhereIn($query->getRootAlias().'.'.$root, $root_id);
      else
        $query->addWhere($query->getRootAlias().'.'.$root.'=?', $root_id);
    }
    return $query;
  }
  
  /**
   * Returns query template
   * @return Doctrine_Query
   */
  public function getQuery()
  {
    $q = $this->table->createQuery('_node')->orderBy($this->getOrderBy());
    return $q;
  }
  
  /**
   * Checks if the table has many roots
   * @return bool
   */
  public function hasManyRoots()
  {
    return $this->getAttribute('hasManyRoots');
  }
  
  /**
   * Returns defined path separator
   * @return string
   */
  public function getPathSeparator()
  {
    return $this->getAttribute('pathSeparator');
  }
  
  /**
   * Returns formed ORDER BY part for building queries
   * @access private
   * @return string
   */
  private function getOrderBy()
  {
    $order_by = array();
    if ($this->hasManyRoots()) {
      $order_by[] = $this->getAttribute('rootColumnName');
    }
    $order_by[] = 'path';
    if ($field_name = $this->getAttribute('sortBy')) {
      $order_by[] = $field_name.' '.$this->getAttribute('sortOrder');
    }
    return implode(',', $order_by);
  }

  /**
   *
   * @param sfEvent $event
   */
  public function listenToCommandPostCommandEvent(sfEvent $event)
  {
    $invoker = $event->getSubject();
    if ($invoker instanceof sfDoctrineDataLoadTask)
    {
      $this->fixAll();
    }
  }
}