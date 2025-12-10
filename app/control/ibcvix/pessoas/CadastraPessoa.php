<?php

use Adianti\Control\TPage;
use Adianti\Database\TCriteria;
use Adianti\Database\TTransaction;
use Adianti\Widget\Datagrid\TDataGrid;
use Adianti\Widget\Form\TButton;
use Adianti\Widget\Form\TCombo;
use Adianti\Widget\Form\TDate;
use Adianti\Widget\Form\TEntry;
use Adianti\Widget\Form\TForm;
use Adianti\Widget\Form\TLabel;
use Adianti\Widget\Form\TText;
use Adianti\Widget\Wrapper\TDBCombo;
use Adianti\Widget\Wrapper\TDBUniqueSearch;
use Adianti\Core\AdiantiCoreApplication;
use Adianti\Registry\TSession;
use Adianti\Widget\Form\TFile;

/**
 * Cadastra Pessoa
 *
 * @version    1.0
 * @package    ibcvix
 * @subpackage control
 * @author     Antonio Affonso
 * @copyright  Copyright (c) 2024 - Igreja Batista Central de Vitória
 */

class CadastraPessoa extends TPage
{

    protected $notebook;
    protected $dadosparentes; // datagrid com lista de parentes
    protected $titulogeral;
    protected $id_atual;
    protected $id_estado_atual;
    // Adicione esta linha
    //protected $form; // form
    use Adianti\base\AdiantiStandardListTrait;

