<?php
namespace ISO8583;

use ISO8583\Error\UnpackError;
use ISO8583\Error\PackError;

class Message 
{
	protected $protocol;
	protected $options;

	protected $length;
	protected $mti;
    protected $bitmap;
    protected $fields = [];
	protected $mappers = [
		'a'     => Mapper\AlphaNumeric::class,
		'n'     => Mapper\AlphaNumeric::class,
		's'     => Mapper\AlphaNumeric::class,
		'an'    => Mapper\AlphaNumeric::class,
		'as'    => Mapper\AlphaNumeric::class,
		'ns'    => Mapper\AlphaNumeric::class,
		'ans'   => Mapper\AlphaNumeric::class,
		'b'	    => Mapper\Binary::class,
		'z'	    => Mapper\AlphaNumeric::class
	];

	public function __construct(Protocol $protocol, $options = [])
	{
		$defaults = [
			'lengthPrefix' => null
		];

		$this->options = $options + $defaults;
		$this->protocol = $protocol;
	}

	protected function shrink(&$message, $length) 
	{
		$message = substr($message, $length);
	}

	public function pack()
	{
		// Setting MTI
		$mti = bin2hex($this->mti);

		// Populating bitmap
		$bitmap = "";
		$bitmapLength = 64 * (floor(max(array_keys($this->fields)) / 64) + 1);

		$tmpBitmap = "";
		for($i=1; $i <= $bitmapLength; $i++) {
			if (
				$i == 1 && $bitmapLength > 64 ||
				$i == 65 && $bitmapLength > 128 ||
				isset($this->fields[$i])
			) {
				$tmpBitmap .= '1';
			} else {
				$tmpBitmap .= '0';
			}

			if ($i % 64 == 0) {
				for($i=0; $i<64; $i+=4){
        			$bitmap .= sprintf('%01x', base_convert(substr($tmpBitmap, $i, 4), 2, 10));
      			}
			}
		}

		// Packing fields
		$message = "";
		foreach($this->fields as $id => $data) {
			if ($id === 1 || $id === 65) {
				continue;
			}

			$fieldData = $this->protocol->getFieldData($id);
			$fieldMapper = $fieldData['type'];

			if (!isset($this->mappers[$fieldMapper])) {
				throw new \Exception('Unknown field mapper for "' . $fieldMapper . '" type');
			}

			$mapper = new $this->mappers[$fieldMapper]($fieldData['length']);
			$message .= $mapper->pack($data);		
		}

		// Packing all message
		$message = $mti . $bitmap . $message;
		if ($this->options['lengthPrefix'] > 0) {
			$message = bin2hex(sprintf('%0' . $this->options['lengthPrefix'] . 'd', strlen($message) / 2)) . $message;
		}

		return $message;
	}

	public function unpack($message)
	{
		// Getting message length if we have one
		if ($this->options['lengthPrefix'] > 0) {
			$length = (int)hex2bin(substr($message, 0, (int)$this->options['lengthPrefix'] * 2));
			$this->shrink($message, (int)$this->options['lengthPrefix'] * 2);

			if (strlen($message) != $length * 2) {
				throw new UnpackError('Message length is ' . $message / 2 . ' and should be ' . $length);
			}
		}

		// Parsing MTI 
		$this->setMTI(hex2bin(substr($message, 0, 8)));
		$this->shrink($message, 8);

		// Parsing bitmap
		$bitmap = "";
		for(;;) {
			$tmp = base_convert(substr($message, 0, 16), 16, 2);
			if (strlen($tmp) < 64) {
				$tmp = str_repeat('0', 64 - strlen($tmp)) . $tmp;
			}

			$this->shrink($message, 16);
			$bitmap .= $tmp;

			if (substr($tmp, 0, 1) !== "1" || strlen($bitmap) > 128) {
				break;
			}
		}

		$this->bitmap = $bitmap;

		// Parsing fields
		for($i=0; $i < strlen($bitmap); $i++) {
			if ($bitmap[$i] === "1") {
				$fieldNumber = $i + 1;

				if ($fieldNumber === 1 || $fieldNumber === 65) {
					continue;
				}

				$fieldData = $this->protocol->getFieldData($fieldNumber);
				$fieldMapper = $fieldData['type'];

				if (!isset($this->mappers[$fieldMapper])) {
					throw new \Exception('Unknown field mapper for "' . $fieldMapper . '" type');
				}

				$mapper = new $this->mappers[$fieldMapper]($fieldData['length']);
				$unpacked = $mapper->unpack($message);

				$this->setField($fieldNumber, $unpacked);
			}
		}
	}

	public function getMTI()
	{
		return $this->mti;
	}

	public function setMTI($mti)
	{
		if (!preg_match('/^[0-9]{4}$/', $mti)) {
			throw new Error\UnpackError('Bad MTI field it should be 4 digits string');
		}

		$this->mti = $mti;
	}

	public function set(array $fields)
	{
		$this->fields = $fields;
	}

	public function getFields()
	{
		return array_keys($this->fields);
	}

	public function setField($field, $value)
	{
		$this->fields[$field] = $value;
	}

	public function getField($field)
	{
		return isset($this->fields[$field]) ? $this->fields[$field] : null;
	}
}
