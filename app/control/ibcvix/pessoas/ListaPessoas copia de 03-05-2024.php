<?php

use Adianti\Widget\Datagrid\TDataGridColumn;

/**
 * DatagridSearchView
 *
 * @version    1.0
 * @package    samples
 * @subpackage tutor
 * @author     Pablo Dall'Oglio
 * @copyright  Copyright (c) 2006 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    http://www.adianti.com.br/framework-license
 */
class ListaPessoas extends TPage
{

    use Adianti\base\AdiantiStandardListTrait;
    private $form;
    private $datagrid;

    public function __construct()
    {
        parent::__construct();

        $this->setDatabase('ibcvix');            // defines the database
        $this->setActiveRecord('Pessoas');   // defines the active record
        $this->setDefaultOrder('nome_completo', 'asc');         // defines the default order
        $this->addFilterField('nome_completo', 'like', 'nome'); // filterField, operator, formField
        $this->setLimit(0);

        $this->form = new BootstrapFormBuilder;

        // criar os campos do formulário
        $id = new TEntry('id');
        $nome = new TEntry('nome');


        // add the fields
        $this->form->addFields([new TLabel('Nome')], [$nome]);

        // manter o formulário preenchido durante a navegação com os dados da sessão
        $this->form->setData(TSession::getValue(__CLASS__ . '_filter_data'));

        $lista_pessoas = new TCheckList('lista_pessoas');

        // cria um datagrid
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->width = '100%';

        // tornar rolável e definir a altura
        $this->datagrid->setHeight('50vh');
        $this->datagrid->makeScrollable();

        // adicionar as colunas
        $this->datagrid->addColumn(new TDataGridColumn('nome_completo',    'NOME',    'left',   '30%'));
        $data_nascimento = $this->datagrid->addColumn(new TDataGridColumn('data_nascimento',    'DATA DE NASCIMENTO',    'center',   '30%'));

        $id_categorias = new TDataGridColumn('Categorias->descricao', 'CATEGORIA', 'center', '30%');
        $this->datagrid->addColumn($id_categorias);




        //$action1 = new TDataGridAction([$this, 'onEdit'],   ['id'=>'{id}']);

        $action1 = new TDataGridAction(['CadastraPessoa', 'onEdit'], ['id' => '{id}']);
        $this->datagrid->addAction($action1, 'Edit', 'fa:edit green');
        //$this->form->addAction( _t('Save'),  new TAction( [$this, 'onSave'] ),  'fa:save green' );

        $data_nascimento->setTransformer(function ($value) {
            $value_br = TDate::convertToMask($value, 'yyyy-mm-dd', 'dd/mm/yyyy');

            $month = substr($value, 5, 2);
            $year  = substr($value, 0, 4);

            $label = (($month == date('m')) && ($year == date('Y'))) ? 'success' : 'warning';

            if ($value) {
                $div = new TElement('span');

                $div->add($value_br);
                return $div;
            }
        });

        // creates the datagrid model
        $this->datagrid->createModel();

        // search box
        /*$input_search = new TEntry('input_search');
        $input_search->placeholder = _t('Search');
        $input_search->setSize('100%');
        $this->datagrid->enableSearch($input_search, 'sobrenome, nome');*/


        // enable fuse search by column name
        //$this->datagrid->enableSearch($input_search, 'sobrenome', 'nome');

        $nomeusuario = TSession::getValue('username');

        $panel = new TPanelGroup("<span class = 'titulo-form'>PROCURA PESSOA</span>");
        $panel->add($this->form);
        $panel->add($this->datagrid)->style = 'overflow-x:auto';
        $panel->addFooter("USUÁRIO: $nomeusuario");

        // wrap the page content using vertical box
        $vbox = new TVBox;
        $vbox->style = 'width: 100%';
        //$vbox->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $vbox->add($panel);
        parent::add($vbox);
    }



    /**
     * Executed when the user clicks at the view button
     */
    public static function onView($param)
    {
        // get the parameter and shows the message
        $code = $param['code'];
        $name = $param['name'];
        new TMessage('info', "The code is: <b>$code</b> <br> The name is : <b>$name</b>");
    }

    /**
     * shows the page
     */
    function show()
    {
        $this->onReload();
        parent::show();
    }

    /**
     * Save the Contrato and the ContratoItem's
     */
    public static function onteste($param)
    {
        try {

            $data = new stdClass;
            TTransaction::open('ibcvix');
            //$pessoa = new Pessoas;
            $pessoa = Pessoas::find($param['id']);
            $pessoa->fromArray((array) $data); // preenche os atributos

            var_dump($pessoa);
            //var_dump($pessoa);
            TForm::sendData('CadastraPessoa', $pessoa);
            TTransaction::close(); // close the transaction

        } catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
    }
}
