<?php
 

 class DeclaracaoDoutrinaria extends TPage

    {
        public function __construct($param)
        {
            parent::__construct();
            $arquivo=$param['arquivo'];
            $regiao=$param['regiao'];
            $contentFile = "app/control/declaracao/$arquivo.html";
            $panel = new TScroll; 
            $panel->setSize(800,750);
            $panel->style = "text-align: justify; width: 50vw !important; height: 83.5vh !important; overflow-y: auto;";
            
            $html = new THtmlRenderer($contentFile);
            
            
            if ($regiao != 'geral') {
            $html->enableSection($regiao);
            $panel->add($html);
            
           
            } else  {
                
                $completo = file_get_contents($contentFile);
                $panel->add($completo);
                
                
            }
 
            parent::add($panel);
            

        }


    
    }



     