    /**
     * Form constructor
     * @param $param Request
     */
    public function __construct($param)
    {
        parent::__construct($param);

        // cria o formulário de identificação $this->form
        $this->form = new BootstrapFormBuilder('Cad_Pessoa');
        $this->form->generateAria(); // automatic aria-label
        $this->form->setFieldSizes('100%');

        $this->form->setProperty('style', 'margin:0;border:0;margin-bottom:30px;');
        $this->form->setClientValidation(true);

        if (isset($_GET['id'])) {
            $this->id_atual = $_GET['id'];
        } else {
            if (empty($this->id_atual)) {
                $this->id_atual = '';
            }
        }
        // criar os campos do formulário
        //CadastraParente::onClose();
        $id = new TEntry('id');
        $id_system_user = new TEntry('id_system_user');
        $nome = new TEntry('nome');
        $sobrenome = new TEntry('sobrenome');
        $nome_completo = new TEntry('nome_completo');
        $nome_completo->setEditable(false);

        $apelido = new TEntry('apelido');

        $genero = new TCombo('genero');
        $genero->addItems(['M' => 'Masculino', 'F' => 'Feminino']);


        $data_nascimento = new TDate('data_nascimento');
        $data_nascimento->setMask('dd/mm/yyyy');
        $data_nascimento->setDatabaseMask('yyyy-mm-dd');

        $nacionalidade = new TDBUniqueSearch('id_nacionalidade', 'ibcvix', 'Nacionalidade', 'id', 'nome');
        $nacionalidade->setMinLength(0);


        // Adicione o campo de upload de foto
        $foto = new TFile('foto');
        $foto->setAllowedExtensions(['jpg', 'jpeg', 'png', 'gif']);
        //$foto->enableFileHandling();
        $foto->setDisplayMode('file');


        $estadocivil = new TDBUniqueSearch('id_estado_civil', 'ibcvix', 'EstadoCivil', 'id', 'descricao');
        $estadocivil->setMinLength(0);

        $pai = new TEntry('pai');
        $mae = new TEntry('mae');
        $conjuge_cristao = new TCombo('conjuge_cristao');
        $conjuge_cristao->addItems(['S' => 'Sim', 'N' => 'Não']);

        $nome_conjuge = new TEntry('conjuge');

        $fone01 = new TEntry('fone01');
        $fone02 = new TEntry('fone02');
        $email01 = new TEntry('email01');
        $categoria = new TDBUniqueSearch('id_categorias', 'ibcvix', 'Categorias', 'id', 'descricao');
        $categoria->setMinLength(0);

        $logradouro = new TEntry('logradouro');
        $cep = new TEntry('cep');
        $bairro = new TEntry('bairro');
        $numero = new TEntry('numero');

        $uf = new TDBCombo('id_estado', 'ibcvix', 'Estados', 'id', '{nome}');
        $uf->setChangeAction(new TAction([$this, 'onChangeEstado']));


        /*$filter = new TCriteria;
        $filter->add(new TFilter('id_estado', '=', $this->id_estado_atual));
        */
        $filter = new TCriteria;
        $filter->add(new TFilter('id', '<', '0'));
        $cidade = new TDBCombo('id_cidade', 'ibcvix', 'Cidades', 'id', 'nome', 'nome', $filter);


        $observacao = new TText('obs');
        $complemento = new TEntry('complemento');


        $data_conversao = new TDate('data_conversao');
        $data_conversao->setMask('dd/mm/yyyy');
        $data_conversao->setDatabaseMask('yyyy-mm-dd');

        $batizado = new TCombo('batizado');
        $batizado->addItems(['S' => 'Sim', 'N' => 'Não']);


        $data_batismo = new TDate('data_batismo');
        $data_batismo->setMask('dd/mm/yyyy');
        $data_batismo->setDatabaseMask('yyyy-mm-dd');

        $ministro_batismo = new TEntry('ministro_batismo');

        $igreja_anterior = new TEntry('igreja_anterior');

        $notas = new TText('notas');


        $cep->setExitAction(new TAction([$this, 'onExitCEP']));
        $cidade->enableSearch();
        $uf->enableSearch();
        $observacao->setSize('100%', 60);

        //$this->notebook = new TNotebook;


        // adicionar os campos

        //Cria a aba de identificação
        $pag1 = $this->form->appendPage('IDENTIFICAÇÃO');


        //Possibilita controlar a aba corrente mantendo após salvar
        $this->form->addFields([new THidden('current_tab')]);
        $this->form->setTabFunction("$('[name=current_tab]').val($(this).attr('data-current_page'));"); //Captura o valor da aba corrente


        $row = $this->form->addFields([new TLabel('ID')], [$id]);
        /*O id precisa existir para ser controlado automaticamente pelo framework*/
        $row->style = 'Display: none'; //Mantem id invisível;
        // Adicione o campo de foto no formulário

        $row = $this->form->addFields([new TLabel('ID USUÁRIO')], [$id_system_user]);
        $row->style = 'Display: none';



        if (isset($param['key'])) {
            $fotoatual = 'app/images/photos/X.png';

            TTransaction::open('ibcvix');
            $pessoa = new Pessoas($param['key']);

            if ($pessoa->foto) {
                //$foto = json_decode(urldecode($pessoa->foto));
                $fotoatual = 'app/images/photos/' . $pessoa->foto;
            } else {
                if ($pessoa->genero == 'F') {
                    $fotoatual = 'app/images/photos/silueta - feminina.jpg';
                } else if ($pessoa->genero == 'M') {
                    $fotoatual = 'app/images/photos/silueta - masculina.jpg';
                }
            }

            if ($pessoa->id_system_user) {
                $usuario = $pessoa->id_system_user;
            }

            $grupo_usuario = SystemUserGroup::where('system_user_id', '=', $pessoa->id_system_user)->first();

            if ($grupo_usuario) {
                $id_grp_usuario = $grupo_usuario->system_group_id;
                //var_dump($grupo_usuario->system_group_id);
            }

            if (!isset($id_grp_usuario)) {
            } else {
                $id_grp_usuario = 2;
            }


            TTransaction::close();
        } else {
            $fotoatual = 'app/images/photos/silueta - X.png';
        }
        // Criar campo de imagem para exibir a foto
        $fotoField = new TImage($fotoatual);


        $fotoField->style = 'max-width: 7vw;min-width: 4vw';
        //$fotoField->setSize(150, 150); // Defina o tamanho da imagem conforme necessário



        $row = $this->form->addFields([$fotoField], [$foto]);
        $row->layout = ['col-sm-2', 'col-sm-3'];


        $row = $this->form->addFields([new TLabel('Nome'), $nome], [new TLabel('Sobrenome'), $sobrenome], [new TLabel('Nome completo'), $nome_completo], [new TLabel('Apelido'), $apelido]);
        $row->layout = ['col-sm-2', 'col-sm-3', 'col-sm-4', 'col-sm-3'];

        $row = $this->form->addFields([new TLabel('Sexo'), $genero], [new TLabel('Nascimento'), $data_nascimento], [new TLabel('Nacionalidade'), $nacionalidade], [new TLabel('Estado Civil'), $estadocivil], [new TLabel('Categoria'), $categoria], [new TLabel('Cônjuge evangélico?'), $conjuge_cristao]);
        //$row->layout = ['col-sm-2', 'col-sm-2', 'col-sm-2', 'col-sm-2', 'col-sm-2'];



        $row = $this->form->addFields([new TLabel('Cônjuge'), $nome_conjuge], [new TLabel('Pai'), $pai], [new TLabel('Mãe'), $mae]);
        $row->layout = ['col-sm-4', 'col-sm-4', 'col-sm-4'];


        $row = $this->form->addFields([new TLabel('Observação'), $observacao]);

        //cria a aba de Contato e Endereço
        $pag2 = $this->form->appendPage('CONTATO E ENDEREÇO');



        $row = $this->form->addFields([new TLabel('Fone 1'), $fone01], [new TLabel('Fone 2'), $fone02], [new TLabel('E-mail'), $email01]);
        $row->layout = ['col-sm-3', 'col-sm-3',  'col-sm-6'];

        $this->form->addContent([new TFormSeparator('Endereço')]);
        $row = $this->form->addFields([new TLabel('Cep'), $cep], [new TLabel('Logradouro'), $logradouro], [new TLabel('Número'), $numero], [new TLabel('Bairro'), $bairro]);
        $row->layout = ['col-sm-2', 'col-sm-5', 'col-sm-2', 'col-sm-3'];

        $row = $this->form->addFields([new TLabel('Complemento'), $complemento], [new TLabel('Estado'), $uf], [new TLabel('Cidade'), $cidade]);
        $row->layout = ['col-sm-6', 'col-sm-2', 'col-sm-4'];





        /************************************************************* 
         *        Inserir uma condição se for membro ou candidato
         *       Caso não seja visitante ou inscrito apenas, não preciosa mostrar a aba
         **************************************************************/


        //cria a aba de Dados eclesiásticos
        $pag3 = $this->form->appendPage('DADOS ECLESIÁSTICOS');


        $row = $this->form->addFields([new TLabel('Conversão'), $data_conversao], [new TLabel('Batizado?'), $batizado], [new TLabel('Batismo'), $data_batismo], [new TLabel('Ministro que batizou'), $ministro_batismo], [new TLabel('Igreja anterior'), $igreja_anterior]);
        $row->layout = ['col-sm-2', 'col-sm-2', 'col-sm-2', 'col-sm-3', 'col-sm-3'];

        $this->form->addFields([new TLabel('Notas'), $notas])->layout = ['col-sm-12'];

        if (isset($_GET['tab'])) {
            $this->form->setCurrentPage($_GET['tab']);
        }
        if (isset($_SESSION['pag_corrente'])) {
            $this->form->setCurrentPage($_SESSION['pag_corrente']);
        }


        $id->setEditable(FALSE);
        $nome->addValidation('nome', new TRequiredValidator);
        $sobrenome->addValidation('sobrenome', new TRequiredValidator);
        $nome->setExitAction(new TAction([$this, 'onNome']));
        $sobrenome->setExitAction(new TAction([$this, 'onNome']));



        //cria a aba de familiares
        $pag4 = $this->form->appendPage('FAMILIARES');



        $form_familia = new TForm('form_familia');


        $familiar = new TButton('inserir');

        $familiar->setAction(new TAction(['CadastraParente', 'onEdit'], ['key' => $this->id_atual, 'id' => $this->id_atual]), 'INCLUIR');


        //$this->form->addActionLink(_t('New'), new TAction(['ContratoForm', 'onEdit'], ['register_state' => 'false']), 'fa:plus green');

        $familiar->setImage('fa:plus white');
        $familiar->setProperty('class', 'btn btn-secondary');


        //$button = new TActionLink('INCLUIR', new TAction([$this, 'onInputDialog']));
        //$button->class='btn btn-default';

        $panel = new TPanelGroup('');
        $form_familia->add($familiar);
        $this->dadosparentes = new BootstrapDatagridWrapper(new TDataGrid);

        // criar as colunas do datagrid
        $id_pessoa_parente       = new TDataGridColumn('id',    'id',    'center', '10%');
        $id_pessoa       = new TDataGridColumn('id_pessoa',    'Pessoa',    'left',   '30%');
        $id_parente       = new TDataGridColumn('id_parente',    'Parente',    'left',   '30%');
        $nome       = new TDataGridColumn('nome',    'Parente',    'left', '50%');
        $parentesco      = new TDataGridColumn('parentesco',   'Parentesco',   'left', '30%');


        // adicionar as colunas à datagrid
        //$this->dadosparentes->addColumn($id_pessoa_parente);
        //        $this->dadosparentes->addColumn($id_pessoa);
        $this->dadosparentes->addColumn($nome);
        $this->dadosparentes->addColumn($parentesco);

        $usr = new SystemGroup();
        if ( $usr->GruposAcesso([1,3,4])) {
            $action1 = new TDataGridAction(['CadastraPessoa', 'onEdit'], ['id' => '{id_parente}']);
            $this->dadosparentes->addAction($action1, 'Editar', 'fa:edit green');
        }
        $action2 = new TDataGridAction([$this, 'onExcluiParente'], ['id_parente' => '{id_parente}', 'id_pessoa' => '{id_pessoa}']);
        $this->dadosparentes->addAction($action2, 'Excluir', 'fa:eraser red');

        $this->titulogeral = 'CADASTRA PESSOA';
        if (isset($_GET['id'])) {
            $this->titulogeral = mb_strtoupper($this->retornaNome($_GET['id']), 'UTF-8');
        } else if (isset($param['id'])) {
            $this->titulogeral = mb_strtoupper($this->retornaNome($param['id']), 'UTF-8');
        }

        $this->form->setFormTitle("<span class = 'titulo-form'>$this->titulogeral</span>");

        $this->dadosparentes->createModel();

        $panel->add($form_familia);


        $form_familia->setFields([$familiar]);

        $panel->add($this->dadosparentes);

        $this->form->addFields([$panel]);


        // criar as ações do formulário
        $btn = $this->form->addAction('Salvar', new TAction([$this, 'onSave']), 'fa:save');
        $btn->class = 'btn btn-sm btn-primary';
        $this->form->addActionLink(_t('New'),  new TAction(['InserePessoa', 'onEdit']), 'fa:plus green');

        if (empty($usuario)) {
            $btn = $this->form->addAction('Criar usuário', new TAction(['NovoUsuario', 'onEdit']), 'fa:plus black');
            $btn->class = 'btn btn-sm btn-info';
        }


        // Box vertical contendo formulário
        $container = new TVBox;
        $container->style = 'width: 100%';
        // $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add($this->form);


        TTransaction::close();
        parent::add($container);
    }

