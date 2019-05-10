<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

use Voquis\Document\Invoice as InvoiceDocument;
use Voquis\Schema\Base\Address;
use Voquis\Schema\Base\Company;
use Voquis\Schema\Base\Invoice as InvoiceBase;
use Voquis\Schema\Collection\InvoiceItems;
use Voquis\Schema\Collection\KeyValuePairs;

class InvoiceController extends Controller
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

        $document = new InvoiceDocument(
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
            new InvoiceBase(
                new Company(
                    new Address([
                        'line1' => $json['invoice']['company']['address']['line1'] ?? null,
                        'line2' => $json['invoice']['company']['address']['line2'] ?? null,
                        'line3' => $json['invoice']['company']['address']['line3'] ?? null,
                        'city' => $json['invoice']['company']['address']['city'] ?? null,
                        'county' => $json['invoice']['company']['address']['county'] ?? null,
                        'postcode' => $json['invoice']['company']['address']['postcode'] ?? null,
                    ]),
                    [
                        'name' => $json['invoice']['company']['name'] ?? null,
                    ]
                ),
                new InvoiceItems($json['invoice']['items'] ?? []),
                new KeyValuePairs($json['invoice']['keyValuePairs'] ?? []),
                [
                    'ref' => $json['invoice']['ref'] ?? null,
                    'summary' => $json['invoice']['summary'] ?? null,
                    'notes' => $json['invoice']['notes'] ?? null,
                    'instructions' => $json['invoice']['instructions'] ?? null,
                    'symbol' => $json['invoice']['symbol'] ?? null,
                    'net' => $json['invoice']['net'] ?? null,
                    'tax' => $json['invoice']['tax'] ?? null,
                    'gross' => $json['invoice']['gross'] ?? null,
                ]
            ),
            []
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
