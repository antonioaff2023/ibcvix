<?php
/**
 * Estados Active Record
 * @author  Antonio Affonso
 */
class Estados extends TRecord
{
    const TABLENAME = 'estados';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'serial'; // {max, serial}
    
    
    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('nome');
        parent::addAttribute('uf');
    }


}
