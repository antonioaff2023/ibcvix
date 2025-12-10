<?php
/**
 * EstadoForm
 *
 * @version    1.0
 * @package    ibcvix
 * @subpackage control
 * @author     Pablo Dall'Oglio
 * @copyright  Copyright (c) 2006 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    http://www.adianti.com.br/framework-license
 */
class EstadoForm extends TPage
{
    protected $form; // form
    
    use Adianti\Base\AdiantiStandardFormTrait; // Standard form methods
    
    /**
     * Class constructor
     * Creates the page and the registration form
     */
    function __construct()
    {
        parent::__construct();
        
        //parent::setTargetContainer('adianti_right_panel');
        $this->setAfterSaveAction( new TAction(['EstadoList', 'onReload'], ['register_state' => 'true']) );
        
        $this->setDatabase('ibcvix');              // defines the database
        $this->setActiveRecord('Estados');     // defines the active record
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_Estado');
        $this->form->setFormTitle('Estado');
        $this->form->setClientValidation(true);
        //$this->form->setColumnClasses( 2, ['col-sm-5 col-lg-4', 'col-sm-7 col-lg-8'] );
        

        // create the form fields
        //$id = new TEntry('id');
        $uf = new TEntry('uf');
        $nome = new TEntry('nome');


        // add the fields
        //$this->form->addFields( [ new TLabel('Id') ], [ $id ] );
        $this->form->addFields( [ new TLabel('UF') ], [ $uf ] );
        $this->form->addFields( [ new TLabel('Nome') ], [ $nome ] );

        $uf->addValidation('Uf', new TRequiredValidator);
        $nome->addValidation('Nome', new TRequiredValidator);


        // set sizes
        //$id->setSize('100%');
        $uf->setSize('100%');
        $nome->setSize('100%');


        //$id->setEditable(FALSE);
        
        // create the form actions
        $btn = $this->form->addAction(_t('Save'), new TAction([$this, 'onSave']), 'fa:save');
        $btn->class = 'btn btn-sm btn-primary';
        $btn->style = 'width: 100px;';
        

        $btn2 = $this->form->addActionLink(_t('New'),  new TAction([$this, 'onEdit']), 'fa:plus white');
        $btn2->class = 'btn btn-sm btn-success';
        $btn2->style = 'width: 100px;';

        //$this->form->addHeaderActionLink( _t('Close'), new TAction([$this, 'onClose']), 'fa:times red');
        
        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 40%';
        // $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add($this->form);
        
        parent::add($container);
    }
    
    /**
     * Close side panel
     */
    public static function onClose($param)
    {
        TScript::create("Template.closeRightPanel()");
    }
}
