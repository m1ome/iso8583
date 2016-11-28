# PHP ISO8583 Parser
[![Build Status](https://travis-ci.org/m1ome/iso8583.svg?branch=master)](https://travis-ci.org/m1ome/iso8583)
[![Coverage Status](https://coveralls.io/repos/github/m1ome/iso8583/badge.svg?branch=master)](https://coveralls.io/github/m1ome/iso8583?branch=master)

# Usage
```php
use ISO8583\Protocol;
use ISO8583\Message;

$iso = new Protocol();
$message = new Message($iso, [
	'lengthPrefix' => 5
]);

// Unpacking message
$message->unpack('303030333430313030200000000000080039303030303032303031303130313030303030303030');

// Packing message
$packedMessage = $message->pack();
```
