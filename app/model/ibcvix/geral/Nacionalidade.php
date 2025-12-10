<?php
/**
 * Nacionalidade Active Record
 * @author  Antonio Affonso
 */
class Nacionalidade extends TRecord
{
    const TABLENAME = 'nacionalidade';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'serial'; // {max, serial}
    
    
    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('id');
        parent::addAttribute('nome');
    }


}
