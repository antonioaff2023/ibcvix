<?php
/**
 * Estado Civil Active Record
 * @author  Antonio Affonso
 */
class EstadoCivil extends TRecord
{
    const TABLENAME = 'estado_civil';
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
