<?php

use Adianti\Database\TTransaction;
use Adianti\Registry\TSession;
use Adianti\Widget\Form\TButton;

/**
 * CidadeList
 *
 * @version    1.0
 * @package    ibcvix
 * @subpackage control
 * @author     Pablo Dall'Oglio
 * @copyright  Copyright (c) 2006 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    http://www.adianti.com.br/framework-license
 */
class ListaPessoas extends TPage
{
    protected $form;     // registration form
    protected $datagrid; // listing
    protected $pageNavigation;
    protected $formgrid;
    protected $deleteButton;

    use Adianti\base\AdiantiStandardListTrait;

    /**
     * Page constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->setDatabase('ibcvix');            // defines the database
        $this->setActiveRecord('Pessoas');   // defines the active record
        $this->setDefaultOrder('nome', 'asc');         // defines the default order
        $this->setLimit(0);
        // $this->setCriteria($criteria) // define a standard filter

        $this->addFilterField('id', '=', 'id'); // Campo filtrado, operador, campo no formulário
        $this->addFilterField('nome_completo', 'like', 'nome'); //Campo filtrado, operador, campo no formulário


        // creates the form
        $this->form = new BootstrapFormBuilder('form_procura_pessoa');




        // criar os campos do formulário
        $id = new TEntry('id');
        $nome = new TEntry('nome');
        $botao = new TButton('procura');
        $botao->setAction(new TAction(array($this, 'onSearch')), 'search');
        $botao->setLabel('Procurar');
        $botao->class = 'btn btn-success';

        // adicionar os campos
        //$this->form->addFields( [ new TLabel('Id') ], [ $id ] );
        $this->form->addFields([new TLabel('Digite o nome ou parte dele:')], [$nome], [$botao]);


        // set sizes
        //$id->setSize('100%');
        $nome->setSize('100%');


        // keep the form filled during navigation with session data
        $this->form->setData(TSession::getValue(__CLASS__ . '_filter_data'));



        // cria um datagrid
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->width = '100%';

        // tornar rolável e definir a altura
        $this->datagrid->setHeight('45vh');
        $this->datagrid->makeScrollable();

        // adicionar as colunas
        $this->datagrid->addColumn(new TDataGridColumn('nome_completo',    'NOME',    'left',   '30%'));
        $data_nascimento = $this->datagrid->addColumn(new TDataGridColumn('data_nascimento',    'DATA DE NASCIMENTO',    'center',   '30%'));

        $id_categorias = new TDataGridColumn('Categorias->descricao', 'CATEGORIA', 'center', '30%');
        $this->datagrid->addColumn($id_categorias);

        $usr = new SystemGroup();
        if ($usr->GruposAcesso([1,3,4])) {
            $action1 = new TDataGridAction(['CadastraPessoa', 'onEdit'], ['id' => '{id}']);
            $this->datagrid->addAction($action1, 'Editar', 'fa:edit green');

            // criar ação DELETE
            $action_del = new TDataGridAction([$this, 'onDelete'], ['id' => '{id}']);
            $this->datagrid->addAction($action_del, 'Excluir', 'far:trash-alt red');
        } 
            $action1 = new TDataGridAction(['FichaPessoa', 'onEdit'], ['id' => '{id}']);
            $this->datagrid->addAction($action1, 'Visualizar', 'fa:address-card');

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
    public function onExportPDF($param)
    {
        try {

            $this->excluiArquivo();

            $current_datetime = date('Y-m-d_H-i-s');

            $output = 'app/output/export_' . $current_datetime . '.pdf';
            $this->exportToPDF($output);

            $window = TWindow::create('', 0.8, 0.8);
            $object = new TElement('object');
            $object->{'data'}  = 'download.php?file=' . $output;
            $object->{'type'}  = 'application/pdf';
            $object->{'style'} = "width: 100%; height:calc(100% - 10px)";


            // processa um template de página em HTML para exibir em modal
            $mostra = new THtmlRenderer('app/resources/modal_pdf.html');

            $replaces = [];
            $replaces['file'] = $output;
            $mostra->enableSection('main', $replaces);

            $window->add($mostra);
            $window->show();
        } catch (Exception $e) {
            new TMessage('error', $e->getMessage());
        }
    }


    function excluiArquivo()
    {
        $directory = 'app/output/';
        $search_pattern = $directory . '*export*';

        $files = glob($search_pattern);

        // Itera sobre a lista de arquivos encontrados e os exclui
        foreach ($files as $arq) {
            if (is_file($arq)) { // Verifica se o item é um arquivo
                if (unlink($arq)) {
                } else {
                }
            }
        }
    }


    public function exportaGeral($param) //O parâmetro enviado já é o 'where' para montagem do SQL
    {
        try {



            TTransaction::open('ibcvix');
            $conn = TTransaction::get(); // obtém a conexão     
            //String final a ser usada para pesquisa

            $cidade = $param['nome'];
            $uf = $param['estado'];


            if ($uf > 0) {
                $where = " id_estado = $uf and nome like '%$cidade%' ";
            } else {
                $where = " nome like '%$cidade%' ";
            }

            $txtsql = <<<EOF
      SELECT nome, uf
      FROM cidade_estado 
      Where 

      $where
            
      ORDER BY nome asc;
      
      EOF;



            $result = $conn->query($txtsql);

            //Cria corpo do relatório

            $html = <<<EOF

                  <style>
                  table {
                    border-collapse: collapse;
                    width: 70%;
                  }
                  th, td {
                    border: 1px solid #000;
                    padding: 8px;
                    text-align: left;
                  }
                  th {
                    background-color: #f2f2f2;
                    text-align: center;
                  }
                </style>
                        <table>
                            <thead>
                            <tr>
                              
                            <th>CIDADE</td>
                            <th>ESTADO</td>
                                
                            </tr>       
                            </thead>  
      EOF;


            foreach ($result as $row) {
                $html .= "<tr>";
                $html .= "<td style='width: 70%; text-align: left'>" . $row['nome'] . "</td>";
                $html .= "<td style='width: 30%; text-align: center'>" . $row['uf'] . "</td>";
                $html .= "</tr>";
            };



            $html .= "</table>";


            // converte o modelo HTML em PDF
            $dompdf = new \Dompdf\Dompdf();
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();



            $this->excluiArquivo();

            $current_datetime = date('Y-m-d_H-i-s');

            $file = 'app/output/export_' . $current_datetime . '.pdf';

            // gravar e abrir arquivo
            file_put_contents($file, $dompdf->output());

            // Use o Bootstrap Modal para exibir o PDF 

            $mostra = $this->retornaModal($file);

            $container = new TVBox;
            $container->style = 'width: 100%';
            $container->add($mostra);
            parent::add($container);


            TTransaction::close(); // close the transaction

        } catch (Exception $e) {
            new TMessage('error', $e->getMessage());
            TTransaction::close();
        }
    }
    function retornaModal($file)
    {

        // processa um template de página em HTML
        $mostra = new THtmlRenderer('app/resources/modal_pdf.html');

        $replaces = [];
        $replaces['file'] = $file;
        $mostra->enableSection('main', $replaces);


        return $mostra;
    }
}
