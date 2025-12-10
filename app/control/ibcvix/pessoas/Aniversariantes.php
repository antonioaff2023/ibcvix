<?php

use Adianti\Database\TCriteria;
use Adianti\Database\TExpression;
use Adianti\Database\TFilter;
use Adianti\Database\TRepository;
use Adianti\Database\TTransaction;
use Adianti\Registry\TSession;
use Adianti\Widget\Base\TElement;
use Adianti\Widget\Container\TPanel;
use Adianti\Widget\Container\TPanelGroup;
use Adianti\Widget\Form\TCheckList;
use Adianti\Widget\Form\TEntry;

/*
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
*/

class Aniversariantes extends TPage
{
  protected $form;
  protected $datagrid;

  use Adianti\base\AdiantiStandardListTrait;

  public function __construct()
  {
    parent::__construct();

    $this->setDatabase('ibcvix');            // define a base de dados
    $this->setActiveRecord('Pessoas');   // Define o active record
    $this->setDefaultOrder('MONTH(data_nascimento), DAY(data_nascimento)', 'asc');
    //$this->addFilterField('MONTH(data_nascimento)', '=', 'mes'); // Campo de filtro, operador, campo do formulário
    $this->setLimit(0);
    $criteria = new TCriteria;

    $mes_atual = intval(date('m'));

    // Array com os nomes abreviados dos meses
    $meses = array(
      'jan', 'fev', 'mar', 'abr', 'mai', 'jun',
      'jul', 'ago', 'set', 'out', 'nov', 'dez'
    );


    $estaSemana = $this->getMondayAndSunday();

    $segunda = $estaSemana['segunda'];
    $domingo = $estaSemana['domingo'];
    $dia1 = $estaSemana['dia1'];
    $dia2 = $estaSemana['dia2'];
    $mes1 = $estaSemana['mes1'];
    $mes2 = $estaSemana['mes2'];

    //Cria checkbox para escolher a semana atual
    $div_sem = new TElement('div');
    $div_sem->style = "margin-top: 20px; margin-bottom: 0; padding-bottom: 0;text-align: left;";

    $sem_chk =  new TElement('input');
    $sem_chk->type = 'checkbox';

    $membro_chk =  new TElement('input');
    $membro_chk->type = 'checkbox';

    $membroclicado = isset($_GET['membro']) ? $_GET['membro'] : 0;

    switch ($membroclicado) {
      case 0:
        $membro = '1';

        $membro_chk->unchecked = 1;

        break;
      case 1:
        $membro = '0';
        $membro_chk->checked = 1;
        break;
    }

    $mesescolhido = isset($_GET['mes']) ? $_GET['mes'] : $mes_atual;


    // Criar os botões dos meses e adicionar à página
    $i = 1;

    foreach ($meses as $mes) {
      $CC = $this->AniversarianteMes($i);
      $t = $CC; //insere o total de aniversariantes do mês
      $botao[$i] = new TElement('button');
      if ($i == $mesescolhido) {
        $botao[$i]->class = 'mes-bto selected'; //muda a cor do mês selecionado

      } else {
        $botao[$i]->class = 'mes-bto';
      }
      // Adicionar ação ao botão
      $botao[$i]->onclick = "window.location.href = 'index.php?class=Aniversariantes&mes=$i&semanatual=0&membro=$membroclicado'";


      $botao[$i]->add($mes . "($t)"); // Adicionar o nome do mês ao botão
      $i++;
    };


    $_SESSION['semanatual'] = isset($_GET['semanatual']) ? $_GET['semanatual'] : 0;

    $semanatual = $_SESSION['semanatual'];

    switch ($_SESSION['semanatual']) {
      case 0:
        $semanaclicada = '1';
        $_SESSION['semanatual'] = '0';

        $sem_chk->unchecked = 1;

        break;
      case 1:
        $semanaclicada = '0';
        $_SESSION['semanatual'] = '1';
        $sem_chk->checked = 1;
        break;
    }





    if (isset($_GET['mes'])) {
      $_SESSION['mesescolhido'] = intval($_GET['mes']);
    } else {
      $_SESSION['mesescolhido'] = $mes_atual;
    }


    $sem_chk->onclick = "window.location.href = 'index.php?class=Aniversariantes&mes=$mesescolhido&membro=$membroclicado&semanatual=$semanaclicada';";
    $membro_chk->onclick = "window.location.href = 'index.php?class=Aniversariantes&mes=$mesescolhido&membro=$membro&semanatual=$semanatual';";

    $panel = new TPanelGroup("<span class = 'titulo-form'><strong>LISTA DE ANIVERSARIANTES POR MÊS</strong></span>");
    $panel->style = "height: 40%; text-align: center;";
    $container = new TVBox;
    $container->style = 'width: 100%';





    if ($mes1 == $mes2 && $semanatual == 1) {
      $criteria->add(new TFilter("DAY(data_nascimento)", "BETWEEN", "$dia1", "$dia2"));
      $criteria->add(new TFilter('MONTH(data_nascimento)', '=', $mes1));
      $consulta_sem = "window.location.href = 'index.php?class=Aniversariantes&mes=$mes_atual&membro=$membroclicado';";
    } else if ($mes1 != $mes2 && $semanatual == 1) {

      $criteria1 = new TCriteria; 
      $criteria2 = new TCriteria; 
      
      $criteria1->add(new TFilter("MONTH(data_nascimento)", "=", "$mes1"));
      $criteria1->add(new TFilter("DAY(data_nascimento)", ">=", "$dia1"));
      $criteria2->add(new TFilter("MONTH(data_nascimento)", "=", "$mes2"));
      $criteria2->add(new TFilter("DAY(data_nascimento)", "<=", "$dia2"));

      $criteria->add($criteria1, TExpression::OR_OPERATOR);
      $criteria->add($criteria2, TExpression::OR_OPERATOR);
      
      $consulta_sem = "window.location.href = 'index.php?class=Aniversariantes&mes=$mes_atual';";
    } else {

      $criteria->add(new TFilter('MONTH(data_nascimento)', '=', $mesescolhido));
    }

    if ($membroclicado == 1) {
      $criteria->add(new TFilter('id_categorias', 'BETWEEN', 1, 3));
    }



    $sem_txt = new TElement('label');
    $sem_txt->add("<span style='padding-right: 0.5vw'><strong>SEMANA ATUAL - $segunda a $domingo</strong></span>");

    $membro_txt = new TElement('label');
    $membro_txt->add("<span style='padding-left: 5vw'><strong>Membro: </strong></span>");

    $div_sem->add("$sem_txt $sem_chk $membro_txt $membro_chk");




    //Adiciona os botões se a semana atual não estiver selecionada
    if ($semanatual == 0) {
      for ($i = 1; $i < 13; $i++) {
        $panel->add($botao[$i]);
      }
    }


    $panel->add($div_sem);


    //Prepara a exibição de dados

    $criteria->add(new TFilter('id_categorias', '<>', '9'));
    $this->setCriteria($criteria);
    $criterio = $criteria->dump(); //Prepara envio dos filtros

    //$panel->add(var_dump($criterio));

    $panel2 = new TPanelGroup();

    $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
    $this->datagrid->width = '100%';

    // tornar rolável e definir a altura
    $this->datagrid->setHeight('55vh');
    $this->datagrid->makeScrollable();

    // adicionar as colunas
    $this->datagrid->addColumn(new TDataGridColumn('nome_completo',    'NOME',    'left', '55%'));
    $data_nascimento = $this->datagrid->addColumn(new TDataGridColumn('data_nascimento',    'DATA DE NASCIMENTO',    'center',   '25%'));
    $this->datagrid->addColumn(new TDataGridColumn('fone01',   'TELEFONE',   'left',   '20%'));


    $usr = new SystemGroup();
    if ($usr->GruposAcesso([1,3,4])) {
    $action1 = new TDataGridAction(['CadastraPessoa', 'onEdit'], ['id' => '{id}']);
    $this->datagrid->addAction($action1, 'Editar', 'fa:edit green');
    }
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


    $panel2->addHeaderActionLink('PDF', new TAction([$this, 'exportaGeral'], ['mes' => "$mesescolhido", 'semanatual' => "$semanatual", 'membro' => "$membro"]), 'far:file-pdf red');
    //$panel2->addHeaderActionLink('PDF', new TAction([$this, 'onExportPDF']), 'far:file-pdf red');
    $panel2->addHeaderActionLink('CSV', new TAction([$this, 'exportaCSV'], ['mes' => "$mesescolhido", 'semanatual' => "$semanatual", 'membro' => "$membro"]), 'fa:table blue');




    $this->datagrid->createModel();

    $panel2->add($this->datagrid);
    $container->add($panel);
    $container->add($panel2);

    parent::add($container);
  }



