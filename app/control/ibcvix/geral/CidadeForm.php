<?php
/**
 * CidadeForm
 *
 * @version    1.0
 * @package    ibcvix
 * @subpackage control
 * @author     Antonio Affonso
 * @license    Igreja Batista Central de Vitória
 */
class CidadeForm extends TPage
{
    protected $form; // form
    
    use Adianti\Base\AdiantiStandardFormTrait; // Standard form methods
    
    /**
     * Construtor de classe
     * Cria a página e o formulário de registro
     */
    function __construct()
    {
        parent::__construct();
        
        //parent::setTargetContainer('adianti_right_panel');
        $this->setAfterSaveAction( new TAction(['CidadeList', 'onReload'], ['register_state' => 'true']) );
        
        $this->setDatabase('ibcvix');              // defines the database
        $this->setActiveRecord('Cidades');     // defines the active record
        
        // cria o formulário
        $this->form = new BootstrapFormBuilder('form_Cidade');
        $this->form->setFormTitle('Cidade');
        
        
        // criar os campos do formulário
        $id = new TEntry('id');
        $nome = new TEntry('nome');

        //Cria o combo para procurar estado
        $estado_id = new TDBUniqueSearch('id_estado', 'ibcvix', 'Estados', 'id', 'nome'); 
        $estado_id->setMinLength(0);
        $estado_id->setMask('{nome} ({uf})');  //Mostra os campos no combo

        // adicionar os campos
        $this->form->addFields( [ new TLabel('Id') ], [ $id ] )->style = 'display: none';
        $this->form->addFields( [ new TLabel('Nome') ], [ $nome ] );
        $this->form->addFields( [ new TLabel('Estado') ], [ $estado_id ] );

        $nome->addValidation('nome', new TRequiredValidator);
        $estado_id->addValidation('id_estado', new TRequiredValidator);


        // Definir tamanhos
        //$id->setSize('100%');
        $nome->setSize('100%');
        $estado_id->setSize('100%');


        //$id->setEditable(FALSE);
        
        // criar as ações do formulário
        $btn = $this->form->addAction(_t('Save'), new TAction([$this, 'onSave']), 'fa:save');
        $btn->class = 'btn btn-sm btn-primary';
        $btn->style = 'width: 100px;';


        $btn2 = $this->form->addActionLink(_t('New'),  new TAction([$this, 'onEdit']), 'fa:plus black');
        $btn2->class = 'btn btn-sm btn-success';
        $btn2->style = 'width: 100px;';

        //$this->form->addHeaderActionLink( _t('Close'), new TAction([$this, 'onClose']), 'fa:times red');
        
        // Box  contêiner vertical onde serão inseridos os objetos criados
        $container = new TVBox;
        $container->style = 'width: 50%';
        // $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add($this->form);
        
        parent::add($container);
    }
    
    /**
     * Feche o painel lateral
     */
    public static function onClose($param)
    {
        TScript::create("Template.closeRightPanel()");
    }
}
