<?php
/*
 * Doctrine_Template_MaterializedPath.class.php
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
 * Doctrine_Template_MaterializedPath
 * @package doctrine
 * @subpackage sfDoctrineMaterializedPlugin
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @author Artem Chistyakov <chistyakov.artem@gmail.com>
 * 
 * @property Doctrine_Table $table
 */
class Doctrine_Template_MaterializedPath extends Doctrine_Template
{
  /**
   * Set up MaterializedPath template
   *
   * @return void
   */
  public function setUp()
  {
    $this->_table->setOption('treeOptions', $this->_options);
    $this->_table->setOption('treeImpl', 'MaterializedPath');
  }

  /**
   * Call set table definition for the MaterializedPath behavior
   *
   * @return void
   */
  public function setTableDefinition()
  {
    $this->addListener(new Doctrine_MaterializedPath_Listener($this->_options));
    $this->_table->getTree()->setTableDefinition();
  }
}