  public function exportaCSV($param)
  {
    TTransaction::open('ibcvix');
    $conn = TTransaction::get(); // obtém a conexão     

    TTransaction::open('ibcvix');
    $conn = TTransaction::get(); // obtém a conexão   

    //var_dump($criterio);

    $result = $this->pesquisa($param, $conn);

    // Verifica se a consulta retornou resultados
    if ($result->rowCount() > 0) {
      // Nome do arquivo CSV
      $this->excluiArquivo();

      $current_datetime = date('Y-m-d_H-i-s');
      $file = 'app/output/export_' . $current_datetime . '.csv';



      $handler = fopen($file, 'w+b');
      fwrite($handler, chr(0xEF) . chr(0xBB) . chr(0xBF)); // Adiciona a BOM para criar arquivo no formato UTF-8

      fputcsv($handler, array('DATA DE NASCIMENTO', 'NOME', 'CONTATO'), '|');
      foreach ($result as $row) {
        fputcsv($handler, array($row['data_formatada'], $row['nome_completo'], $row['fone01']), '|');
      }

      fclose($handler);
      parent::openFile($file);
    }

    // Fecha a conexão com o banco de dados
    TTransaction::close();
  }


  /*
  Calcula o total de aniversariantes do mês retornando o valor
  */
  public function AniversarianteMes($mes = null)
  {

    TTransaction::open('ibcvix');
    $criteria = new TCriteria;

    $membro = isset($_GET['membro']) ? $_GET['membro'] : 0;

    $criteria->add(new tFilter('MONTH(data_nascimento)', '=', $mes));
    $criteria->add(new tFilter('id_categorias', '<>', 9));

    if ($membro == 1) {
      $criteria->add(new tFilter('id_categorias', 'BETWEEN', 1, 3));
    }

    $repositorio = new TRepository('Pessoas');
    $count = $repositorio->count($criteria);

    TTransaction::close();

    return $count;
  }

