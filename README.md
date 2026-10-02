# BSG World PHP SDK

[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/andriichuk/bsg-php-sdk/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/andriichuk/bsg-php-sdk/actions/workflows/run-tests.yml)

Framework-agnostic PHP SDK for the [BSG World REST API](https://bsg.world/developers/rest-api). It currently supports sending a single SMS and retrieving its delivery status.

## Installation

```bash
composer require andriichuk/bsg-php-sdk
```

## Send an SMS

```php
use Andriichuk\Bsg\BsgClient;
use Andriichuk\Bsg\Requests\SendSmsRequest;

$client = new BsgClient('live_your_api_key');

$sent = $client->sendSms(new SendSmsRequest(
    msisdn: '+380991112233',
    originator: 'YourSender',
    body: 'You have been invited!',
    reference: 'invite42',
));

$sent->id;
$sent->reference;
$sent->price;
$sent->currency;
```

Optional request parameters are `reference`, `validity`, `tariff`, and `twoWay`. Phone numbers are normalized to digits before being sent.

## Get SMS status

Use either the BSG message ID returned by `sendSms()` or your external reference:

```php
$status = $client->getSmsStatus($sent->id);
$sameStatus = $client->getSmsStatusByReference('invite42');

$status->status;   // e.g. "delivered"
$status->timeDr;   // delivery-report time in UTC
$status->msisdn;
$status->price;
$status->currency;
```

Both methods return `SmsStatusResponse`. BSG API failures throw `BsgApiException`, which exposes `errorCode` and `errorDescription`.

## Development

```bash
composer test
composer analyse
composer format
```

## License

The MIT License. See [LICENSE](LICENSE.md) for details.
