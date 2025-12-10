<?php

use Adianti\Control\TPage;
use Adianti\Database\TCriteria;
use Adianti\Database\TTransaction;
use Adianti\Registry\TSession;
use Adianti\Widget\Datagrid\TDataGrid;
use Adianti\Widget\Dialog\TDialogIbcv;
use Adianti\Widget\Form\TButton;
use Adianti\Widget\Form\TCombo;
use Adianti\Widget\Form\TDate;
use Adianti\Widget\Form\TEntry;
use Adianti\Widget\Form\TForm;
use Adianti\Widget\Form\TLabel;
use Adianti\Widget\Form\TText;
use Adianti\Widget\Wrapper\TDBCombo;
use Adianti\Widget\Wrapper\TDBUniqueSearch;


/**
 * Cadastra Pessoa
 *
 * @version    1.0
 * @package    ibcvix
 * @subpackage control
 * @author     Antonio Affonso
 * @copyright  Copyright (c) 2024 - Igreja Batista Central de Vitória
 */

class InserePessoa extends TPage
{

    protected $dadosparentes; // datagrid com lista de parentes
    protected $titulogeral;
    // Adicione esta linha
    //protected $form; // form
    use Adianti\base\AdiantiStandardListTrait;
    /**
     * Form constructor
     * @param $param Request
     */
    public function __construct($param)
    {
        parent::__construct();




        // cria o formulário de identificação $this->form
        $this->form = new BootstrapFormBuilder('Cad_Pessoa');
        $this->form->generateAria(); // automatic aria-label
        $this->form->setFieldSizes('100%');

        $this->form->setProperty('style', 'margin:0;border:0;margin-bottom:30px;');
        $this->form->setClientValidation(true);


        // criar os campos do formulário

        $id = new TEntry('id');
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

        $categoria = new TDBUniqueSearch('id_categorias', 'ibcvix', 'Categorias', 'id', 'descricao');
        $categoria->setMinLength(0);

        $logradouro = new TEntry('logradouro');
        $cep = new TEntry('cep');
        $bairro = new TEntry('bairro');
        $numero = new TEntry('numero');

        $filter = new TCriteria;
        $filter->add(new TFilter('id', '<', '0'));
        //$cidade = new TDBCombo('id_cidade', 'ibcvix', 'CidadeEstado', 'id', 'nome','nome',$filter);
        $cidade = new TDBCombo('id_cidade', 'ibcvix', 'Cidades', 'id', 'nome', 'nome', $filter);



        $uf = new TDBCombo('id_estado', 'ibcvix', 'Estados', 'id', '{nome}');
        $uf->setChangeAction(new TAction([$this, 'onChangeEstado']));
        $observacao = new TText('obs');


        $cep->setExitAction(new TAction([$this, 'onExitCEP']));
        $cidade->enableSearch();
        $uf->enableSearch();
        $observacao->setSize('100%', 60);


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

            TTransaction::close();
        } else {
            $fotoatual = 'app/images/photos/silueta - X.png';
        }
        // Criar campo de imagem para exibir a foto
        $fotoField = new TImage($fotoatual);


        $fotoField->style = 'max-width: 7vw;min-width: 4vw';
        //$fotoField->setSize(150, 150); // Defina o tamanho da imagem conforme necessário

        $row = $this->form->addFields([new TLabel('Foto'), $fotoField], [new TLabel('Insira a foto'), $foto]);
        $row->layout = ['col-sm-2', 'col-sm-6'];


        $row = $this->form->addFields([new TLabel('Nome'), $nome], [new TLabel('Sobrenome'), $sobrenome], [new TLabel('Nome completo'), $nome_completo]);
        $row->layout = ['col-sm-3', 'col-sm-4', 'col-sm-5'];

        $row = $this->form->addFields([new TLabel('Apelido'), $apelido], [new TLabel('Sexo'), $genero], [new TLabel('Data de nascimento'), $data_nascimento], [new TLabel('Nacionalidade'), $nacionalidade]);
        $row->layout = ['col-sm-4 ', 'col-sm-2', 'col-sm-3', 'col-sm-3'];



        $this->form->addFields([new TLabel('Pai'), $pai], [new TLabel('Mãe'), $mae], [new TLabel('Estado Civil'), $estadocivil])
            ->layout = ['col-sm-5', 'col-sm-5', 'col-sm-2'];


        $row = $this->form->addFields([new TLabel('Cônjuge evangélico?'), $conjuge_cristao], [new TLabel('Cônjuge'), $nome_conjuge], [new TLabel('Categoria'), $categoria]);
        $row->layout = ['col-sm-2', 'col-sm-6',  'col-sm-4'];

        $row = $this->form->addFields([new TLabel('Observação'), $observacao]);



        $this->titulogeral = 'CADASTRA PESSOA';

        $this->form->setFormTitle("<span class = 'titulo-form'>$this->titulogeral</span>");



