<?php
/**
 * Cria thumbnails das imagens configuradas na pasta corrente
 * @author Maiquel Leonel <skywishmtfk@gmail.com>
 * @package PHPThumbnail
 * @since 03:22 01/02/2010
 * @dependencies php-gd
 * @version 2.0
 */
class Thumb{
   private $t = array('old_w' => 0,'old_h' => 0,'new_w' => 0,'new_h' => 0);
   private $max;
   private $arquivo;
   private $caminho; 
   /**
    * pre configura os tipos aceitos e as funcoes corresponmdentes
    */
   private $tipos = array('jpg' => array('imagecreatefromjpeg','imagejpeg'),
                           'jpeg'=> array('imagecreatefromjpeg','imagejpeg'),
                           'gif' => array('imagecreatefromgif' ,'imagegif'),
                           'png' => array('imagecreatefrompng' ,'imagepng'),
                  );
   /**
    * Funcao construtora executa os metodos na chamada
    * @param [string] $arquivo o caminho para o arquivo
    * @param [array] $novos_tamanhos  array com os tamanhos maximos 
    *                            o primeiro elemento a a largura
    *                            o segundo elemento a a altura
    * @return void
    */
   public function __construct($arquivo, $novos_tamanhos,$caminho_para_salvar=""){
      //seta o arquivo
      $this->arquivo = $arquivo;
      //seta o caminho pra salvar..
      $this->caminho = $caminho_para_salvar;
      //pega informacoes sobre o arquivo..
      $this->type = pathinfo($this->arquivo);
      //seta os tamanhos maximos
      $this->max = array('w' => $novos_tamanhos[0],'h' => $novos_tamanhos[1]);
      //redimensiona e salva no diretorio dado
      $this->redimensiona();
   }
   /**
    * Metodo que calcula os tamanho proporcional com base na imagem
    * @return void
    */
   private function define_tamanhos(){
      list($w,$h) = getimagesize($this->arquivo);
      $prop = $w / $h;
      $this->t['new_h'] = $this->max['h'];
      $this->t['new_w'] = $this->max['w'];
      $this->t['old_w'] = $w;
      $this->t['old_h'] = $h;
      if ($w > $this->max['w'] || $h > $this->max['h']):
         $this->t['new_w'] = $this->max['w'];
         $this->t['new_h'] = $this->max['w'] / $prop;
         if ($this->t['new_h'] > $this->max['h']):
            $this->t['new_h'] = $this->max['h'];
            $this->t['new_w'] = $this->max['h'] * $prop;
         endif;
      endif;
   }
   /**
    * metodo responsavel por rediemensionar as imagens e salvar no diretorio corrente do script
    * @return void;
    */
   private function redimensiona(){
      //se eh um tipo aceito
      if(array_key_exists(strtolower($this->type['extension']),$this->tipos)):
         $this->define_tamanhos();
         //cria o arquivo de destino de referencia
         $dst = imagecreatetruecolor($this->t['new_w'], $this->t['new_h']);
         // cria uma copia da imagem
         $src = $this->tipos[strtolower($this->type['extension'])][0]($this->caminho.$this->arquivo);
         // redimensiona a copia
         imagecopyresampled($dst, $src, 0, 0, 0, 0,$this->t['new_w'],$this->t['new_h'],$this->t['old_w'],$this->t['old_h']);
         // salva a nova imagem no caminho correto
         $this->tipos[strtolower($this->type['extension'])][1]($dst,$this->caminho.$this->arquivo); 
         //apaga o destino de referencia
         imagedestroy($dst);
      endif;
   }
   /**
    * Metodo estatico que retira o nome do arquivo
    * @param [array] $aquivo normalmente o $_FILES['nome-do-campo-no-form']
    * @param [string] $nome o nome que se quer dar ao arquivo
    *                           em caso de omissao sera setado o timestamp do momento
    * @return [string] o nome "unico" para o arquivo com sua extensao correta
    */
   public static function nomeia(array $arquivo,$nome=false){
      if(!$nome):
         $nome = time();
      endif;
      //seta os patterns
      $pattern = array('bug-do-IE' => '/(pjpeg)|(pjpg)|(x-png)/',
                        'correcao-IE' => '/(\/p)|(\/x-)/',
                        'extensao' => '/(image\/)/'
               );
      //verifica se veio do IEca com bug...
      if(preg_match($pattern['bug-do-ie'],$arquivo['type'])):
         //retiro o char errado
         $arquivo['type'] = preg_replace($pattern['correcao-IE'],'/',$arquivo['type']);
      endif;
      //captura a extensao pelo mime-type do arquivo informado
      $extensao = preg_replace($pattern['extensao'],'',$arquivo['type']);
      //retorno unico para o arquivo
      return $nome.'.'.$extensao;
   }
   /**
    * Metodo estatico que move o arquivo para a pasta correta
    * renomeando conforme o necessario 
    * @param [array] $arquivo  normalmente o $_FILES['nome-do-campo-no-form']
    * @param [string] $caminho_para_arquivo  geralmente filtrado pelo metodo 
    *                                       Thumb::nomeia antes
    * @return [bool] true caso consiga efetuar o upload.
    */
   public static function upload(array $arquivo,$caminho_para_arquivo){
      return move_uploaded_file($arquivo['tmp_name'],$caminho_para_arquivo);
   }
}

