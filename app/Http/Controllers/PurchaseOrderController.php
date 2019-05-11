<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

use Voquis\Document\PurchaseOrder as PurchaseOrderDocument;
use Voquis\Schema\Base\Address;
use Voquis\Schema\Base\Company;
use Voquis\Schema\Base\PurchaseOrder as PurchaseOrderBase;
use Voquis\Schema\Collection\PurchaseOrderItems;
use Voquis\Schema\Collection\KeyValuePairs;

class PurchaseOrderController extends Controller
{
    /**
     * Generate PDF document
     *
     * @param  Request  $request
     * @return Response
     */
    public function pdf(Request $request)
    {
        // Only allow Content-Type: application/json
        if (!$request->isJson()) {
            return new Response('Unexpected Content-Type, expecting application/json', 400);
        }

        $post = $request->post();
        $json = is_array($post) ? $post : [];

        $document = new PurchaseOrderDocument(
            new Company(
                new Address([
                    'line1' => $json['company']['address']['line1'] ?? null,
                    'line2' => $json['company']['address']['line2'] ?? null,
                    'line3' => $json['company']['address']['line3'] ?? null,
                    'city' => $json['company']['address']['city'] ?? null,
                    'county' => $json['company']['address']['county'] ?? null,
                    'postcode' => $json['company']['address']['postcode'] ?? null,
                ]),
                [
                    'name' => $json['company']['name'] ?? null,
                    'number' => $json['company']['number'] ?? null,
                    'vatNumber' => $json['company']['vatNumber'] ?? null,
                    'email' => $json['company']['email'] ?? null,
                    'telephone' => $json['company']['telephone'] ?? null,
                    'website' => $json['company']['website'] ?? null,
                    'logoUrl' => $json['company']['logoUrl'] ?? null,
                ]
            ),
            new PurchaseOrderBase(
                new Company(
                    new Address([
                        'line1' => $json['purchaseOrder']['company']['address']['line1'] ?? null,
                        'line2' => $json['purchaseOrder']['company']['address']['line2'] ?? null,
                        'line3' => $json['purchaseOrder']['company']['address']['line3'] ?? null,
                        'city' => $json['purchaseOrder']['company']['address']['city'] ?? null,
                        'county' => $json['purchaseOrder']['company']['address']['county'] ?? null,
                        'postcode' => $json['purchaseOrder']['company']['address']['postcode'] ?? null,
                    ]),
                    [
                        'name' => $json['purchaseOrder']['company']['name'] ?? null,
                    ]
                ),
                new PurchaseOrderItems($json['purchaseOrder']['items'] ?? []),
                new KeyValuePairs($json['purchaseOrder']['keyValuePairs'] ?? []),
                [
                    'ref' => $json['purchaseOrder']['ref'] ?? null,
                    'summary' => $json['purchaseOrder']['summary'] ?? null,
                    'notes' => $json['purchaseOrder']['notes'] ?? null,
                    'symbol' => $json['purchaseOrder']['symbol'] ?? null,
                    'net' => $json['purchaseOrder']['net'] ?? null,
                    'tax' => $json['purchaseOrder']['tax'] ?? null,
                    'gross' => $json['purchaseOrder']['gross'] ?? null,
                ]
            ),
            [
                'logoHeight' => $json['logoHeight'],
                'emailTelUnderLogo' => $json['emailTelUnderLogo']
            ]
        );

        $response = new Response(
            $document->getPdf(),
            201,
            [
                'Content-Type' => 'application/pdf'
            ]
        );
        return $response;
    }
}
