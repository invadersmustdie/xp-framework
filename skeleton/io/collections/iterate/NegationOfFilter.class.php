<?php
/* This class is part of the XP framework
 *
 * $Id$
 */

  uses('io.collections.iterate.IterationFilter');

  /**
   * Negation filter
   *
   * @purpose  Iteration Filter
   */
  class NegationOfFilter extends Object implements IterationFilter {
    public
      $filter= NULL;
      
    /**
     * Constructor
     *
     * @param   io.collections.iterate.IterationFilter filter
     */
    public function __construct($filter) {
      $this->filter= $filter;
    }
  
    /**
     * Accepts an element
     *
     * @param   io.collections.IOElement element
     * @return  bool
     */
    public function accept($element) {
      return !$this->filter->accept($element);
    }

    /**
     * Creates a string representation of this iterator
     *
     * @return  string
     */
    public function toString() {
      return $this->getClassName().'<'.$this->filter->toString().'>';
    }
  
  } 
?>