    function onCarregaParente($id)
    {
        $this->dadosparentes->clear();
        try {

            TTransaction::open('permission'); // abre uma transação
            $conn = TTransaction::get(); // obtém a conexão

            $pessoa = Pessoas::find($id);

            if ($id) {

                //$id = $param['key'];
                $pesquisaparente = <<<EOF
                    SELECT 
                    pp.id, 
                    pp.id_pessoa, 
                    pp.id_parente, 
                    parente.nome_completo AS nome, 
                    pa.descricao AS parentesco
                FROM 
                    pessoa_parentesco AS pp
                LEFT JOIN 
                    pessoas AS parente ON pp.id_parente = parente.id
                LEFT JOIN 
                    parentesco AS pa ON pp.id_parentesco = pa.id
                WHERE 
                    pp.id_pessoa = $id
                Order by hierarquia

        EOF;

                $result = $conn->query($pesquisaparente);

                foreach ($result as $row) // exibe os resultados
                {
                    $item = new stdClass;
                    $item->id = $row['id'];
                    $item->id_parente = $row['id_parente'];
                    $item->id_pessoa = $row['id_pessoa'];
                    $item->nome = $row['nome'];
                    $item->parentesco = $row['parentesco'];
                    $this->dadosparentes->addItem($item);
                }
            }


            TTransaction::close(); // fecha a transação.
        } catch (Exception $e) {
            new TMessage('error', $e->getMessage());
        }
    }


