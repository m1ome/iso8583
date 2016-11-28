# PHP ISO8583 Parser

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
