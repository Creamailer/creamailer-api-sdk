# Creamailer API v1 - PHP

The official PHP client library for the Creamailer API.

[[toc]]

## Requirements

PHP 5.6 or above.

## Installation

You can install Creamailer API using Composer:

```
composer require creamailer/creamailer-api-v1
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
```

## Lists

### Create list
```
$listName = 'My list';
$listLanguage = 'fi';
$listAutoSuppress = true;

$result = $this->creamailer->lists()->create(
    $listName,
    $listLanguage,
    $listAutoSuppress
);

print_r($result);
```

### Get single list
```
$listId = 1234;

$result = $this->creamailer->lists()->show(
    $listId
);

print_r($result);
```

### Get all lists

```
$result = $this->creamailer->lists()->showMany();

print_r($result);
```

### Get list subscribers
```
$listId = 1234;

$getResult = $this->creamailer->lists()->subscribers(
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

$result = $this->creamailer->lists()->update(
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

$result = $this->creamailer->lists()->delete(
    $listId = 1234
);

print_r($result);
```
