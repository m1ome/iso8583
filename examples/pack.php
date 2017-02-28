<?php

require 'vendor/autoload.php';

use ISO8583\Protocol;
use ISO8583\Message;

$iso = new Protocol();
$message = new Message($iso, [
    'lengthPrefix' => 0
]);

// Packing message
$message->setMTI('0200');
$message->setField(2, "5574200000002100");
$message->setField(3, "000000");
$message->setField(4, "000000001000");
$message->setField(7, "0914225918");
$message->setField(11, "000155");
$message->setField(12, "235918");
$message->setField(13, "0914");
$message->setField(14, "0201");
$message->setField(15, "0914");
$message->setField(18, "0000");
$message->setField(22, "0200");
$message->setField(25, "00");
$message->setField(28, "00000000");
$message->setField(32, "000002");
$message->setField(33, "000002");
$message->setField(35, "5574200000002100=02017650000000000");
$message->setField(37, "PCZ257000155");
$message->setField(41, "02117986");
$message->setField(42, "000000000020553");
$message->setField(43, "Location2              Welwyn Garden01GB");
$message->setField(49, "826");
$message->setField(59, "0000456445|0000|PCZ257000155");

echo $message->pack() . PHP_EOL;
