<?php

declare(strict_types=1);

namespace Andriichuk\Bsg;

use Andriichuk\Bsg\Contracts\BsgClientInterface;
use Andriichuk\Bsg\Exceptions\BsgApiException;
use Andriichuk\Bsg\Requests\SendSmsRequest;
use Andriichuk\Bsg\Responses\SendSmsResponse;
use Andriichuk\Bsg\Responses\SmsStatusResponse;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\RequestOptions;
use RuntimeException;

/**
 * @see https://bsg.world/developers/rest-api/sending-sms
 * @see https://bsg.world/developers/rest-api/viewing-an-sms-status
 */
final class BsgClient implements BsgClientInterface
{
    private const string BASE_URI = 'https://api.bsg.world';

    private ClientInterface $httpClient;

    public function __construct(
        private readonly string $apiKey,
        ?ClientInterface $httpClient = null,
    ) {
        $this->httpClient = $httpClient ?? new Client([
            'base_uri' => self::BASE_URI,
        ]);
    }

    public function sendSms(SendSmsRequest $request): SendSmsResponse
    {
        $response = $this->httpClient->request('POST', '/rest/sms/create', [
            RequestOptions::HEADERS => $this->headers(),
            RequestOptions::HTTP_ERRORS => false,
            RequestOptions::JSON => $request->toArray(),
        ]);

        $decoded = $this->parseResponse((string) $response->getBody(), 'send SMS');

        return new SendSmsResponse(
            error: (string) $decoded['error'],
            id: isset($decoded['id']) ? (string) $decoded['id'] : null,
            reference: isset($decoded['reference']) ? (string) $decoded['reference'] : null,
            price: isset($decoded['price']) ? (string) $decoded['price'] : null,
            currency: isset($decoded['currency']) ? (string) $decoded['currency'] : null,
        );
    }

    public function getSmsStatus(string|int $id): SmsStatusResponse
    {
        return $this->requestSmsStatus('/rest/sms/'.rawurlencode((string) $id));
    }

    public function getSmsStatusByReference(string $reference): SmsStatusResponse
    {
        return $this->requestSmsStatus('/rest/sms/reference/'.rawurlencode($reference));
    }

    private function requestSmsStatus(string $uri): SmsStatusResponse
    {
        $response = $this->httpClient->request('GET', $uri, [
            RequestOptions::HEADERS => $this->headers(),
            RequestOptions::HTTP_ERRORS => false,
        ]);

        $decoded = $this->parseResponse((string) $response->getBody(), 'SMS status');

        return new SmsStatusResponse(
            error: (string) $decoded['error'],
            errorDescription: isset($decoded['errorDescription']) ? (string) $decoded['errorDescription'] : null,
            id: isset($decoded['id']) ? (string) $decoded['id'] : null,
            msisdn: isset($decoded['msisdn']) ? (string) $decoded['msisdn'] : null,
            reference: isset($decoded['reference']) ? (string) $decoded['reference'] : null,
            timeIn: isset($decoded['time_in']) ? (string) $decoded['time_in'] : null,
            timeSent: isset($decoded['time_sent']) ? (string) $decoded['time_sent'] : null,
            timeDr: isset($decoded['time_dr']) ? (string) $decoded['time_dr'] : null,
            status: isset($decoded['status']) ? (string) $decoded['status'] : null,
            price: isset($decoded['price']) ? (string) $decoded['price'] : null,
            currency: isset($decoded['currency']) ? (string) $decoded['currency'] : null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function parseResponse(string $json, string $operation): array
    {
        /** @var mixed $decoded */
        $decoded = json_decode($json, true);

        if (! is_array($decoded)) {
            throw new RuntimeException("Could not parse BSG {$operation} response JSON.");
        }

        if ((string) ($decoded['error'] ?? '') !== '0') {
            throw BsgApiException::fromResponse($decoded);
        }

        return $decoded;
    }

    /**
     * @return array{Accept: string, X-API-KEY: string}
     */
    private function headers(): array
    {
        return [
            'Accept' => 'application/json',
            'X-API-KEY' => $this->apiKey,
        ];
    }
}
