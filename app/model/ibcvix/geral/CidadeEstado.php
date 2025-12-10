<?php

use Adianti\Database\TRecord;

/**
 * Cidades Active Record
 * @author  Antonio Affonso
 */
class CidadeEstado extends TRecord
{
    const TABLENAME = 'cidade_estado';
    const PRIMARYKEY= 'id';
    //const IDPOLICY =  'serial'; // {max, serial}
    
    
    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('id');
        parent::addAttribute('nome');
        parent::addAttribute('uf');
        parent::addAttribute('id_estado');
        parent::addAttribute('id_ibge');

        
    }


}
