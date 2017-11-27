#!/usr/bin/php -q
<?php
set_time_limit(30);
$param_error_log = '/tmp/notas.log';
$param_debug_on = 1;
require('phpagi.php');
require('yandex.php');
require('TextToSpeech.php');
$agi = new AGI();
$agi->answer();
sleep(1);
$agi->text2wav("Bienvenido al diccionario de la universidad de Antioquia");

do{

if($option != '5' || $option != '7')
{
$agi->text2wav("Presione uno para conocer el significado de una palabra");
$agi->text2wav("Presione dos para conocer la traduccion al ingles de una palabra");
$agi->text2wav("Presione tres para repetir este menu");
$agi->text2wav("Presione cero para salir");

$number = $agi->get_data("beep", 3000, 10);
sleep(1);
$option = $number["result"];
}
else
{
  $option = '2';
}

switch($option)
{
   case "1":
$agi->text2wav("Mencione la palabra que desea consultar");

$number = $agi->record_file('word', 'wav','',4000,NULL,true,NULL);
$agi->stream_file('word','',NULL);
sleep(1);
    

$agi->text2wav("Estamos procesando su audio, un segundo por favor");
TextToSpeech::loadAudio();

$object = TextToSpeech::decodeJson(); 
$word = $object->{'result'}[0]->{'alternative'}[0]->{'transcript'};
if($word != ''){

//Quitar caracteres especiales
$wordR =TextToSpeech::convert($word);
$agi->text2wav("La palabra que nuestro sistema detecto fue ".$wordR);

$response = shell_exec("curl --header 'app_id: 9652931d' \
--header 'app_key: 7f89d8e6afca0dfb3d63a391f8f3b394' \
 https://od-api.oxforddictionaries.com/api/v1/entries/es/".$word);

$object = json_decode($response);
$definition = $object->{'results'}[0]->{'lexicalEntries'}[0]->{'entries'}[0]->{'senses'}[0]->{'definitions'}[0];
if($definition != '')
{
$agi->text2wav(TextToSpeech::convert($definition));

$agi->text2wav("Presione uno si quieres escuchar un ejemplo con esta palabra");
$number = $agi->get_data("beep", 3000, 10);
sleep(1);
$opt = $number["result"];
if($opt == '1')
{
$example = $object->{'results'}[0]->{'lexicalEntries'}[0]->{'entries'}[0]->{'senses'}[0]->{'examples'}[0]->{'text'};
$agi->text2wav(TextToSpeech::convert($example));
sleep(1);	
}
else
{
$agi->text2wav("El ejemplo no sera leido. Se repetira el menu principal.");
sleep(1);
}
}
//Else de 'definition' cuando no se encuentra la definicion de la palabra dada
else
{
  $agi->text2wav("Lo sentimos, nuestro sistema no pudo encontrar la definicion de".$wordR);
}
}
else
{
$agi->text2wav("Lo sentimos, nuestro sistema no pudo detectar la palabra mencionada.");
$agi->text2wav("Presione cinco para intentar de nuevo");
$agi->text2wav("Presione seis para escuchar de nuevo el menu");
$number = $agi->get_data("beep", 3000, 10);
sleep(1);
$option = $number["result"];
}
break;
   case "2":
	$agi->text2wav("Mencione la palabra que desea traduucir");

	$number = $agi->record_file('word', 'wav','',4000,NULL,true,NULL);
	$agi->stream_file('word','',NULL);
	sleep(1);	    

	$agi->text2wav("Estamos procesando su audio, un segundo por favor");
	TextToSpeech::loadAudio();

	$object = TextToSpeech::decodeJson(); 
	$word = $object->{'result'}[0]->{'alternative'}[0]->{'transcript'};
	if($word != ''){

	//Quitar caracteres especiales
	$wordR =TextToSpeech::convert($word);
	$agi->text2wav("La palabra que nuestro sistema detecto fue ".$wordR);

	$response = shell_exec("curl --header 'app_id: 9652931d' \
	 --header 'app_key: 7f89d8e6afca0dfb3d63a391f8f3b394' \
	  https://od-api.oxforddictionaries.com/api/v1/entries/es/".$word."/translations=en");

	$object = json_decode($response);
	$translation = $object->{'results'}[0]->{'lexicalEntries'}[0]->{'entries'}[0]->{'senses'}[0]->{'subsenses'}[0]->{'translations'}[0]->{'text'};

if($translation == '')
{
	$translation = $object->{'results'}[0]->{'lexicalEntries'}[0]->{'entries'}[0]->{'senses'}[0]->{'translations'}[0]->{'text'};
}

if($translation != '')
{
$translationR = TextToSpeech::convert($translation);
require("definiciones.inc");
$link = mysql_connect(MAQUINA, USUARIO,CLAVE);
mysql_select_db(DB, $link);
mysql_query("INSERT INTO Speech VALUES(".$wordR.",".$translationR.");", $link);
mysql_query("INSERT INTO Users VALUES(".$wordR.");", $link); 
$agi->text2wav($translationR);
}
//Else de 'definition' cuando no se encuentra la definicion de la palabra dada
else
{
  $agi->text2wav("Lo sentimos, nuestro sistema no pudo encontrar la traduccion de".$wordR);
  $agi->text2wav("Presione siete para intentar de nuevo");
  $agi->text2wav("Presione seis para escuchar de nuevo el menu");
  $number = $agi->get_data("beep", 3000, 10);
  sleep(1);
  $option = $number["result"];
}
}
else
{
$agi->text2wav("Lo sentimos, nuestro sistema no pudo detectar la palabra mencionada.");
$agi->text2wav("Presione siete para intentar de nuevo");
$agi->text2wav("Presione seis para escuchar de nuevo el menu");
$number = $agi->get_data("beep", 3000, 10);
sleep(1);
$option = $number["result"];
}
    break;
   case "3":
    continue;
    break;
    case "0":
	break;
    case "":
	$agi->text2wav("No se ingreso ningun codigo");
    break;
    default:
      $agi->text2wav("Codigo incorrecto");
}


}while($option != "0");

$agi->text2wav("Gracias por utilizar el diccionario de la universidad de Antioquia, hasta pronto");
$agi->hangup();



