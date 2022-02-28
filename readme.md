# Creamailer API - PHP

The official PHP client library for the Creamailer API.

## Requirements

PHP 5.6 or above.

## Installation

You can install Creamailer API using Composer:

```
composer require creamailer/creamailer-api-sdk
```

After installation:
* If your appliaction doesn't have autoloader, add it with line: ``require("vendor/autoload.php")``

## Examples

To use the Creamailer API you need ``CREAMAILER_ACCESS_TOKEN`` and ``CREAMAILER_SHARED_SECRET``.
You can obtain the keys by logging in to Creamailer and selecting: ``Username > Settings > API``.

### Getting Started

Start by use-ing the class and creating an instance with your API keys.

```
use Creamailer\Creamailer;

$creamailer = new Creamailer(YOUR_CREAMAILER_ACCESS_TOKEN, YOUR_CREAMAILER_SHARED_SECRET);
```

### Testing the Connection

You can test connection with the ping method as below.

```
$result = $creamailer->ping();

if ( ! $result->success) {
    echo 'Error: ' . $result->message;
}

print_r($result);
```

## Lists

### Create list
```
$listName = 'My list';
$listLanguage = 'fi';
$listAutoSuppress = true;

$result = $creamailer->lists()->create(
    $listName,
    $listLanguage,
    $listAutoSuppress
);

print_r($result);
```

### Get list
```
$listId = 1234;

$result = $creamailer->lists()->show(
    $listId
);

print_r($result);
```

### Get all lists

```
$result = $creamailer->lists()->showMany();

print_r($result);
```

### Get list subscribers
```
$listId = 1234;

$result = $creamailer->lists()->subscribers(
    $listId
);

print_r($result);
```

### Update list
```
$listId = 1234;
$listName = 'My list';
$listLanguage = 'fi';
$listAutoSuppress = true;

$result = $creamailer->lists()->update(
    $listId,
    $listName,
    $listLanguage,
    $listAutoSuppress
);

print_r($result);
```

### Delete list
```
$listId = 1234;

$result = $creamailer->lists()->delete(
    $listId = 1234
);

print_r($result);
```
## Subscribers
###  Create subscriber
```
$listId = 1234;
$email = 'name@example.com';
$name = 'Firstname Lastname';
$company = 'Company Name';
$address = 'Street 1';
$city = 'Helsinki';
$zip_code = '00100';
$country = 'Finland';
$phone = '040123456';
$customer_number = '1234';
$send_autoresponders = true;
$send_autoresponders_if_exists = true;
$status = 'active';

// If list has custom fields
$some_custom_field = 'some extra info';

$result = $creamailer->subscribers()->create(
    $listId,
    [
        'email' => $email,
        'name' => $name,
        'company' => $company,
        'address' => $address,
        'company' => $company,
        'city' => $city,
        'zip_code' => $zip_code,
        'country' => $country,
        'phone' => $phone,
        'customer_number' => $customer_number,
        'send_autoresponders' => $send_autoresponders,
        'send_autoresponders_if_exists' => $send_autoresponders_if_exists,
        'status' => $status,
        'custom_fields' => [
            'some_custom_field' => $some_custom_field
        ]
    ]
);

print_r($result);
```
**Note:** Use custom_fields only if they exists on the list.

### Get subscriber
```
$listId = 1234;

$result = $creamailer->lists()->delete(
    $listId = 1234
);

print_r($result);
```
### Update subscriber
```
$listId = 1234;
$email = 'name@example.com';
$name = 'Firstname Lastname';
$status' => 'active',

$result = $creamailer->subscribers()->update(
    $listId,
    [
        'email' => $email,
        'name' => $name,
        'status' => status
    ]
);

print_r($result);
```
### Delete subscriber
```
$listId = 1234;
$email = 'name@example.com';

$result = $creamailer->subscribers()->delete(
    $listId,
    $email
);

print_r($result);
```
## Suppressions

### Create suppression
Add email to suppressions list.
```
$email = 'name@example.com';
 
$result = $creamailer->suppressions()->create(
    $email
);

print_r($result);
```
### Get all suppressions
```
$result = $creamailer->suppressions()->show();
 
print_r($result);
```
### Delete suppression
Delete email from suppressions list.
```
$email = 'name@example.com';
 
$deleteResult = $creamailer->suppressions()->delete(
    $email
);

print_r($result);
```

## Support and Feedback

In case you find any bugs, submit an issue directly here in GitHub.

Also for future improvement requests, please rise an issue so we can discuss it further.

We do PHPUnit unit tests with this library, but since it's an API, we don't release the tests. We don't recommend anyone to run tests agains our public API address.