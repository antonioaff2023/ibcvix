<?php

use Adianti\Control\TWindow;
use Adianti\Database\TCriteria;
use Adianti\Database\TTransaction;
use Adianti\Widget\Dialog\TDialogIbcv;
use Adianti\Widget\Dialog\TInputDialog;
use Adianti\Widget\Form\TEntry;
use Adianti\Widget\Form\TLabel;
use Adianti\Widget\Wrapper\TDBUniqueSearch;


/**
 * CadastraUsuario
 *
 * @version    1.0
 * @package    ibcvix
 * @subpackage control
 * @author     Antonio Affonso
 * @copyright  Copyright (c) 2024 Igreja Batista Central de Vitória
 *  
 * A pirncipal função é criar um usuário utilizando os dados de uma pessoa já cadastrada. E gravar o ID de usuário em campo específico da pessoa para manter o relacionamento.
 */
class NovoUsuario extends TPage
{
    protected $form; // form
    protected $fieldlist;

    use Adianti\Base\AdiantiStandardFormTrait;

    /**
     * Class constructor
     * Cria a página e o formulário de registro
     */
    function __construct($param)
    {
        parent::__construct($param);

        // Criar formulário

        parent::setTargetContainer('adianti_right_panel');
        $this->setAfterSaveAction(new TAction(['CadastraPessoa', 'onReload'], ['register_state' => 'true']));

        //$this->setDatabase('ibcvix');              // defines the database
        //$this->setActiveRecord('Pessoas');    

        $this->form = new BootstrapFormBuilder('form_cad_user');
        $this->form->setFormTitle("<span class = 'titulo-form'>NOVO USUÁRIO</span>");

        // Cria os campos para o formulário
        $id            = new TEntry('id'); //Não aparece na tela

        $name          = new TEntry('name');
        $name->setEditable(false);
        $phone         = new TEntry('phone');
        $phone->setEditable(false);
        $address       = new TEntry('address'); //Este campo será uma concatenação de vários campos da tabela Pessoas
        $address->setEditable(false);
        $function_name = new TEntry('function_name'); //Será sempre 'Usuário'. A mudança só pode ser feito pelo administrador.
        $function_name->setEditable(false);
        $login         = new TEntry('login'); //Único campo de fato criado. Todos os outros serão herdados da tabela Pessoas
        $login->setsize('50%');
        $email         = new TEntry('email'); //Este campo ser alterado, mas será herdado da tabela Pessoas
        $email->setsize('70%');

        /*Os próximos campos serão idênticos aos do usuário que está criando o novo. Não aparecerão na tela são eles:
                unit_id, groups, frontpage_id, units*/

        $id_lbl             = new TLabel('id');
        $name_lbl           = new TLabel('Nome');
        $login_lbl          = new TLabel('Login');
        $email_lbl          = new TLabel('E-mail');
        $phone_lbl          = new TLabel('Telefone');
        $address_lbl        = new TLabel('Endereço');
        $function_name_lbl  = new TLabel('Função');

        $row = $this->form->addFields([$id_lbl], [$id]);
        $row->style = 'display: none';

        $row = $this->form->addFields([$name_lbl], [$name]);
        $row = $this->form->addFields([$phone_lbl], [$phone]);
        //$row->style = 'display: none';
        $row = $this->form->addFields([$address_lbl], [$address]);
        //$row->style = 'display: none';
        $row = $this->form->addFields([$function_name_lbl], [$function_name]);
        //$row->style = 'display: none';

        $row = $this->form->addFields([$email_lbl], [$email]);
        $row = $this->form->addFields([$login_lbl], [$login]);

        
        $this->form->addAction('Salvar', new TAction([$this, 'onConfirm1'], ['key' => $param['id']]), 'fa:save green');
        $this->form->addHeaderActionLink(_t('Close'), new TAction([$this, 'onClose']), 'fa:times red');


        $container = new TVBox;
        $container->style = 'width: 100%';
        // $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add($this->form);

        parent::add($container);

        /*
        $window = TWindow::create('', 1, null);
        // carrega um html
        $html = new THtmlRenderer('app/resources/modal_view.html');
        $replaces = [];
        $replaces['formulario'] = $this->form;

        // habilita seção main, passando replaces
        $html->enableSection('main', $replaces);
        $window->add($html);
        $window->show();*/
    }


    public function onConfirm1($param)
    {
        try {

            $data = $this->form->getData();
            TTransaction::open('ibcvix');
            $salvaUsuario = new SystemUser;
            $salvaUsuario->fromArray((array) $data); // preenche os atributos
            $salvaUsuario->system_unit_id = 1;
            $salvaUsuario->active = 'Y';
            $salvaUsuario->accepted_term_policy = 'S';
            $salvaUsuario->frontpage_id =55;
            $salvaUsuario->store(); // armazena Usuário
            $salvaUsuario->addSystemUserGroup( new SystemGroup([2]) );


            $pessoa = Pessoas::find($param['key']);
            $pessoa->id_system_user = $salvaUsuario->id;
            $pessoa->store();
            

            

           TTransaction::close();
           TToast::show('success', 'Usuário criado com sucesso.', 'top center', 'far:check-circle');
           AdiantiCoreApplication::loadPage('CadastraPessoa', 'onEdit', ['key' => $param['key'], 'id' => $param['key'], 'tab' => '0']);

        } catch (Exception $e) {
            TTransaction::close();
            new TMessage('error', $e->getMessage()); // exibe mensagem de exceção
        }
    }

