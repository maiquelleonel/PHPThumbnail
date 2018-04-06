<?php
/**
 * Cria thumbnails das imagens configuradas na pasta corrente
 * @author Maiquel Leonel <skywishmtfk@gmail.com>
 * @package PHPThumbnail
 * @since 03:22 01/02/2010
 * @dependencies php-gd
 * @version 2.0
 */
class Thumb {
   
   private $t = array('old_w' => 0,'old_h' => 0,'new_w' => 0,'new_h' => 0);
   private $max;
   private $file;
   private $path; 
   
   /**
    * pre configura os tipos aceitos e as funcoes corresponmdentes
    */
   private $types = array(
      'jpg' => array('make' => 'imagecreatefromjpeg', 'save' => 'imagejpeg'),
      'jpeg'=> array('make' => 'imagecreatefromjpeg', 'save' => 'imagejpeg'),
      'gif' => array('make' => 'imagecreatefromgif' , 'save' => 'imagegif'),
      'png' => array('make' => 'imagecreatefrompng' , 'save' => 'imagepng'),
   );
   
   /**
    * Funcao construtora executa os metodos na chamada
    * @param [string] $file o caminho para o arquivo
    * @param [array]  $new_sizes array com os tamanhos maximos 
    *                            o primeiro elemento a a largura
    *                            o segundo elemento a a altura
    * @return void
    */
   public function __construct($file, $new_sizes, $path="")
   {
      //seta o arquivo
      $this->file = $file;
      //seta o caminho pra salvar..
      $this->path = $path;
      //pega informacoes sobre o arquivo..
      $this->type = pathinfo($this->file);
      $this->type['extension'] = strtolower($this->type['extension']);
      //seta os tamanhos maximos
      $this->max = array('w' => $new_sizes[0],'h' => $new_sizes[1]);
      //redimensiona e salva no diretorio dado
      $this->resize();
   }
   
   /**
    * Metodo que calcula os tamanho proporcional com base na imagem
    * @return void
    */
   private function setSizes()
   {
      list($w, $h) = getimagesize($this->file);
      $prop = $w / $h;
      $this->t['new_h'] = $this->max['h'];
      $this->t['new_w'] = $this->max['w'];
      $this->t['old_w'] = $w;
      $this->t['old_h'] = $h;
      if ($w > $this->max['w'] || $h > $this->max['h']) {
         $this->t['new_w'] = $this->max['w'];
         $this->t['new_h'] = $this->max['w'] / $prop;
         if ($this->t['new_h'] > $this->max['h']) {
            $this->t['new_h'] = $this->max['h'];
            $this->t['new_w'] = $this->max['h'] * $prop;
         }
      }
   }
   
   /**
    * metodo responsavel por redimensionar as imagens e salvar no diretorio corrente do script
    * @return void;
    */
   private function resize()
   {
      //se eh um tipo aceito
      if (array_key_exists($this->type['extension'], $this->types)) {
         $this->setSizes();
         //cria o arquivo de destino de referencia
         $dst = imagecreatetruecolor($this->t['new_w'], $this->t['new_h']);
         // cria uma copia da imagem
         $src = $this->types[$this->type['extension']]['make']($this->path . $this->file);
         // redimensiona a copia
         imagecopyresampled($dst, $src, 0, 0, 0, 0, $this->t['new_w'], $this->t['new_h'], $this->t['old_w'], $this->t['old_h']);
         // salva a nova imagem no caminho correto
         $this->types[$this->type['extension']]['save']($dst, $this->path. $this->file); 
         //apaga o destino de referencia
         imagedestroy($dst);
      }
   }
   
   /**
    * Metodo estatico que retira o nome do arquivo
    * @param [array] $aquivo normalmente o $_FILES['nome-do-campo-no-form']
    * @param [string] $nome o nome que se quer dar ao arquivo
    *                           em caso de omissao sera setado o timestamp do momento
    * @return [string] o nome "unico" para o arquivo com sua extensao correta
    */
   public static function getName(array $file, $name=false)
   {
      if (! $name) {
         $name = time();
      }
      //seta os patterns
      $pattern = array(
          'IE-bug' => '/(pjpeg)|(pjpg)|(x-png)/',
          'IE-fix' => '/(\/p)|(\/x-)/',
          'ext'    => '/(image\/)/'
      );
      //verifica se veio do IEca com bug...
      if (preg_match($pattern['IE-bug'], $file['type'])) {
         //retiro o char errado
         $file['type'] = preg_replace($pattern['IE-fix'], '/', $file['type']);
      }
      //captura a extensao pelo mime-type do arquivo informado
      $ext = preg_replace($pattern['ext'], '', $file['type']);
      //retorno unico para o arquivo
      return $name . '.' . $ext;
   }
   /**
    * Metodo estatico que move o arquivo para a pasta correta
    * renomeando conforme o necessario 
    * @param [array] $arquivo  normalmente o $_FILES['nome-do-campo-no-form']
    * @param [string] $caminho_para_arquivo  geralmente filtrado pelo metodo 
    *                                       Thumb::nomeia antes
    * @return [bool] true caso consiga efetuar o upload.
    */
   public static function upload(array $file, $new_path)
   {
      return move_uploaded_file($file['tmp_name'], $new_path);
   }
}
