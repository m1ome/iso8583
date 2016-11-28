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
            expect($this->msg->getFields())->toBe([3, 52, 53]);
            expect($this->msg->getField(3))->toBe('900000');
            expect($this->msg->getField(52))->toBe($this->bin);
            expect($this->msg->getField(53))->toBe('2001010100000000');
            expect($this->msg->getField(64))->toBe(null);
        });
    });

    describe('Pack', function() {
        it('should pack message successfully', function() {
            $this->msg->setMTI('0100');
            $this->msg->setField(3, '900000');
            $this->msg->setField(52, $this->bin);
            $this->msg->setField(53, '2001010100000000');

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

});
