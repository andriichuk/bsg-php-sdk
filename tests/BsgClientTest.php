<?php

use Andriichuk\Bsg\BsgClient;
use Andriichuk\Bsg\Exceptions\BsgApiException;
use Andriichuk\Bsg\Requests\SendSmsRequest;
use Andriichuk\Bsg\Responses\SendSmsResponse;
use Andriichuk\Bsg\Responses\SmsStatusResponse;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\RequestOptions;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

it('sends a single SMS', function () {
    $httpClient = $this->createMock(ClientInterface::class);
    $stream = $this->createMock(StreamInterface::class);
    $response = $this->createMock(ResponseInterface::class);

    $stream->method('__toString')->willReturn('{"error":"0","id":"22125","reference":"invite42","price":"0.02","currency":"EUR"}');
    $response->method('getBody')->willReturn($stream);

    $httpClient->expects($this->once())
        ->method('request')
        ->with('POST', '/rest/sms/create', [
            RequestOptions::HEADERS => [
                'Accept' => 'application/json',
                'X-API-KEY' => 'live_test_key',
            ],
            RequestOptions::HTTP_ERRORS => false,
            RequestOptions::JSON => [
                'destination' => 'phone',
                'msisdn' => '380991112233',
                'originator' => 'Sender',
                'body' => 'Hello & welcome',
                'reference' => 'invite42',
                'validity' => 24,
                'tariff' => 2,
                '2way' => true,
            ],
        ])
        ->willReturn($response);

    $client = new BsgClient('live_test_key', $httpClient);
    $result = $client->sendSms(new SendSmsRequest(
        msisdn: '+380 99 111-22-33',
        originator: 'Sender',
        body: 'Hello & welcome',
        reference: 'invite42',
        validity: 24,
        tariff: 2,
        twoWay: true,
    ));

    expect($result)->toBeInstanceOf(SendSmsResponse::class)
        ->and($result->toArray())->toBe([
            'error' => '0',
            'id' => '22125',
            'reference' => 'invite42',
            'price' => '0.02',
            'currency' => 'EUR',
        ]);
});

it('gets an SMS status by BSG message ID', function () {
    $httpClient = $this->createMock(ClientInterface::class);
    $stream = $this->createMock(StreamInterface::class);
    $response = $this->createMock(ResponseInterface::class);

    $stream->method('__toString')->willReturn(json_encode([
        'error' => 0,
        'errorDescription' => 'No errors',
        'id' => '211',
        'msisdn' => '380972000001',
        'reference' => 'invite42',
        'time_in' => '2017-01-17 09:11:41',
        'time_sent' => '2017-01-17 09:11:42',
        'time_dr' => '2017-01-17 09:12:03',
        'status' => 'delivered',
        'price' => 0.23,
        'currency' => 'EUR',
    ], JSON_THROW_ON_ERROR));
    $response->method('getBody')->willReturn($stream);

    $httpClient->expects($this->once())
        ->method('request')
        ->with('GET', '/rest/sms/211', [
            RequestOptions::HEADERS => [
                'Accept' => 'application/json',
                'X-API-KEY' => 'live_test_key',
            ],
            RequestOptions::HTTP_ERRORS => false,
        ])
        ->willReturn($response);

    $client = new BsgClient('live_test_key', $httpClient);
    $status = $client->getSmsStatus(211);

    expect($status)->toBeInstanceOf(SmsStatusResponse::class)
        ->and($status->status)->toBe('delivered')
        ->and($status->toArray())->toBe([
            'error' => '0',
            'errorDescription' => 'No errors',
            'id' => '211',
            'msisdn' => '380972000001',
            'reference' => 'invite42',
            'time_in' => '2017-01-17 09:11:41',
            'time_sent' => '2017-01-17 09:11:42',
            'time_dr' => '2017-01-17 09:12:03',
            'status' => 'delivered',
            'price' => '0.23',
            'currency' => 'EUR',
        ]);
});

it('gets an SMS status by external reference', function () {
    $httpClient = $this->createMock(ClientInterface::class);
    $stream = $this->createMock(StreamInterface::class);
    $response = $this->createMock(ResponseInterface::class);

    $stream->method('__toString')->willReturn('{"error":"0","id":"211","reference":"invite42","status":"sent"}');
    $response->method('getBody')->willReturn($stream);

    $httpClient->expects($this->once())
        ->method('request')
        ->with('GET', '/rest/sms/reference/invite42', [
            RequestOptions::HEADERS => [
                'Accept' => 'application/json',
                'X-API-KEY' => 'live_test_key',
            ],
            RequestOptions::HTTP_ERRORS => false,
        ])
        ->willReturn($response);

    $client = new BsgClient('live_test_key', $httpClient);
    $status = $client->getSmsStatusByReference('invite42');

    expect($status->reference)->toBe('invite42')
        ->and($status->status)->toBe('sent');
});

it('throws a detailed exception for a BSG API error', function () {
    $httpClient = $this->createMock(ClientInterface::class);
    $stream = $this->createMock(StreamInterface::class);
    $response = $this->createMock(ResponseInterface::class);

    $stream->method('__toString')->willReturn('{"error":"20","errorDescription":"SMS not found"}');
    $response->method('getBody')->willReturn($stream);
    $httpClient->expects($this->once())->method('request')->willReturn($response);

    $client = new BsgClient('live_test_key', $httpClient);
    $client->getSmsStatus('missing');
})->throws(BsgApiException::class, 'BSG API error 20: SMS not found');

it('throws when BSG returns malformed JSON', function () {
    $httpClient = $this->createMock(ClientInterface::class);
    $stream = $this->createMock(StreamInterface::class);
    $response = $this->createMock(ResponseInterface::class);

    $stream->method('__toString')->willReturn('not-json');
    $response->method('getBody')->willReturn($stream);
    $httpClient->expects($this->once())->method('request')->willReturn($response);

    $client = new BsgClient('live_test_key', $httpClient);
    $client->getSmsStatus(211);
})->throws(RuntimeException::class, 'Could not parse BSG SMS status response JSON.');
