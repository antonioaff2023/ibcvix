<?php
/**
 * Profissão Active Record
 * @author  Antonio Affonso
 */
class Parentesco extends TRecord
{
    const TABLENAME = 'parentesco';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}
    
    
    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('id');
        parent::addAttribute('descricao');
        parent::addAttribute('hierarquia');
    }


}
