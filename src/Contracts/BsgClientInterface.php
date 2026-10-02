<?php

declare(strict_types=1);

namespace Andriichuk\Bsg\Contracts;

use Andriichuk\Bsg\Requests\SendSmsRequest;
use Andriichuk\Bsg\Responses\SendSmsResponse;
use Andriichuk\Bsg\Responses\SmsStatusResponse;

interface BsgClientInterface
{
    public function sendSms(SendSmsRequest $request): SendSmsResponse;

    public function getSmsStatus(string|int $id): SmsStatusResponse;

    public function getSmsStatusByReference(string $reference): SmsStatusResponse;
}
