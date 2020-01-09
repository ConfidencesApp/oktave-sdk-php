# Oktave PHP SDK

This official PHP SDK for interacting with **Oktave**.

## Installation

You can install the package manually or by adding it to your `composer.json`:

```
{
  "require": {
      "confidencesapp/oktave-sdk-php": "^0.1.0"
  }
}
```

## Instantiating the SDK Client:

Pass in the configuration to the client:

```php
$config = [
    'client_id' => '{your_client_uuid}',
    'client_secret' => '{your_client_secret}',
    'webhook_secret' => '{your_webhook_secret}', // optional, required for request signature validation
];
$oktave = new Oktave\Client($config);
```

Or configure after construct:

```php
$oktave = new Oktave\Client()
            ->setClientID('uuid')
            ->setClientSecret('ok_cltsec_...')
            ->setWebhookSecret('ok_whsec_...'); // optional, required for request signature validation
```

**Note:** if you are unsure what your `client_id`, `client_secret` or `webhook_secret` are, please go to
[your account](https://app.oktave.co/account/developer) and copy them.

## For multiple teams users:

Pass in the team ID to the client:

```php
$config = [
    // ...
    'team' => '{your_team_uuid}'
];
$oktave = new Oktave\Client($config);
```

Or configure after construct:

```php
$oktave = new Oktave\Client($config)
            ->setTeam('uuid'); // optional, required to specify a team ID
```

**Attention!** If no `team` is specified, the team on which the OAuth client has been declared is used by default.

**Note:** if you are unsure what your `team` is, please go to
[your account](https://app.oktave.co/account/developer) and copy it.

Reset to the default team without its ID:

```php
// set the team to null.
$oktave = new Oktave\Client($config)
            ->setTeam(null);
```

**Note:** the team value can be updated at anytime, for example between resource calls.

## On-Premise Customers

If you are an on-premise customer and have your own infrastructure with your own domain, you can configure the client to use your domain:

```php
$oktave->setBaseURL('https://api.yourdomain.com');
```

Or by adding the `api_endpoint` field to the `$config` array you pass to the constructor.

## Using the client

### Multiple Resources

To return a list of your resources

```php
// return a list of your blacklist items 
$oktave->blacklistItems->all();
```

### Pagination

To return a paginated list of your resources

```php
// return a list of your paginated blacklist items
// items per page accepted values : 10, 20, 50, 100

$result = $oktave->blacklistItems->perPage(20)->page(5)->all();
$result->data() // contains the ressource collection
$result->meta() // contains the current pagination meta

/* [ 'current_page' => 5, 'per_page' => 20, 'total' => 95 ] */
```

### Single Resource by ID

Fetch a Resource by ID:

```php
$oktave->blacklistItems->get($blacklistItemID);
```

## Handling Exceptions

Aside from errors that may occur due to the call, there may be other Exceptions thrown. To handle them, simply wrap your call in a try catch block:

```php
try {
    $oktave->blacklistItems->all();
} catch (Exception $e) {
    // do something with $e
}
```

Internally, there are several custom Exceptions which may be raised - see the [Exceptions](src/Exceptions) directory for more information.


### Webhook request verification

To verify a webhook request signature

```php
// return true if the request signature is valid 
$oktave->webhooks->verifySignatureFromGlobals();
```

```php

$eventID = isset($_SERVER['HTTP_OKTAVE_EVENT_ID']) ? $_SERVER['HTTP_OKTAVE_EVENT_ID'] : null;
$requestTimestamp = isset($_SERVER['HTTP_OKTAVE_TIMESTAMP']) ? (int) $_SERVER['HTTP_OKTAVE_TIMESTAMP'] : null;
$signature = isset($_SERVER['HTTP_OKTAVE_SIGNATURE']) ? $_SERVER['HTTP_OKTAVE_SIGNATURE'] : null;

// return true if the request signature is valid 
$oktave->webhooks->verifySignature($eventID, $requestTimestamp, $signature);
```

## Test

```bash
phpunit
```

Generate a coverage report:

```bash
phpunit --coverage-html ./ignore
```
