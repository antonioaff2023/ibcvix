<?php

/**
 * EstadoList
 *
 * @version    1.0
 * @package    ibcvix
 * @subpackage control
 * @author     Pablo Dall'Oglio
 * @copyright  Copyright (c) 2006 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    http://www.adianti.com.br/framework-license
 */
class EstadoList extends TPage
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
    $this->setActiveRecord('Estados');   // defines the active record
    $this->setDefaultOrder('nome', 'asc');         // defines the default order
    $this->setLimit(6);

    $this->addFilterField('id', '=', 'id'); // filterField, operator, formField
    $this->addFilterField('uf', 'like', 'uf'); // filterField, operator, formField
    $this->addFilterField('nome', 'like', 'nome'); // filterField, operator, formField


    // creates the form
    $this->form = new BootstrapFormBuilder('form_search_Estado');
    $this->form->setFormTitle("<span class = 'titulo-form'><strong>Estado</strong></span>");


    // create the form fields
    //$id = new TEntry('id');
    $uf = new TEntry('uf');
    $nome = new TEntry('nome');



    // add the fields
    //$this->form->addFields( [ new TLabel('Id') ], [ $id ] );
    $this->form->addFields([new TLabel('UF')], [$uf]);
    $this->form->addFields([new TLabel('Nome')], [$nome]);


    // set sizes
    //$id->setSize('100%');
    $uf->setSize('100%');
    $nome->setSize('100%');


    // manter o formulário preenchido durante a navegação com os dados da sessão
    $this->form->setData(TSession::getValue(__CLASS__ . '_filter_data'));

    // adicionar as ações do formulário de pesquisa
    $btn = $this->form->addAction(_t('Find'), new TAction([$this, 'onSearch']), 'fa:search');
    $btn->class = 'btn btn-sm btn-primary';
    $btn->style = 'width: 100px;';
    $btn2 = $this->form->addActionLink(_t('New'), new TAction(['EstadoForm', 'onEdit'], ['register_state' => 'false']), 'fa:plus white');
    $btn2->class = 'btn btn-sm btn-success';
    $btn2->style = 'width: 100px;';


    // creates a Datagrid
    $this->datagrid = new TDataGrid;
    $this->datagrid->style = 'width: 100%';
    //$this->datagrid->datatable = 'true';
    // $this->datagrid->enablePopover('Popover', 'Hi <b> {name} </b>');


    // creates the datagrid columns
    //$column_id = new TDataGridColumn('id', 'Id', 'center', '10%');
    $column_uf = new TDataGridColumn('uf', 'UF', 'center', '10%');
    $column_nome = new TDataGridColumn('nome', 'Nome', 'left');

    $column_uf->enableAutoHide(250);
    // add the columns to the DataGrid
    //$this->datagrid->addColumn($column_id);
    $this->datagrid->addColumn($column_uf);
    $this->datagrid->addColumn($column_nome);


    // creates the datagrid column actions
    //$column_id->setAction(new TAction([$this, 'onReload']), ['order' => 'id']);
    $column_uf->setAction(new TAction([$this, 'onReload']), ['order' => 'uf']);
    $column_nome->setAction(new TAction([$this, 'onReload']), ['order' => 'nome']);


    $action1 = new TDataGridAction(['EstadoForm', 'onEdit'], ['id' => '{id}', 'register_state' => 'false']);
    $action2 = new TDataGridAction([$this, 'onDelete'], ['id' => '{id}']);

    $this->datagrid->addAction($action1, _t('Edit'),   'far:edit blue');
    $this->datagrid->addAction($action2, _t('Delete'), 'far:trash-alt red');

    // create the datagrid model
    $this->datagrid->createModel();


    // criar a página de navegação
    $this->pageNavigation = new TPageNavigation;
    $this->pageNavigation->enableCounters();
    $this->pageNavigation->setAction(new TAction(array($this, 'onReload')));
    $this->pageNavigation->setWidth($this->datagrid->getWidth());

    $data = $this->form->getData();


    $estado = $data->nome;
    $unidade = $data->uf;

    $panel = new TPanelGroup('', 'black');
    $panel->add($this->datagrid);
    $panel->addFooter($this->pageNavigation);

    // header actions
    $dropdown = new TDropDown(_t('Export'), 'fa:list');
    $dropdown->setPullSide('right');
    $dropdown->setButtonClass('btn btn-default waves-effect dropdown-toggle');
    $dropdown->addAction(_t('Save as CSV'), new TAction([$this, 'onExportCSV']), 'fa:table blue');
    $dropdown->addAction(_t('Save as PDF'), new TAction([$this, 'exportaGeral'], ['nome' => $estado, 'uf' => $unidade]), 'far:file-pdf red');
    $panel->addHeaderWidget($dropdown);

    // vertical box container
    $container = new TVBox;
    $container->style = 'width: 50%;';
    // $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
    $container->add($this->form);
    $container->add($panel);

    parent::add($container);
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


  function retornaModal($file)
  {

    // processa um template de página em HTML
    $mostra = new THtmlRenderer('app/resources/modal_pdf.html');

    $replaces = [];
    $replaces['file'] = $file;
    $mostra->enableSection('main', $replaces);


    return $mostra;
  }
  public function exportaGeral($param) //O parâmetro enviado já é o 'where' para montagem do SQL
  {
    try {



      TTransaction::open('ibcvix');
      $conn = TTransaction::get(); // obtém a conexão     
      //String final a ser usada para pesquisa

      $estado = $param['nome'];
      $uf = $param['uf'];
      $txtsql = <<<EOF
      SELECT nome, uf
      FROM estados 
      Where 

      nome like '%$estado%'
      and
      uf like '%$uf%'
            
      ORDER BY nome asc;
      
      EOF;



      $result = $conn->query($txtsql);

      //Cria corpo do relatório

      $html = <<<EOF

                  <style>
                  table {
                    border-collapse: collapse;
                    width: 50%;
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
                              
                            <th>ESTADO</td>
                            <th>SIGLA</td>
                                
                            </tr>       
                            </thead>  
      EOF;


      foreach ($result as $row) {
        $html .= "<tr>";
        $html .= "<td style='width: 15%; text-align: center'>" . $row['nome'] . "</td>";
        $html .= "<td style='width: 15%; text-align: center'>" . $row['uf'] . "</td>";
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
}
