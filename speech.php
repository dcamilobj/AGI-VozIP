#!/usr/bin/php -q
<?php
set_time_limit(30);
$param_error_log = '/tmp/notas.log';
$param_debug_on = 1;
require('phpagi.php');
require('yandex.php');
$agi = new AGI();
$agi->answer();
sleep(1);
text2wav($agi,"Bienvenido a la universidad de Antioquia, esta llamada sera monitoreada");
$number = $agi->get_data("beep", 5000, 10);
$agi->record_file("audio", "FLAC", "beep");
sleep(1);

require('definiciones.inc');
$link = mysql_connect(MAQUINA, USUARIO,CLAVE);
mysql_select_db(DB, $link);
mysql_query("INSERT INTO Users VALUES(".($number["result"]).");", $link);

/*$respuesta = shell_exec("curl -X POST --data-binary @'/var/lib/asterisk/sounds/buzon.wav' -o /var/lib/asterisk/buzon.json --header 'Content-Type: audio/l16; rate=8000;' 'https://www.google.com/speech-api/v2/recognize?output=json&lang=es-CO&key=AIzaSyB_MehHP4JhkcNWm2OQ4I8ncaWa7jLYELU'");


$file = fopen("/var/lib/asterisk/buzon.json", "r");
$line = fgets($file); // QUita la primera linea
$respuesta = fgets($file); // Obtiene la respuesta real

$objeto = json_decode($respuesta);
$mensaje = $objeto->{'result'}[0]->{'alternative'}[0]->{'transcript'};*/
