<?php

namespace Test\OnlineConvert\Functional;

/**
 * Tests interaction with the information endpoint.
 */
class InformationEndpointTest extends FunctionalTestCase
{
    /**
     * @test
     */
    public function apiSchemaContainsBasicAndContactInformation()
    {
        $schema = $this->api->getInformationEndpoint()->getSchema();

        $this->assertArrayHasKey('info', $schema);
        $this->assertArrayHasKey('title', $schema['info']);
        $this->assertArrayHasKey('termsOfService', $schema['info']);
        $this->assertArrayHasKey('contact', $schema['info']);
        $this->assertArrayHasKey('email', $schema['info']['contact']);
        $this->assertArrayHasKey('version', $schema['info']);
    }

    /**
     * @test
     */
    public function statusCodesAreInformedCorrectly()
    {
        $statusList = $this->api->getInformationEndpoint()->getStatusesList();
        $statusCodes = array_column($statusList, 'code');

        $this->assertContains('incomplete', $statusCodes);
        $this->assertContains('queued', $statusCodes);
        $this->assertContains('ready', $statusCodes);
        $this->assertContains('downloading', $statusCodes);
        $this->assertContains('processing', $statusCodes);
        $this->assertContains('completed', $statusCodes);
        $this->assertContains('failed', $statusCodes);
    }

    /**
     * @test
     */
    public function canGetConversionSchema()
    {
        $target     = 'mp3';
        $category   = 'audio';
        $conversion = $this->api->getInformationEndpoint()->getConversionSchema($target, $category);

        $this->assertArrayHasKey('target', $conversion, 'Conversion schema must show the target');
        $this->assertArrayHasKey('category', $conversion, 'Conversion schema must show the category');
        $this->assertArrayHasKey('options', $conversion, 'Conversion schema must show the conversion options');

        $this->assertEquals($target, $conversion['target'], 'The target must be the same as the requested one');
        $this->assertEquals($category, $conversion['category'], 'The category must be the same as the requested one');
    }
}
