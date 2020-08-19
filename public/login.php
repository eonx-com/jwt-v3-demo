<?php

use Carbon\Carbon;

require_once('../vendor/autoload.php');

$config = (require('../config.php'));

// create random User and Address claims.
$faker = Faker\Factory::create();
$user = [
    'first_name' => $faker->firstName,
    'last_name' => $faker->lastName,
    'email' => $faker->email,
    'mobile' => $faker->regexify('04\d{8}'),
];
$address = [
    "first_name" => $user['first_name'],
    "last_name" => $user['last_name'],
    "email" => $user['email'],
    "street_address" => $faker->streetAddress,
    "suburb" => $faker->city,
    "state" => $faker->randomElement(['VIC', 'NSW', 'SA', 'QLD', 'TAS']),
    "postcode" => (string)$faker->numberBetween(1000, 9999),
];

// build and sign token
$key = new \Lcobucci\JWT\Signer\Key($config['key']);
$signer = new \Lcobucci\JWT\Signer\Hmac\Sha256();
$builder = new \Lcobucci\JWT\Builder();
$token = $builder
    ->relatedTo($faker->uuid)
    ->identifiedBy($faker->uuid)
    ->permittedFor('https://sso.ewallet.com.au/auth')
    ->issuedBy($config['issuer'])
    ->issuedAt(Carbon::now()->getTimestamp())
    ->expiresAt(Carbon::now()->addMinute()->getTimestamp())
    ->canOnlyBeUsedAfter(Carbon::now()->getTimestamp())
    ->withClaim('ver', '3.0')
    ->withClaim('https://rewards.eonx.com/user', $user)
    ->withClaim('https://rewards.eonx.com/user/address', $address)
    ->getToken($signer, $key);

$request = ['authToken' => (string)$token];
error_log(print_r($request, true));

// send token to AuthEndpoint
$client = new \GuzzleHttp\Client();
$response = $client->post($config['endpoint'], [
    'json' => $request
]);

$data = json_decode((string)$response->getBody(), true);

error_log(print_r($data, true));

$target = sprintf('%s?%s',
    $data['data']['target'],
    http_build_query(
        ['accessToken' => $data['data']['accessToken']]
    )
);

error_log($target);

http_response_code(302);
header(sprintf('Location: %s', $target));

