<?php

use Andriichuk\Bsg\Requests\SendSmsRequest;

it('omits null SMS options', function () {
    $request = new SendSmsRequest(
        msisdn: '+380 99 111-22-33',
        originator: 'Sender',
        body: 'Hello',
    );

    expect($request->toArray())->toBe([
        'destination' => 'phone',
        'msisdn' => '380991112233',
        'originator' => 'Sender',
        'body' => 'Hello',
    ]);
});
