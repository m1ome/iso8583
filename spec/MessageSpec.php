<?php

use ISO8583\Protocol;
use ISO8583\Message;

describe('Message', function() {

    beforeEach(function() {
        $this->protocol = new Protocol();
        $this->msg  = new Message($this->protocol, [
            'lengthPrefix' => 5
        ]);

        $this->bin     = hex2bin(dechex(bindec(str_repeat('11111001', 8))));
        $this->example = '3030303432303130302000000000001800393030303030f9f9f9f9f9f9f80032303031303130313030303030303030';
    });

    describe('Unpack', function() {
        it('should unpack message successfully', function() {
            $this->msg->unpack($this->example);

            expect($this->msg->getMTI())->toBe('0100');
            expect($this->msg->getFieldsIds())->toBe([3, 52, 53]);
            expect($this->msg->getField(3))->toBe('900000');
            expect($this->msg->getField(52))->toBe($this->bin);
            expect($this->msg->getField(53))->toBe('2001010100000000');
            expect($this->msg->getFields())->toBe([
                3 => '900000',
                52 => $this->bin,
                53 => '2001010100000000'
            ]);
        });

        it('should throw error on wrong length', function() {
            expect(function() {
                $message = substr($this->example, 0, strlen($this->example) - 2);
                $this->msg->unpack($message);
            })->toThrow(new \ISO8583\Error\UnpackError('Message length is 41 and should be 42'));
        });
    });

    describe('Pack', function() {
        it('should pack message successfully', function() {
            $this->msg->setMTI('0100');
            $this->msg->setField(1, 'This field will be skipped');
            $this->msg->setField(3, '900000');
            $this->msg->setField(52, $this->bin);
            $this->msg->setField(53, '2001010100000000');
            $this->msg->setField(65, 'This field should also be skipped');

            $message = $this->msg->pack();
            expect($message)->toBe($this->example);
        });

        it('should pack message successfully using set() function', function() {
            $this->msg->setMTI('0100');
            $this->msg->set([
                3 => '900000',
                52 => $this->bin,
                53 => '2001010100000000'
            ]);

            $message = $this->msg->pack();
            expect($message)->toBe($this->example);
        });

        it('throw error on wrong MTI', function() {
            expect(function() {
                $this->msg->setMTI('12345');
            })->toThrow('Bad MTI field it should be 4 digits string');
        });
    });

    describe('Pack & Unpack', function() {
        it('should pack&unpack message', function() {
            $mti = '0200';
            $fields = [
                4 => "000000000000",
                7 => "1205114178",
                11 => "000110",
                18 => "6000",
                19 => "643",
                22 => "0200",
                25 => "00",
                32 => "513299",
                37 => "623450001100",
                41 => 12345678,
                42 => "100000001      ",
                49 => "643",
                60 => "2230110000",
                2  => "5175380690063288",
                3  => "300000",
                35 => "5175380690063288=17032011888301500000",
                52 => hex2bin("A877727C5AD463FD"),
                53 => "2001010100000000"                
            ];
            ksort($fields);

            $this->msg->setMTI($mti);
            foreach($fields as $id => $data) {
                $this->msg->setField($id, $data);
            }

            $packedMessage = $this->msg->pack();
            
            $msg = new Message($this->protocol, [
                'lengthPrefix' => 5
            ]);
            $msg->unpack($packedMessage);

            expect($msg->getMTI())->toBe($mti);
            expect($msg->getFields())->toEqual($fields);
        });
    });
});
