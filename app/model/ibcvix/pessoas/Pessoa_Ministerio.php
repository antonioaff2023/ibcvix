<?php
/**
 * Pessoa Ministério Active Record
 * @author  Antonio Affonso
 */
class PessoaMinisterio extends TRecord
{
    const TABLENAME = 'pessoa_ministerio';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'serial'; // {max, serial}
    
    
    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('id');
        parent::addAttribute('id_pessoa');
        parent::addAttribute('id_ministerio');
        parent::addAttribute('funcao');
        parent::addAttribute('lider');  //Boolean para identificar se a pessoa é a líder ou não do ministério. Importante determinar se existe algo ocupando a função.
    }


}
