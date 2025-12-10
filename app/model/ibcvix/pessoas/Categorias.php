<?php
use Adianti\Database\TRecord;
/**
 * Categorias Active Record
 * Trata das categorias de cada pessoa Visitante, Membro Ativo, Membro Inativo, Membro Agregado(?), Inscrito, Agregado, 
 * 
 * @author  Antonio Affonso
 */
class Categorias extends TRecord
{
    const TABLENAME = 'categorias';
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
        parent::addAttribute('membro');
    }


}