        // criar as ações do formulário
        $btn = $this->form->addAction(_t('Save'), new TAction([$this, 'onSave']), 'fa:save');
        $btn->class = 'btn btn-sm btn-primary';
        //$this->form->addActionLink(_t('New'),  new TAction([$this, 'onEdit']), 'fa:plus green');

        $nome->addValidation('nome', new TRequiredValidator);
        $sobrenome->addValidation('sobrenome', new TRequiredValidator);
        $nome->setExitAction(new TAction([$this, 'onNome']));
        $sobrenome->setExitAction(new TAction([$this, 'onNome']));



        // Box vertical contendo formulário
        $container = new TVBox;
        $container->style = 'width: 100%';
        // $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add($this->form);



        parent::add($container);
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
     * Action to be executed when the user changes the state
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
                TDBCombo::reloadFromModel('Cad_Pessoa', 'id_cidade', 'ibcvix', 'Cidades', 'id', "{nome} ({$estado->uf})", 'nome', $criteria, TRUE);
            } else {
                TCombo::clearField('Cad_Pessoa', 'id_cidade');
            }

            TTransaction::close();
        } catch (Exception $e) {
            new TMessage('error', $e->getMessage());
        }
    }

    /**
     * Autocompleta outros campos a partir do CNPJ
     */
    public static function onExitCNPJ($param)
    {
        session_write_close();

        try {
            $cnpj = preg_replace('/[^0-9]/', '', $param['codigo_nacional']);
            $url  = 'http://receitaws.com.br/v1/cnpj/' . $cnpj;

            $content = @file_get_contents($url);

            if ($content !== false) {
                $cnpj_data = json_decode($content);


                $data = new stdClass;
                if (is_object($cnpj_data) && $cnpj_data->status !== 'ERROR') {
                    $data->tipo = 'J';
                    $data->nome = $cnpj_data->nome;
                    $data->nome_fantasia = !empty($cnpj_data->fantasia) ? $cnpj_data->fantasia : $cnpj_data->nome;

                    if (empty($param['cep'])) {
                        $data->cep = $cnpj_data->cep;
                        $data->numero = $cnpj_data->numero;
                    }

                    if (empty($param['fone01'])) {
                        $data->fone = $cnpj_data->telefone;
                    }

                    if (empty($param['email'])) {
                        $data->email = $cnpj_data->email;
                    }

                    TForm::sendData('Cad_Pessoa', $data, false, true);
                } else {
                    $data->nome = '';
                    $data->nome_fantasia = '';
                    $data->cep = '';
                    $data->numero = '';
                    $data->telefone = '';
                    $data->email = '';
                    TForm::sendData('Cad_Pessoa', $data, false, true);
                }
            }
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

    public function onEdit()
    {
    }

    /**
     * Save form data
     * @param $param Request
     */
    public function onSave($param = null)
    {

        try {

            $data = $this->form->getData(); //lê os dados do formulário

            $id_atual = $param['id'];


            TTransaction::open('ibcvix');
            $conn = TTransaction::get(); // obtém a conexão     
            $pesquisa = "SELECT MAX(id) AS maiorvalor FROM pessoas";
            $result = $conn->query($pesquisa);

            foreach ($result as $row) {
                $ultimoid = $row['maiorvalor'];
            }

            $param['id'] = $ultimoid + 1;

            $reg = new Pessoas;
            //$reg->id = $param['id'];
            $reg->nome = $param['nome'];
            $reg->sobrenome = $param['sobrenome'];
            $reg->nome_completo = $param['nome_completo'];
            $reg->apelido = $param['apelido'];
            $reg->genero = $param['genero'];
            $reg->data_nascimento = date('Y-m-d',strtotime($param['data_nascimento']));
            $reg->id_nacionalidade = $param['id_nacionalidade'];
            $reg->id_estado_civil = $param['id_estado_civil'];
            $reg->pai = $param['pai'];
            $reg->mae = $param['mae'];
            $reg->id_categorias = $param['id_categorias'];
            $reg->conjuge_cristao = $param['conjuge_cristao'];
            $reg->conjuge = $param['conjuge'];
            $reg->obs = $param['obs'];
            $reg->foto = $param['foto'];

            $reg->store();

            
            if ($reg->foto) {
                $source_file   = 'tmp/' . $reg->foto;
                $target_file   = 'app/images/photos/' . $reg->foto;
                $finfo         = new finfo(FILEINFO_MIME_TYPE);


                if (file_exists($source_file) and $finfo->file($source_file) == 'image/jpeg') {
                    // move to the target directory
                    rename($source_file, $target_file);
                }
            }

            AdiantiCoreApplication::loadPage('CadastraPessoa', 'onEdit', ['key' => $param['id'], 'id' => $param['id']]);


            TTransaction::close();
            //new TMessage('info', 'REGISTRO SALVO.');
            TToast::show('success', 'CADASTRO INSERIDO.', 'bottom right', 'far:check-circle');
        } catch (Exception $e) {
            new TMessage('error', $e->getMessage()); // exibe mensagem de exceção
            $this->form->setData($this->form->getData()); // preenche form
            TTransaction::rollback(); // desfaz operações da transação
        }
    }
}
