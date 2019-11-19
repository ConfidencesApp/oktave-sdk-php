# oktave PHP SDK

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
    'client_id' => '{your_client_id}',
    'client_secret' => '{your_client_secret}',
];
$oktave = new Oktave\Client($config);
```

Or configure after construct:

```php
$oktave = new Oktave\Client()
            ->setClientID('xxx')
            ->setClientSecret('yyy');
```

**Note:** if you are unsure what your `client_id` or `client_secret` are, please select the
[store in your account](https://app.oktave.co/account/developer) and copy them.

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

## Test

```bash
phpunit
```

Generate a coverage report:

```bash
phpunit --coverage-html ./ignore
```
