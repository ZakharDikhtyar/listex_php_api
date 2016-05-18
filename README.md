# listex_php_api

## Initialize

```php
include_once 'listex_php_api/ListexApi.php';

$api = new \ListexApi\ListexApi();
```

## Use

```php
// get product by id
$res = $api->getProduct(127477);

// get list of TM
$res = $api->getTrademarksList();
```