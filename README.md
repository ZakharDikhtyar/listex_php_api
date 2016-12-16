# listex_php_api v3

## Initialize

```php
include_once './ListexApi.php';

$api = new \Listex\Api();
```

## Use

```php
// get product by id
$result = $api->getProductById(127477);

// get list of brands
$result = $api->getBrands();
```
