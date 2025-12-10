<?php
/**
 * Cidade Active Record
 * @author  Antonio Affonso
 */
class Ministerios extends TRecord
{
    const TABLENAME = 'ministerios';
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
        parent::addAttribute('estatutario');
    }


}
