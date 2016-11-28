<?php
namespace ISO8583;

class Protocol 
{
	protected $schema = [];

	public function __construct($schema = null)
	{
        $schema = $schema === null ? implode(DIRECTORY_SEPARATOR, [__DIR__, 'Schema.json']) : $schema;
        if (!file_exists($schema)) {
            throw new \Exception('Unknown schema file: ' . $schema);
        }

		$schemaJSON    = json_decode(file_get_contents($schema), true);
        if ($schemaJSON === null) {
            throw new \Exception('Bad JSON schema file: ' . $schema);
        }

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
