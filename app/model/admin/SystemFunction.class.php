<?php
/**
* Criada por Antonio Affonso
 */
class SystemFunction extends TRecord
{
    const TABLENAME = 'system_function';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}
    
    
    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('descricao');
        parent::addAttribute('obs');
    }
}
