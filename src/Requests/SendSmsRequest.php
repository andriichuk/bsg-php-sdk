<?php

declare(strict_types=1);

namespace Andriichuk\Bsg\Requests;

final readonly class SendSmsRequest
{
    public function __construct(
        public string $msisdn,
        public string $originator,
        public string $body,
        public ?string $reference = null,
        public ?int $validity = null,
        public ?int $tariff = null,
        public ?bool $twoWay = null,
    ) {}

    /**
     * @return array{destination: 'phone', msisdn: string, originator: string, body: string, reference?: string, validity?: int, tariff?: int, '2way'?: bool}
     */
    public function toArray(): array
    {
        $payload = [
            'destination' => 'phone',
            'msisdn' => preg_replace('/\D+/', '', $this->msisdn) ?? '',
            'originator' => $this->originator,
            'body' => $this->body,
        ];

        if ($this->reference !== null) {
            $payload['reference'] = $this->reference;
        }

        if ($this->validity !== null) {
            $payload['validity'] = $this->validity;
        }

        if ($this->tariff !== null) {
            $payload['tariff'] = $this->tariff;
        }

        if ($this->twoWay !== null) {
            $payload['2way'] = $this->twoWay;
        }

        return $payload;
    }
}
