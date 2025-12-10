<?php
/**
 * Template View pattern implementation
 *
 * @version    1.0
 * @package    samples
 * @subpackage tutor
 * @author     Pablo Dall'Oglio
 * @copyright  Copyright (c) 2006 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    https://adiantiframework.com.br/license-tutor
 */
class DeclaracaoPDF extends TPage
{
    /**
     * Constructor method
     */
    public function __construct()
    {
        parent::__construct();

        $css = "<link rel='stylesheet' href='app/resources/impressao.css'>";
        
        $modelo = file_get_contents('app/resources/modelo.html');

 

        $contentFile = 'app/control/declaracao/declaracao.html';

        if (!file_exists($contentFile)) {
            die('Arquivo DeclaracaoPDF.php não encontrado.');
        }

        $html = file_get_contents($contentFile);
        // create the HTML Renderer

        $html = $css . $modelo . $html;
        
        try
        {
 
                                                      
            // replace the main section variables
            
            
            // wrap the page content using vertical box
            $vbox = new TVBox;
            $vbox->style = 'width: 100%';
            
            $vbox->add($html);
            
            
                        
            $options = new \Dompdf\Options();
            $options->setIsRemoteEnabled(true);
            $options->setChroot(getcwd());
            
            // converts the HTML template into PDF
            $dompdf = new \Dompdf\Dompdf($options);
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();
        
            $canvas = $dompdf->get_canvas();
            
            
            
            $canvas->page_script(function ($pageNumber, $pageCount, $canvas, $fontMetrics) {
                $footer = "Página {$pageNumber} de {$pageCount}";
                $font = $fontMetrics->get_font("Arial", "normal");
                $size = 10;
                $width = $canvas->get_width();
                $height = $canvas->get_height();
                $textWidth = $fontMetrics->get_text_width($footer, $font, $size);
                $canvas->text(($width - $textWidth) / 2, $height - 15, $footer, $font, $size);
            });
            

            
            // write and open file
            file_put_contents('app/output/document.pdf', $dompdf->output());
            
            // open window to show pdf
            $window = TWindow::create('IMPRIME DECLARAÇÃO DOUTRINÁRIA', 0.8, 0.8);
            $object = new TElement('object');
            $object->data  = 'app/output/document.pdf';
            $object->type  = 'application/pdf';
            $object->style = "width: 100%; height:calc(100% - 10px)";
            $object->add('O navegador não suporta a exibição deste conteúdo, <a style="color:#007bff;" target=_newwindow href="'.$object->data.'"> clique aqui para baixar</a>...');
            
            $window->add($object);
            $window->show();
        }
        catch (Exception $e)
        {
            new TMessage('error', $e->getMessage());
        }
    }

    public function extrairSecao($arquivo, $nome) {
        // Verifica se o arquivo existe
        if (!file_exists($arquivo)) {
            return null; // Retorna null se o arquivo não existir
        }
    
        // Lê o conteúdo do arquivo
        $conteudo = file_get_contents($arquivo);
    
        // Monta a expressão regular dinamicamente com base no nome da seção
        $padrao = '/<!--\[' . preg_quote($nome, '/') . '\]-->(.*?)<!--\[\/' . preg_quote($nome, '/') . '\]-->/s';
    
        // Procura pela seção no conteúdo
        if (preg_match($padrao, $conteudo, $matches)) {
            return trim($matches[1]); // Retorna o conteúdo encontrado, removendo espaços extras
        }
    
        return null; // Retorna null se a seção não for encontrada
    }
}
