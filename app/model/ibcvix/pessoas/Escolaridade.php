<?php
/**
 * Escolaridade Active Record
 * @author  Antonio Affonso
 */
class Escolaridade extends TRecord
{
    const TABLENAME = 'escolaridade';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'serial'; // {max, serial}
    
    
    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('id');
        parent::addAttribute('descricao');
        
    }


}
