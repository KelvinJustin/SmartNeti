<?php

/**
 * TumaSend Plugin Tests
 * 
 * Basic unit tests for plugin components
 * Run with: phpunit PluginTest.php
 */

namespace TumaSend\Tests;

use PHPUnit\Framework\TestCase;

class PluginTest extends TestCase
{
    private $config;
    private $api;
    private $sms;
    
    protected function setUp(): void
    {
        // Mock dependencies for testing
        $this->config = $this->createMock('TumaSend\Config');
        $this->api = $this->createMock('TumaSend\API');
        $this->sms = new \TumaSend\SMS($this->config, $this->api);
    }
    
    /**
     * Test phone number normalization
     */
    public function testPhoneNormalization()
    {
        // Test Malawi formats
        $this->assertEquals('+265991234567', $this->sms->normalizePhone('0991234567'));
        $this->assertEquals('+265991234567', $this->sms->normalizePhone('991234567'));
        $this->assertEquals('+265991234567', $this->sms->normalizePhone('265991234567'));
        $this->assertEquals('+265991234567', $this->sms->normalizePhone('+265991234567'));
        
        // Test invalid formats
        $this->assertFalse($this->sms->normalizePhone('123'));
        $this->assertFalse($this->sms->normalizePhone('abc'));
    }
    
    /**
     * Test SMS segment calculation
     */
    public function testSegmentCalculation()
    {
        // GSM-7 single segment
        $this->assertEquals(1, $this->sms->calculateSegments('Hello World'));
        
        // GSM-7 multi-segment
        $longGsm7 = str_repeat('A', 161);
        $this->assertEquals(2, $this->sms->calculateSegments($longGsm7));
        
        // Unicode single segment
        $this->assertEquals(1, $this->sms->calculateSegments('Hello 世界'));
        
        // Unicode multi-segment
        $longUnicode = str_repeat('世', 71);
        $this->assertEquals(2, $this->sms->calculateSegments($longUnicode));
    }
    
    /**
     * Test GSM-7 detection
     */
    public function testGsm7Detection()
    {
        $this->assertTrue($this->sms->isGSM7('Hello World'));
        $this->assertTrue($this->sms->isGSM7('Test £123'));
        $this->assertFalse($this->sms->isGSM7('Hello 世界'));
        $this->assertFalse($this->sms->isGSM7('Test 😊'));
    }
}
