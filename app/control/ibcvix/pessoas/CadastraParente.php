<?php

use Adianti\Control\TWindow;
use Adianti\Database\TCriteria;
use Adianti\Database\TTransaction;
use Adianti\Widget\Dialog\TDialogIbcv;
use Adianti\Widget\Dialog\TInputDialog;
use Adianti\Widget\Form\TEntry;
use Adianti\Widget\Wrapper\TDBUniqueSearch;




/**
 * ContratoForm
 *
 * @version    1.0
 * @package    erphouse
 * @subpackage control
 * @author     Antonio Affonso
 * @copyright  Copyright (c) 2006 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    http://www.adianti.com.br/framework-license
 */
class CadastraParente extends TWindow
{
    protected $form; // form
    protected $fieldlist;

    /**
     * Class constructor
     * Creates the page and the registration form
     */
    function __construct($param)
    {
        parent::__construct($param);

        // creates the form


        $this->form = new BootstrapFormBuilder('input_form');
        
        //$this->form->setFormTitle("<span class = 'titulo-form'>Cadastra Parente</span>");
        //$this->form->setClientValidation(true);

        $id_pessoa = new TEntry('key');
        $id_pessoa->setValue($param['key']);

        $criteria =  new TCriteria;
        $criteria->add(new TFilter('id', '<>', $param['id'])); //Elimina a pessoa selecionada da lista

        //Elimina todos os parentes já cadastrados da lista de busca
        TTransaction::open('ibcvix');
        $cadastrados = PessoaParentesco::where('id_pessoa', '=', $param['id'])->load();

        foreach ($cadastrados as $cadastrado) {
            $criteria->add(new TFilter('id', '<>', $cadastrado->id_parente));
        }

        TTransaction::close();

        $parente = new  TDBUniqueSearch('id_parente', 'ibcvix', 'Pessoas', 'id', 'nome_completo', null, $criteria);

        $parente->setMinLength(0);
        $parente->style = 'width: 20vw;';
        $parente->setId('parente');
        $parente_lbl = new TLabel('Parente:');


        $parentesco = new  TDBUniqueSearch('id_parentesco', 'ibcvix', 'Parentesco', 'id', 'descricao');
        $parentesco->setMinLength(0);
        $parente->setId('parentesco');
        $parentesco->style = 'width: 10vw;';
        $parentesco_lbl = new TLabel('Parentesco:');


        $row = $this->form->addFields([$parente_lbl, $parente]);
        $row->layout = ['col-sm-2', 'col-sm-4'];


        $row = $this->form->addFields([$parentesco_lbl, $parentesco]);
        $row->layout = ['col-sm-2', 'col-sm-4'];

        $row = $this->form->addFields([$id_pessoa]);
        $row->style = 'display: none';

        $this->form->addAction('Salvar', new TAction([$this, 'onConfirm1'], ['key' => $this->id_atual]), 'fa:save green');



        new TDialogIbcv('<strong>CADASTRA PARENTE</strong>',$this->form);
        
    }


    public static function onConfirm1($param)
    {
        try {


            TTransaction::open('ibcvix');
            $preparaGrava = new PessoaParentesco;
            //CadastraParente::onGravaParentes($param['key'],$param['id_parente'],$param['id_parentesco']);
            /*$parente = new PessoaParentesco;
            $parente->id_pessoa = $param['key'];
            $parente->id_parente = $param['id_parente'];
            $parente->id_parentesco = $param['id_parentesco'];
            $parente->store();*/

            $parentesco2 = '';

            switch ($param['id_parentesco']) {
                case 1: //Trata gravação de cônjuge 

                    $parentesco2 = $param['id_parentesco'];

                    $pessoaparente = Pessoas::where('id', '=', $param['id_parente'])->first();
                    $pessoa = Pessoas::find($param['key']);
                    if ($pessoa) {
                        $pessoa->conjuge = $pessoaparente->nome_completo;
                        if ($pessoa->id_categorias >= 1 && $pessoa->id_categorias <= 3) {
                            $pessoa->conjuge_cristao = 'S';
                        }
                        $pessoa->store();

                        $pessoaparente->conjuge = $pessoa->nome_completo;
                        if ($pessoaparente->id_categorias >= 1 && $pessoaparente->id_categorias <= 3) {
                            $pessoaparente->conjuge_cristao = 'S';
                        }
                        $pessoaparente->store();
                    }
                    break;

                case 2: //trata gravação de filhos

                    $pais = new Pessoas($param['key']);
                    if ($pais->genero = 'F') {
                        $parentesco2 = 5;
                    } else if ($pais->genero = 'M') {
                        $parentesco2 = 4;
                    } else {
                        new TMessage('error', 'CADASTRE O SEXO DOS PAIS.'); // exibe mensagem de exceção
                        exit;
                    }

                    break;

                case 3:
                case 9: //Trata se for irmãos ou primos
                    $parentesco2 = $param['id_parentesco'];
                    
                    break;

                case 4:
                case 5: //Trata se for pai ou mãe
                    $parentesco2 = 2;

                    break;

                case 6: //Trata se for Avós
                    $parentesco2 = 10;

                    break;
                case 7: //Trata se for tios
                    $parentesco2 = 8;
                    break;
                case 8:  //Trata se for sobrinhos
                    $parentesco2 = 7;
                    break;
                case 10:  //Trata se for netos
                    $parentesco2 = 6;
                    break;
                case 11: //Trata se for bisavós
                    $parentesco2 = 12;
                    break;
                case 12: //Trata se for bisnetos
                    break;
                default:
                    new TMessage('error', 'Não foi possível gravar. Consulte os dados.'); // exibe mensagem de exceção
                    break;
            }
            $preparaGrava->onGravaParentes($param['key'], $param['id_parente'], $param['id_parentesco']);
            $preparaGrava->onGravaParentes($param['id_parente'], $param['key'], $parentesco2);

            TTransaction::close();

            AdiantiCoreApplication::loadPage('CadastraPessoa', 'onEdit', ['key' => $param['key'], 'id' => $param['key'], 'tab' => '3']);
        } catch (Exception $e) {
            new TMessage('error', $e->getMessage()); // exibe mensagem de exceção
        }
    }

    public function onEdit($param)
    {
    }
    public static function onClose()
    {
        parent::closeWindow();
    }
}
