<?php

use ISO8583\Protocol;

describe('Protocol', function() {

    it('throws on not existing file', function() {
        $schema = __DIR__ . '/Fixtures/NonExistingSchema.json';

        expect(function() use($schema) {
            new Protocol($schema);
        })->toThrow(new Exception('Unknown schema file: ' . $schema));
    });

    it('throws on non JSON file', function() {
        $schema = __DIR__ . '/Fixtures/BadSchema.json';

        expect(function() use($schema) {
            new Protocol($schema);
        })->toThrow(new Exception('Bad JSON schema file: ' . $schema));
    });

    it('will use default file if schema not provided', function() {
        $iso = new Protocol();

        expect($iso->getFieldData(128))->toBe([
            'type' => 'b',
            'length' => '64',
            'description' => 'Message authentication code'
        ]);

        expect(function() use($iso) {
            $iso->getFieldData(129);
        })->toThrow(new Exception('No field 129 in schema'));
    });

    it('parse a good schema file', function() {
        $schema = __DIR__ . '/Fixtures/GoodSchema.json';
        $iso = new Protocol($schema);

        expect($iso->getFieldData(128))->toBe([
            'type' => 'b',
            'length' => '64',
            'description' => 'Message authentication code'
        ]);

        expect(function() use($iso) {
            $iso->getFieldData(129);
        })->toThrow(new Exception('No field 129 in schema'));
    });
});
