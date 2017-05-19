# listex_php_api v3

## Initialize

```php
include_once './ListexApi.php';

$api = new \Listex\Api('012345abc');
```

## Use

```php
// get product by barcode
$result = $api->getProductsByGtin(5018066112433);

// get list of brands
$result = $api->getBrands();
```
