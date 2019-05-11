<?php

namespace AppTest;

use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;
use Smalot\PdfParser\Parser;

class PurchaseOrderTest extends TestCase
{
    /**
     * Test GET where POST is only allowed, expect 405 status code
     *
     * @return void
     */
    public function testMethodNotAllowed(): void
    {
        $this->get('/purchaseOrder');

        $this->assertEquals(
            405,
            $this->response->getStatusCode()
        );
    }

    /**
     * Test missing application/json Content-Type, expect 400 status code
     *
     * @return void
     */
    public function testBadRequest(): void
    {
        $this->post('/purchaseOrder');

        $this->assertEquals(
            400,
            $this->response->getStatusCode()
        );
    }

    /**
     * Test valid requests that returns PDF
     *
     * @return void
     */
    public function testValidRequest(): void
    {
        $data = [
            'company' => [
                'name' => 'My Company',
                'number' => 'OC123456',
                'vatNumber' => 'GB1234567890',
                'email' => 'info@example.com',
                'telephone' => '01234567890',
                'website' => 'www.example.com',
                'address' => [
                    'line1' => 'line 1',
                    'line2' => 'line 2',
                    'city' => 'City',
                    'postcode' => 'PO57 C0D',
                ]
            ],
            'purchaseOrder' => [
                'ref' => 'PO-1234',
                'summary' => 'Purchase Order for some service',
                'notes' => 'Purchase notes',
                'symbol' => '&pound;',
                'net' => 100.0,
                'tax' => 20.0,
                'gross' => 120.0,
                'company' => [
                    'name' => 'Customer Company',
                    'address' => [
                        'line1' => 'line 1',
                        'line2' => 'line 2',
                        'city' => 'City',
                        'postcode' => 'PO57 C0D',
                    ]
                ],
                'items' => [
                    [
                        'quantity' => 1,
                        'description' => 'Description 1',
                        'net' => 1.5,
                        'taxPercent' => 20,
                        'gross' => 1.75,
                    ]
                ],
                'keyValuePairs' => [
                    [
                        'key' => 'k1',
                        'value' => 'v1',
                    ]
                ],
            ],
            'logoHeight' => 40,
            'emailTelUnderLogo' => true,
        ];
        $this->json('POST', '/purchaseOrder', $data);
        // Assert HTTP status code
        $this->assertEquals(201, $this->response->getStatusCode());
        // Verify PDF contents
        $parser = new Parser();
        $pdf = $parser->parseContent($this->response->getContent());
        $pdfText = $pdf->getText();
        $this->assertStringContainsString($data['company']['name'], $pdfText);
        $this->assertStringContainsString($data['company']['telephone'], $pdfText);
        $this->assertStringContainsString(implode("\n", $data['company']['address']), $pdfText);
        $this->assertStringContainsString($data['purchaseOrder']['notes'], $pdfText);
    }
}