    /**
     * Clear form data
     * @param $param Request
     */
    public function onClear($param)
    {
        $this->form->clear(TRUE);
    }



    /**
     * Carregar o objeto para formar os dados
     * @param $param Request
     */
    public function onEdit($param)
    {
        try {
            if (isset($param['key'])) {
                // obter o parâmetro $key
                $key = $param['key'];
                TTransaction::close();

                // abrir uma transação com o banco de dados
                TTransaction::open('ibcvix');

                $class = new Pessoas($key);
                $this->form->setData($class);


                $cidade = Cidades::find($class->id_cidade);

                $data = new stdClass;
                if ($cidade) {

                    $data->id_estado = $cidade->id_estado;
                    $data->id_cidade = $class->id_cidade;
                    TForm::sendData('Cad_Pessoa', $data);
                } else {
                    $data->id_estado = null;
                    $data->id_cidade = null;
                    TForm::sendData('Cad_Pessoa', $data);
                }


                // fechar a transação
                TTransaction::close();

                $this->onReload($param);
                $this->onCarregaParente($param['key']);
            } else {
                $this->form->clear();
            }
        } catch (Exception $e) // in case of exception
        {
            $this->form->setData($class);
            // shows the exception error message
            new TMessage('error', $e->getMessage());
            // undo all pending operations
            TTransaction::rollback();
        }
    }

