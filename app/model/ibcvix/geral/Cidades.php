<?php
/**
 * Cidades Active Record
 * @author  Antonio Affonso
 */
class Cidades extends TRecord
{
    const TABLENAME = 'cidades';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'serial'; // {max, serial}
    
    
    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('nome');
        parent::addAttribute('id_estado');
        parent::addAttribute('id_ibge');
    }

    public function get_estado()
    {
        return Estados::find($this->id_estado);
    }

    

}
