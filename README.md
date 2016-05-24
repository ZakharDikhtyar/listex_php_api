# listex_php_api

## Initialize

```php
include_once './ListexApi.php';

$api = new \Listex\Api();
```

## Use

```php
// get product by id
$result = $api->getProduct(127477);

// get list of TM
$result = $api->getTrademarksList();
```