    /**
     * Ação a ser executada quando o usuário alterar o estado
     * @param $param Action parameters
     */
    public static function onChangeEstado($param)
    {
        try {

            TTransaction::open('ibcvix');
            if (!empty($param['id_estado'])) {
                $estado = Estados::where('id', '=', $param['id_estado'])->first();
                $criteria = TCriteria::create(['id_estado' => $param['id_estado']]);

                // formname, field, database, model, key, value, ordercolumn = NULL, criteria = NULL, startEmpty = FALSE
                TDBCombo::reloadFromModel('Cad_Pessoa', 'id_cidade', 'ibcvix', 'Cidades', 'id', "{nome}", 'nome', $criteria, TRUE);
            } else {
                TCombo::clearField('Cad_Pessoa', 'id_cidade');
            }

            TTransaction::close();
        } catch (Exception $e) {
            new TMessage('error', $e->getMessage());
        }
    }

    /**
     * Autocompleta outros campos a partir do CEP
     */
    public static function onExitCEP($param)
    {
        session_write_close();

        try {

            $logradouro = $param['logradouro'] ?? null;
            //$bairro = $param['bairro'] ?? null;

            if (!empty($logradouro)) {
                // Se logradouro já estiver preenchido não faz nada
                return;
            }

            $cep = preg_replace('/[^0-9]/', '', $param['cep']);
            $url = 'https://viacep.com.br/ws/' . $cep . '/json/';

            $content = @file_get_contents($url);

            if ($content !== false) {
                $cep_data = json_decode($content);

                $data = new stdClass;
                if (is_object($cep_data) && empty($cep_data->erro)) {
                    TTransaction::open('ibcvix');
                    $estado = Estados::where('uf', '=', $cep_data->uf)->first();
                    $cidade = Cidades::where('id_ibge', '=', $cep_data->ibge)->first();
                    TTransaction::close();

                    $data->logradouro  = $cep_data->logradouro;
                    $data->complemento = $cep_data->complemento;
                    $data->bairro      = $cep_data->bairro;
                    $data->id_estado   = $estado->id ?? '';
                    $data->id_cidade   = $cidade->id ?? '';

                    TForm::sendData('Cad_Pessoa', $data, false, true);
                } else {
                    $data->logradouro  = '';
                    $data->complemento = '';
                    $data->bairro      = '';
                    $data->id_estado   = '';
                    $data->id_cidade   = '';

                    TForm::sendData('Cad_Pessoa', $data, false, true);
                }
            }
        } catch (Exception $e) {
            new TMessage('error', $e->getMessage());
        }
    }


