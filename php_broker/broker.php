<?php
// =======================================================
//  MQTT BROKER - TESTE DE DEPLOY (Hello World)
// =======================================================

require __DIR__ . '/vendor/autoload.php';

use Workerman\Worker;

$worker = new Worker("tcp://0.0.0.0:1883");
$worker->count = 1;
$worker->name = "PHP_MQTT_Broker";

$worker->onConnect = function($connection) {
    echo "Nova conexão: {$connection->getRemoteIp()}\n";
    echo "Hello World!\n";
};

$worker->onMessage = function($connection, $data) {
    if (empty($data)) return;

    $packetType = ord($data[0]) >> 4;

    // CONNECT -> CONNACK
    if ($packetType === 1) {
        $connection->send(chr(0x20) . chr(0x02) . chr(0x00) . chr(0x00));
        echo "Cliente conectado - Hello World!\n";
    }

    // PINGREQ -> PINGRESP
    if ($packetType === 12) {
        $connection->send(chr(0xD0) . chr(0x00));
    }
};

$worker->onClose = function($connection) {
    echo "Conexão encerrada\n";
};

Worker::runAll();
