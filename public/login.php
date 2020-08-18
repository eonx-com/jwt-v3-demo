<?php

require_once ('../vendor/autoload.php');

$config = (require ('../config.php'));

// create random User and Address claims.
$faker = Faker\Factory::create();
$user = [
    'first_name' => $faker->firstName,
    'last_name' => $faker->lastName,
    'email' => $faker->email,
    'mobile' => $faker->regexify('04\d{10}'),
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
$now = \Carbon\Carbon::now();
$key = new \Lcobucci\JWT\Signer\Key($config['key']);
$signer = new \Lcobucci\JWT\Signer\Hmac\Sha256();
$builder = new \Lcobucci\JWT\Builder();
$token = $builder
    ->relatedTo($faker->uuid)
    ->identifiedBy($faker->uuid)
    ->permittedFor('https://sso.ewallet.com.au/auth')
    ->issuedBy($config['issuer'])
    ->issuedAt($now->getTimestamp())
    ->expiresAt($now->addMinute()->getTimestamp())
    ->canOnlyBeUsedAfter($now->getTimestamp())
    ->withClaim('ver', '3.0')
    ->withClaim('https://rewards.eonx.com/user', $user)
    ->withClaim('https://rewards.eonx.com/user/address', $address)
    ->getToken($signer, $key);

error_log((string)$token);

// send token to AuthEndpoint
$client = new \GuzzleHttp\Client();
$response = $client->post($config['endpoint'], [
    'json' => ['authToken' => (string)$token]
]);