  function getMondayAndSunday()
  {
    // Obtém o timestamp atual
    $timestamp = time();

    // Obtém o dia da semana (0 para domingo, 1 para segunda, ..., 6 para sábado)
    $dayOfWeek = date("w", $timestamp);

    // Calcula o número de dias para retroceder para chegar à segunda-feira
    $daysToMonday = $dayOfWeek == 0 ? 6 : $dayOfWeek - 1;

    // Calcula o timestamp da segunda-feira
    $mondayTimestamp = strtotime("-{$daysToMonday} days", $timestamp);

    // Calcula o timestamp do domingo subsequente (7 dias após a segunda-feira)
    $sundayTimestamp = strtotime("+6 days", $mondayTimestamp);

    // Formata as datas no formato dd/mm/yyyy
    $monday = date("d/m/Y", $mondayTimestamp);
    $sunday = date("d/m/Y", $sundayTimestamp);
    $dia1 = date("d", $mondayTimestamp);
    $dia2 = date("d", $sundayTimestamp);
    $mes1 = date("m", $sundayTimestamp);
    $mes2 = date("m", $sundayTimestamp);

    // Retorna um array com as datas
    return array('segunda' => $monday, 'domingo' => $sunday, 'dia1' => $dia1, 'dia2' => $dia2, 'mes1' => $mes1, 'mes2' => $mes2);
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

      //var_dump($criterio);

      $result = $this->pesquisa($param, $conn);

      //Cria corpo do relatório

      $html = <<<EOF
                  <style>
                  table {
                    border-collapse: collapse;
                    width: 100%;
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
                              
                            <th>DATA</td>
                            <th>NOME</td>
                            <th>TELEFONE</td>
                                
                            </tr>       
                            </thead>  
      EOF;


      foreach ($result as $row) {
        $html .= "<tr>";
        $html .= "<td style='width: 15%; text-align: center'>" . $row['data_formatada'] . "</td>";
        $html .= "<td style='width: 60%;'>" . $row['nome_completo'] . "</td>";
        $html .= "<td style='text-align: center'>" . $row['fone01'] . "</td>";
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

  public function pesquisa($param, $conn)
  {
    $estaSemana = $this->getMondayAndSunday();

    $dia1 = $estaSemana['dia1'];
    $dia2 = $estaSemana['dia2'];
    $mes1 = $estaSemana['mes1'];
    $mes2 = $estaSemana['mes2'];


    //String final a ser usada para pesquisa



    $criterio = '';
    $mes = $param['mes'];
    switch ($param['semanatual']) {
      case 0:
        $criterio = " MONTH(data_nascimento) = $mes ";
        break;

      case 1:

        if ($mes1 == $mes2) {
          $criterio = "DAY(data_nascimento) BETWEEN $dia1 and $dia2 ";
        } else {

          $criterio = "(MONTH(data_nascimento) = $mes1 and DAY(data_nascimento) >= $dia1) or ";
          $criterio .= "(MONTH(data_nascimento) = $mes2 and DAY(data_nascimento) <= $dia2) ";
        }

        break;
    }

    if ($param['membro'] == 1) {
      $criterio .=  "and id_categorias BETWEEN 1 and 3 ";
    }

    $criterio .= " and id_categorias <> 9 ";

    $txtsql = <<<EOF
    SELECT DATE_FORMAT(data_nascimento, '%d/%m/%Y') AS data_formatada, nome_completo, fone01 
    FROM pessoas 
    Where 

    $criterio
    
    ORDER BY MONTH(data_nascimento) asc, DAY(data_nascimento) asc;
    
    EOF;


    $result = $conn->query($txtsql);

    return $result;
  }
}
