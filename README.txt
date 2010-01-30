Exemplo de uso

//seta a pasta para salvar a imagem
$pasta = '../images/flyers/';
//pego o nome do arquivo.extensão, corrgindo o bug so IEca
if(preg_match('/(pjpeg)|(pjpg)/',$arquivo['type'])):
	$arquivo['type'] = preg_replace('/\/p/','/',$arquivo['type']);
endif;
//seto um nome unico para o arquivo
$nome_arquivo = time().'.'.preg_replace('/(image\/)/','',$arquivo['type']);
//movo o arquivo para a pasta
if(move_uploaded_file($_FILES['imagem']['tmp_name'],$pasta . $nome_arquivo)):
	//crio o thumb configurando o tamanho
	//apenas um valor é necessario mas é possivel pasar os dois
	new Thumb($pasta . $nome_arquivo,array(120));
endif;

para multiplos redimensionamentos 

	$pasta_tb = '../images/fotos/thumbs/';
	$pasta_gd = '../images/fotos/gd/';
	.
	.
	.
	if(move_uploaded_file($arquivo['tmp_name'],$pasta_gd.$nome_arquivo)):
		//basta copiar o arquivo original para o destino 
		copy($pasta_gd.$nome_arquivo,$pasta_tb.$nome_arquivo);
		//seta os valores
		// parametro array( maior_largura_possivel, maior_altura_possivel
		new Thumb($pasta_gd.$nome_arquivo,array(690,510));
		new Thumb($pasta_tb.$nome_arquivo,array(230,170));
	endif;
