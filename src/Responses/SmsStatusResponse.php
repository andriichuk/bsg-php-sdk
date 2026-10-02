<?php

declare(strict_types=1);

namespace Andriichuk\Bsg\Responses;

final readonly class SmsStatusResponse
{
    public function __construct(
        public string $error,
        public ?string $errorDescription = null,
        public ?string $id = null,
        public ?string $msisdn = null,
        public ?string $reference = null,
        public ?string $timeIn = null,
        public ?string $timeSent = null,
        public ?string $timeDr = null,
        public ?string $status = null,
        public ?string $price = null,
        public ?string $currency = null,
    ) {}

    /**
     * @return array{error: string, errorDescription: string|null, id: string|null, msisdn: string|null, reference: string|null, time_in: string|null, time_sent: string|null, time_dr: string|null, status: string|null, price: string|null, currency: string|null}
     */
    public function toArray(): array
    {
        return [
            'error' => $this->error,
            'errorDescription' => $this->errorDescription,
            'id' => $this->id,
            'msisdn' => $this->msisdn,
            'reference' => $this->reference,
            'time_in' => $this->timeIn,
            'time_sent' => $this->timeSent,
            'time_dr' => $this->timeDr,
            'status' => $this->status,
            'price' => $this->price,
            'currency' => $this->currency,
        ];
    }
}
