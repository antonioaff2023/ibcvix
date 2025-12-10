<?php

use Adianti\Base\AdiantiStandardControlTrait;
use Adianti\Control\TAction;
use Adianti\Control\TPage;
use Adianti\Registry\TSession;
use Adianti\Widget\Base\TElement;
use Adianti\Widget\Template\THtmlRenderer;
use Adianti\Widget\Util\TActionLink;

class FichaPessoa extends TPage
{
    public function __construct($param)
    {
        parent::__construct($param);



        $html = new THtmlRenderer('app/resources/ficha_identificacao.html');

        $replaces = [];
        //$param['key'] = 16;
        try {
            TTransaction::open('ibcvix');

            $pessoa = Pessoas::find($param['key']);
            $replaces = $pessoa->toArray();

            if ($replaces['foto']) {
                //$foto = json_decode(urldecode($pessoa->foto));
                
                
            } else {
                if ($replaces['genero'] == 'F') {
                    $replaces['foto'] = 'silueta - feminina.jpg';
                } else if ($replaces['genero'] == 'M') {
                    $replaces['foto'] = 'silueta - masculina.jpg';
                }
            }

            $replaces['genero'] = $pessoa->retorna_sexo() ?? '';
            $replaces['data_nascimento'] = $replaces['data_nascimento'] ? date('d/m/Y', strtotime($replaces['data_nascimento'])) : '';
            $replaces['categoria'] = $pessoa->get_categorias()->descricao ?? '';
            $replaces['conjuge_cristao'] = $pessoa->conjuge_cristao() ?? '';
            $replaces['estado_civil'] = $pessoa->get_estado_civil()->descricao ?? '';
            $replaces['nacionalidade'] = $pessoa->get_nacionalidade()->nome ?? '';
            $replaces['endereco'] = $pessoa->endereco_extenso();
            $replaces['data_conversao'] = $replaces['data_conversao'] ? date('d/m/Y', strtotime($replaces['data_conversao'])) : '';
            $replaces['batizado'] = $pessoa->retorna_batizado() ?? '';
            $replaces['data_batismo'] = $replaces['data_batismo'] ? date('d/m/Y', strtotime($replaces['data_batismo'])) : '';





            TTransaction::close();
        } catch (Exception $e) {
            new TMessage('error', $e->getMessage());
        }

        $panel1 = new TElement('div');
        $panel1->style = 'position: relative; height: 80vh; ';


        $panel = new TPanelGroup("<span class = 'titulo-form'>FICHA PESSOAL</span>");
        $panel->style = 'width: 70vw; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);';

        
        
        //O rodapé só pode ser inserido se o usuário for a mesma pessoa que está editando
        $usr = new SystemGroup();
        if (isset($replaces['id_system_user']) || $usr->GruposAcesso([1,3,4])) {
            if ($param['id_system_user'] = $usr) {
                $link = new TActionLink('EDITAR', new TAction(['CadastraPessoa', 'onEdit'], ['key' => $param['key'], 'id' => $param['key']]));
                $link->class = 'btn btn-success';
                $panel->addFooter($link);
            }
        }


        $html->enableSection('main', $replaces);
        $panel1->add($panel);
        $panel->add($html);
        parent::add($panel1);
    }

    public function onEdit()
    {
    }
}
