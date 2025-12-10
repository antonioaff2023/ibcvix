<?php
/**
 * Profissão Active Record
 * @author  Antonio Affonso
 */
class Profissao extends TRecord
{
    const TABLENAME = 'profissao';
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
        parent::addAttribute('obs');
    }


}
