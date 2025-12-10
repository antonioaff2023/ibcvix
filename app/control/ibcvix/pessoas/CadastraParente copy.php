<?php
use Adianti\Control\TWindow;
/**
 * ContratoForm
 *
 * @version    1.0
 * @package    erphouse
 * @subpackage control
 * @author     Pablo Dall'Oglio
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
        parent::setSize(0.8, null);
        parent::setMinWidth(0.9, 1000);
        parent::removePadding();
        parent::removeTitleBar();
        parent::disableEscape();

        // creates the form

        $this->form = new BootstrapFormBuilder('input_form');
        $this->form->setFormTitle('Cadastra Parente');
        $this->form->setClientValidation(true);

        $parente = new  TDBUniqueSearch('id_parente', 'ibcvix', 'Pessoas', 'id', 'nome_completo');
        $parente->setMinLength(0);
        $parente->style = 'width: 20vw;';
        $parente_lbl = new TLabel('Parente:');


        $parentesco = new  TDBUniqueSearch('id_parentesco', 'ibcvix', 'parentesco', 'id', 'descricao');
        $parentesco->setMinLength(0);
        $parentesco->style = 'width: 10vw;';
        $parentesco_lbl = new TLabel('Parentesco:');


        $row = $this->form->addFields([$parente_lbl], [$parente]);
        $row->layout = ['col-sm-2', 'col-sm-4'];


        $row = $this->form->addFields([$parentesco_lbl], [$parentesco]);
        $row->layout = ['col-sm-2', 'col-sm-4'];


        $this->form->addAction('Salvar', new TAction([__CLASS__, 'onConfirm1']), 'fa:save green');

        // create the page container
        $saida = new TVBox;
        $saida->style = 'width: 80%';
        //$container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $saida->add($this->form);
        parent::add($saida);
    }

    public static function onConfirm1($param)
    {
        new TMessage('info', 'Confirm1 : ' . str_replace(',', '<br>', json_encode($param)));
    }
    public function onEdit()
    {
    }
    public static function onClose($param)
    {
        parent::closeWindow();
    }
}
