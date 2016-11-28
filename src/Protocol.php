<?php
namespace ISO8583;

class Protocol 
{
	protected $schema = [];

	public function __construct($schema = null)
	{
		$defaultSchema = implode(DIRECTORY_SEPARATOR, [__DIR__, 'Schema.json']);
		$schemaJSON    = json_decode(file_get_contents($defaultSchema));

		foreach($schemaJSON as $field => $data) 
		{
			$this->setFieldData((int)$field, $data);
		}
	}

	public function getFieldData($field)
	{
		 if (!isset($this->schema[$field])) 
		 {
			 throw new \Exception('No field ' . $field . ' in schema');
		 }

		 return $this->schema[$field];
	}

	public function setFieldData($field, $data)
	{
		$this->schema[$field] = $data;
	}
}
