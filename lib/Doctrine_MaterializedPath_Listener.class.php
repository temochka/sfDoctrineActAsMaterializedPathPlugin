<?php
/*
 * Doctrine_MaterializedPath_Listener.class.php
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
 * Doctrine_MaterializedPath_Listener
 * Listener for MaterializedPath nodes
 *
 * @package     Doctrine
 * @subpackage  Template
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @author      Artem Chistyakov <chistyakov.artem@gmail.com>
 */
class Doctrine_MaterializedPath_Listener extends Doctrine_Record_Listener
{
  /**
   * Array of Schoolable options
   *
   * @var string
   */
  protected $_options = array();

  /**
   * __construct
   *
   * @param string $options
   * @return void
   */
  public function __construct(array $options)
  {
    $this->_options = $options;
  }
  
  /**
   * Executes level fixes before saving the record
   * @param Doctrine_Event $event 
   */
  public function preSave(Doctrine_Event $event) {
    /* @var $object sfDoctrineRecord */
    $object = $event->getInvoker();
    $object->getNode()->hasValidRootValue();
    $object->getNode()->fixLevel();
  }

  /**
   * Executes path and level fixes after saving the record
   * @param Doctrine_Event $event 
   */
  public function postSave(Doctrine_Event $event)
  {
    /** @var $object sfDoctrineRecord */
    $object = $event->getInvoker();
    $object->getNode()->fixPathAndSave();
  }
}