    public static function onNome($param)
    {

        $data = new stdClass;
        $param['nome_completo'] = $param['nome'] . ' ' . $param['sobrenome'];

        TForm::sendData('Cad_Pessoa', $param, false, false);
    }


    /**
     * Closes window
     */
    public static function onClose()
    {
        parent::closeWindow();
    }

    public function onFamilia($param)
    {
        $i = intval($param['current_tab']);
    }



    public static function retornaNome($id)
    {
        TTransaction::open('ibcvix');

        $Pessoa = Pessoas::find($id);
        if ($Pessoa instanceof Pessoas) {
            return $Pessoa->nome_completo;
        } else {
            return '';
        }
    }

    public function onSave($param)
    {
        try {
            $i = intval($param['current_tab']);
            $this->id_atual = $param['id'];

            $this->activeRecord = 'Pessoas';
            // open a transaction with database
            TTransaction::open('ibcvix');

            // get the form data
            $object = $this->form->getData($this->activeRecord);
            //$object->foto = json_decode(urldecode($object->foto));

            // validate data
            $this->form->validate();


            // stores the object
            $object->store();


            if ($object->foto) {
                $source_file   = 'tmp/' . $object->foto;
                $target_file   = 'app/images/photos/' . $object->foto;
                $finfo         = new finfo(FILEINFO_MIME_TYPE);


                if (file_exists($source_file) and $finfo->file($source_file) == 'image/jpeg') {
                    // move to the target directory
                    rename($source_file, $target_file);
                }
            }

            //$this->form->setData($object);

            if (!empty($this->afterSaveCallback)) {
                $callback = $this->afterSaveCallback;
                $callback($object, $this->form->getData());
            }

            // fill the form with the active record data

            AdiantiCoreApplication::loadPage('CadastraPessoa', 'onEdit', ['key' => $this->id_atual, 'id' => $this->id_atual, 'tab' => $i]);
            // close the transaction
            TTransaction::close();

            // shows the success message
            if (isset($this->useMessages) and $this->useMessages === false) {
                AdiantiCoreApplication::loadPageURL($this->afterSaveAction->serialize());
            } else {
                TToast::show('success', 'Registro atualizado', 'bottom right', 'far:check-circle');
            }

            return $object;
        } catch (Exception $e) // in case of exception
        {
            // get the form data
            $object = $this->form->getData();

            // fill the form with the active record data
            $this->form->setData($object);

            // shows the exception error message
            new TMessage('error', $e->getMessage());

            // undo all pending operations
            TTransaction::rollback();
        }
    }

    public static function onExcluiParente($param)
    {
        try {


            TTransaction::open('ibcvix');
            $PessoaParentesco = new PessoaParentesco;
            //$PessoaParentesco->delete($param['id_pessoa_parente']); // exclui o objeto sem carregar

            $excluipessoa = new PessoaParentesco;
            $excluipessoa->onExcluiParente($param['id_pessoa'], $param['id_parente']);

            TToast::show('success', 'Registro excluído com sucesso.', 'bottom right', 'far:check-circle');
            TTransaction::close();
            AdiantiCoreApplication::loadPage('CadastraPessoa', 'onEdit', ['key' => $param['id_pessoa'], 'id' => $param['id_pessoa'], 'tab' => '3']);
        } catch (Exception $e) // in case of exception
        {
            // get the form data
            $object = $this->form->getData();

            // fill the form with the active record data
            $this->form->setData($object);

            // shows the exception error message
            new TMessage('error', $e->getMessage());

            // undo all pending operations
            TTransaction::rollback();
        }
    }

    public function onIncluiUsuario()
    {
        $data = $this->form->getData();
        $this->form->setData($data);
        TTransaction::open('ibcvix');
        $pessoa = new Pessoas($data->id);

        TTransaction::close();
        if($pessoa) {
            if($pessoa->email='') {
                new TMessage('error', 'DIGITE E SALVE O E-MAIL DA PESSOA.');
                
            }

            AdiantiCoreApplication::loadPage('NovoUsuario', 'onEdit', ['key' => $data->id, 'id' => $data->id]);


        } else {
            new TMessage('error', 'PESSOA NÃO CADASTRADA.');
        }


    }
}
