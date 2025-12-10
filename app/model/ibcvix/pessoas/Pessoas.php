<?php

use Adianti\Database\TCriteria;
use Adianti\Database\TFilter;
use Adianti\Database\TRepository;
use Adianti\Database\TTransaction;

/**
 * Pessoas Active Record
 * @author  Antonio Affonso
 */
class Pessoas extends TRecord
{
    const TABLENAME = 'pessoas';
    const PRIMARYKEY = 'id';
    const IDPOLICY =  'max'; // {max, serial}


    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('nome');
        parent::addAttribute('sobrenome');
        parent::addAttribute('apelido');
        parent::addAttribute('nome_completo');
        parent::addAttribute('id_categorias');
        parent::addAttribute('genero');
        parent::addAttribute('pai');
        parent::addAttribute('mae');
        parent::addAttribute('obs');
        parent::addAttribute('fone01');
        parent::addAttribute('fone02');
        parent::addAttribute('email01');
        parent::addAttribute('logradouro');
        parent::addAttribute('bairro');
        parent::addAttribute('numero');
        parent::addAttribute('complemento');
        parent::addAttribute('cep');
        parent::addAttribute('id_cidade');
        parent::addAttribute('data_nascimento');
        parent::addAttribute('identidade');
        parent::addAttribute('cpf');
        parent::addAttribute('id_estado_civil');
        parent::addAttribute('id_escolaridade');
        parent::addAttribute('conjuge');
        parent::addAttribute('foto');
        parent::addAttribute('conjuge_cristao');
        parent::addAttribute('data_conversao');
        parent::addAttribute('batizado');
        parent::addAttribute('data_batismo');
        parent::addAttribute('ministro_batismo');
        parent::addAttribute('igreja_anterior');
        parent::addAttribute('notas');
        parent::addAttribute('id_system_user');
        parent::addAttribute('id_profissao');
        parent::addAttribute('id_nacionalidade');
    }

    public function get_categorias()
    {
        return Categorias::find($this->id_categorias);
    }


    public function get_estado_civil()
    {
        return EstadoCivil::find($this->id_estado_civil);
    }

    public function get_escolaridade()
    {
        return Escolaridade::find($this->id_escolaridade);
    }

    public function get_profissao()
    {
        return Profissao::find($this->id_profissao);
    }

    public function get_system_user()
    {
        return SystemUser::find($this->id_system_user);
    }

    public function get_nacionalidade()
    {

        return Nacionalidade::find($this->id_nacionalidade);
    }

    public function get_cidade()
    {
        return Cidades::find($this->id_cidade);
    }


    public function retorna_sexo()
    {
        $sexo = 'Indefinido';
        if ($this->genero == 'M') {
            $sexo = 'Masculino';
        } else if ($this->genero == 'F') {
            $sexo = 'Feminino';
        }

        return $sexo;
    }
    public function conjuge_cristao()
    {
        $conjuge = '';
        if ($this->conjuge_cristao == 'S') {
            $conjuge = 'Sim';
        } else if ($this->conjuge_cristao == 'N') {
            $conjuge = 'Não';
        }

        return $conjuge;
    }

    public function endereco_extenso()
    {


        $endereco = $this->logradouro;
        $endereco .= ', ' . $this->numero;
        $endereco .= ', ' . $this->bairro;
        if ($this->cidade) {
            $cidade = new Cidades($this->id_cidade);
            $estado = $cidade->get_estado()->uf;
            $endereco .= ', ' . $this->get_cidade()->nome . '/' . $estado;
        } else {
            $endereco .= '';
        }

        return $endereco;
    }


    public function retorna_batizado()
    {
        $batizado = '';
        if($this->batizado) {
            $batizado = $this->batizado; 
        } else {
            $batizado = $this->grava_batizado();
        }
        

        if ($batizado == 'S') {
            $batizado = 'Sim';
        } else if ($this->batizado == 'N') {
            $batizado = 'Não';
        }

        return $batizado;
    }

    public function grava_batizado() {
        if ($this->data_batismo) {
            TTransaction::open('ibcvix');
            $repos = new TRepository('Pessoas');
            $criterio = new TCriteria;
            $criterio->add(new TFilter('id','=',$this->id));
            $valor = array('batizado' => 'S');
            $repos->update($valor, $criterio);
            TTransaction::close();
            return 'Sim';
        } else {
            return '';
        }
    }
}
