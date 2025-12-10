<?php

use Adianti\Database\TTransaction;

/**
 * Pessoa Ministério Active Record
 * @author  Antonio Affonso
 */
class PessoaParentesco extends TRecord
{
    const TABLENAME = 'pessoa_parentesco';
    const PRIMARYKEY = 'id';
    const IDPOLICY =  'max'; // {max, serial}


    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('id');
        parent::addAttribute('id_pessoa');
        parent::addAttribute('id_parente');  //Identifica o parente no id da tabela pessoa
        parent::addAttribute('id_parentesco');
    }

    public function get_parente()
    {
        return Pessoas::find($this->id_parente);
    }
    
    public function get_pessoa()
    {
        return Pessoas::find($this->id_pessoa);
    }
    

    public function get_pessoaparentesco()
    {
        return Parentesco::find($this->id_parentesco);
    }

    public function onPreparaParentes($id_pessoa, $id_parente, $id_parentesco1, $id_parentesco2)
    {

        try {

            if ($id_pessoa) {
                $this->onGravaParentes($id_pessoa, $id_parente, $id_parentesco1);
            }

            if ($id_parente) {
                $this->onGravaParentes($id_parente, $id_pessoa, $id_parentesco2);
            }

        } catch (Exception $e) {
            new TMessage('error', $e->getMessage()); // exibe mensagem de exceção
        }
    }

    public function onExcluiParente($id_pessoa, $id_parente) {

        try{
        TTransaction::open('ibcvix');
        $this->where('id_pessoa','=', $id_pessoa)->where('id_parente','=', $id_parente)->delete();
        $this->where('id_pessoa','=', $id_parente)->where('id_parente','=', $id_pessoa)->delete();
        } catch (Exception $e) {
            new TMessage('error', $e->getMessage()); // exibe mensagem de exceção
            TTransaction::close();
        }

        
    }

    public function onGravaParentes($principal, $parente, $parentesco)
    {
        try {
            TTransaction::open('ibcvix');
            $gravaparente = new PessoaParentesco;
            $gravaparente->id_pessoa = $principal;
            $gravaparente->id_parente = $parente;
            $gravaparente->id_parentesco = $parentesco;
            $gravaparente->store();
            TTransaction::close();
        } catch (Exception $e) {
            new TMessage('error', $e->getMessage()); // exibe mensagem de exceção
            TTransaction::close();
        }
    }
    
}