    /**
     * Close side panel
     */
    public static function onClose($param)
    {
        TScript::create("Template.closeRightPanel()");
    }

    public function onEdit($param)
    {
        // monta objeto para enviar dados ao formulário
        TTransaction::open('ibcvix');
        $Pessoa = new Pessoas($param['id']);

        $login = $this->criarLogin($Pessoa->nome_completo);
        $usuario =  SystemUser::where('login', '=', $login)->load();

        if ($usuario) {
            TToast::show('info', 'Usuário não pode ser criado pois já existe com os nomes sugeridos', 'top center', 'far:check-circle');
            //exit;
        }


        $obj = new StdClass;
        $obj->name = $Pessoa->nome_completo;
        $obj->email = $Pessoa->email01;
        $obj->address =  $Pessoa->endereco_extenso();
        $obj->phone = $Pessoa->fone01;
        $obj->function_name = 'Usuário';
        $obj->login = $login;
        TTransaction::close();

        // preenche o formulário com os atributos do objeto
        TForm::sendData('form_cad_user', $obj);
    }


    public function criarLogin($nomeCompleto)
    {
        // Remove espaços em branco no início e no final do nome
        $nomeCompleto = trim($nomeCompleto);

        // Divide o nome completo em partes usando espaços como delimitador
        $partesNome = explode(" ", $nomeCompleto);

        // Palavras a serem ignoradas
        $palavrasIgnoradas = ["de", "da", "do"];

        // Filtra partes do nome, ignorando palavras com menos de duas letras e as palavras ignoradas
        $partesNome = array_filter($partesNome, function ($parte) use ($palavrasIgnoradas) {
            return strlen($parte) > 2 && !in_array(strtolower($parte), $palavrasIgnoradas);
        });

        if (count($partesNome) > 2) {
            //Inserir teste para saber se o login já existe e possibilitar novos testes com os outros nomes. 
            for ($i = 0; $i < count($partesNome); $i++) {
                // Obtém o primeiro e o último nome
                $primeiroNome = $partesNome[0];
                $ultimoNome = $partesNome[count($partesNome) - ($i + 1)];

                // Concatena o primeiro e o último nome para formar o login

                $login = strtolower($primeiroNome . "." . $ultimoNome);
                $login = $this->removerAcentos($login);

                $testaLogin = $this->testaLogin($login);

                if (!$testaLogin) {
                    return $login;
                }
            }
        }


        $primeiroNome = $partesNome[0];
        $ultimoNome = $partesNome[count($partesNome) - 1];

        $loginbase = strtolower($primeiroNome . "." . $ultimoNome);
        $contador = 1;
        $testaLogin = $this->testaLogin($loginbase);

        $login = $loginbase;

        while ($testaLogin) {
            $login = $loginbase . $contador;
            $testaLogin = $this->testaLogin($login);
            $contador++;
        }


        return $login;
    }


    public function removerAcentos($string)
    {
        $mapaAcentos = array(
            'á' => 'a', 'à' => 'a', 'ã' => 'a', 'â' => 'a', 'ä' => 'a',
            'é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e',
            'í' => 'i', 'ì' => 'i', 'î' => 'i', 'ï' => 'i',
            'ó' => 'o', 'ò' => 'o', 'õ' => 'o', 'ô' => 'o', 'ö' => 'o',
            'ú' => 'u', 'ù' => 'u', 'û' => 'u', 'ü' => 'u',
            'ç' => 'c',
            'Á' => 'A', 'À' => 'A', 'Ã' => 'A', 'Â' => 'A', 'Ä' => 'A',
            'É' => 'E', 'È' => 'E', 'Ê' => 'E', 'Ë' => 'E',
            'Í' => 'I', 'Ì' => 'I', 'Î' => 'I', 'Ï' => 'I',
            'Ó' => 'O', 'Ò' => 'O', 'Õ' => 'O', 'Ô' => 'O', 'Ö' => 'O',
            'Ú' => 'U', 'Ù' => 'U', 'Û' => 'U', 'Ü' => 'U'
        );
        return strtr($string, $mapaAcentos);
    }




    public function testaLogin($login)
    {
        TTransaction::open('ibcvix');

        $user = SystemUser::where('login', '=', $login)->load();

        if ($user) {
            return true;
        } else {
            return false;
        }



        TTransaction::close();
    }
}
