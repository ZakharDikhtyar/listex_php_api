# listex_php_api v3

## Initialize

```php
include_once './ListexApi.php';

$api = new \Listex\Api('012345abc');
```

## Use

### Response format

```php
// json format (default)
$api->setFormatJson();

// xml format
$api->setFormatXml();
```

### Calls limit

```php
// get api calls limit (call only after API request)
$usageLimit = $api->getUsageLimit();

// get current usage count requests (call only after API request)
$currentUsageCount = $api->getCurrentUsageCount();

// returns seconds until access is granted (call only after API request)
$retryAfter = $api->getRetryAfter();
```

### ETag

```php
// returns last ETag (execute only after calling a method that supports ETags)
$etag = $api->getLastETag();
```
## Methods for networks

```php
$result = $api->getAttributes();

$result = $api->getAttributes(14000);

$result = $api->getAttributes(14000, \Listex\Api::ATTRIBUTE_TYPE_MANDATORY);

$result = $api->getBrands();

try {
    $result = $api->getBrands(null, '384de79147a35a30');
} catch (\Listex\NotModifiedListexApiException $e) {
    echo 'Not modified';
}

$result = $api->getBrands(1);

try {
    $result = $api->getBrands(1, '384de79147a35a30');
} catch (\Listex\NotModifiedListexApiException $e) {
    echo 'Not modified';
}

$result = $api->getSuppliers('11111111');

$result = $api->getCategories();

try {
    $result = $api->getCategories('07ab630cecd3a992');
} catch (\Listex\NotModifiedListexApiException $e) {
    echo 'Not modified';
}

$result = $api->getProductsById(2);

try {
    $result = $api->getProductsById(2, '0758dbb0d820aa98');
} catch (\Listex\NotModifiedListexApiException $e) {
    echo 'Not modified';
}

$result = $api->getProductsById(2, null, DateTime::createFromFormat('Y-m-d', '2025-01-01'));


$result = $api->getProductsByGtin('4011100091108');

try {
    $result = $api->getProductsByGtin('4011100091108', '0758dbb0d820aa98');
} catch (\Listex\NotModifiedListexApiException $e) {
    echo 'Not modified';
}

$result = $api->getProductsByGtin('4011100091108', null, DateTime::createFromFormat('Y-m-d', '2025-01-01'));


$result = $api->getProductsByLtin('4011100091108', 1);

try {
    $result = $api->getProductsByLtin('4011100091108', 1, '0758dbb0d820aa98');
} catch (\Listex\NotModifiedListexApiException $e) {
    echo 'Not modified';
}

$result = $api->getProductsByLtin('4011100091108', 1, null, DateTime::createFromFormat('Y-m-d', '2025-01-01'));


$result = $api->getProductsBySku('4011100091108', 1);

try {
    $result = $api->getProductsBySku('4011100091108', 1, '0758dbb0d820aa98');
} catch (\Listex\NotModifiedListexApiException $e) {
    echo 'Not modified';
}

$result = $api->getProductsBySku('4011100091108', 1, null, DateTime::createFromFormat('Y-m-d', '2025-01-01'));


$result = $api->getETagsList(1);

$result = $api->getETagsListPaginated(1);

$result = $api->getETagsListPaginated(2, 100000);

$result = $api->getSuggestions('test');

$result = $api->addReviewToGood(1, 'comment', \Listex\Api::SOCIAL_TYPE_GOOGLE_PLUS, '10000000', 'Name', 5);

$result = $api->addReviewToParty(1, 'comment', \Listex\Api::SOCIAL_TYPE_GOOGLE_PLUS, '10000000', 'Name', 5);

$result = $api->addReviewToBrand(1, 'comment', \Listex\Api::SOCIAL_TYPE_GOOGLE_PLUS, '10000000', 'Name', 5);

$result = $api->addReplyToReview(5587, 'comment', \Listex\Api::SOCIAL_TYPE_GOOGLE_PLUS, '10000000', 'Name', 5);

$result = $api->getImage('https://icf.listex.info/300x200/5a7eb614-13d3-69ed-caf7-420624d1bdd3.jpg', 500, 500, 1);

$result = $api->getLocations();

$result = $api->getLocations(1);

$result = $api->getPalletizationById(1);

$result = $api->getPalletizationByGtin('4011100091108');

$result = $api->getPalletizationByLtin('4011100091108', 1);

$result = $api->getPalletizationBySku('4011100091108', 1);

$result = $api->getNoveltyProducts('2025-01-01');

$result = $api->getNoveltyProducts('2025-01-01', '2025-02-28');
```

## Methods for suppliers

```php
$result = $api->getAttributes();

$result = $api->getAttributes(14000);

$result = $api->getAttributes(14000, \Listex\Api::ATTRIBUTE_TYPE_MANDATORY);

$result = $api->getBrands(1);

try {
    $result = $api->getBrands(1, '384de79147a35a30');
} catch (\Listex\NotModifiedListexApiException $e) {
    echo 'Not modified';
}

$result = $api->getCategories();

$result = $api->getSupplierProductsById(2);

try {
    $result = $api->getSupplierProductsById(2, '0758dbb0d820aa98');
} catch (\Listex\NotModifiedListexApiException $e) {
    echo 'Not modified';
}

$result = $api->getSupplierProductsByGtin('4011100091108');

try {
    $result = $api->getSupplierProductsByGtin('4011100091108', '0758dbb0d820aa98');
} catch (\Listex\NotModifiedListexApiException $e) {
    echo 'Not modified';
}

$result = $api->getSupplierProductsByLtin('4011100091108', 1);

try {
    $result = $api->getSupplierProductsByLtin('4011100091108', 1, '0758dbb0d820aa98');
} catch (\Listex\NotModifiedListexApiException $e) {
    echo 'Not modified';
}

$result = $api->getSupplierProductsBySku('4011100091108', 1);

try {
    $result = $api->getSupplierProductsBySku('4011100091108', 1, '0758dbb0d820aa98');
} catch (\Listex\NotModifiedListexApiException $e) {
    echo 'Not modified';
}

$result = $api->getSupplierETagsList(1);

$result = $api->addReviewToGood(1, 'comment', \Listex\Api::SOCIAL_TYPE_GOOGLE_PLUS, '10000000', 'Name', 5);

$result = $api->addReviewToParty(1, 'comment', \Listex\Api::SOCIAL_TYPE_GOOGLE_PLUS, '10000000', 'Name', 5);

$result = $api->addReviewToBrand(1, 'comment', \Listex\Api::SOCIAL_TYPE_GOOGLE_PLUS, '10000000', 'Name', 5);

$result = $api->addReplyToReview(5587, 'comment', \Listex\Api::SOCIAL_TYPE_GOOGLE_PLUS, '10000000', 'Name', 5);

$result = $api->getImage('https://icf.listex.info/300x200/5a7eb614-13d3-69ed-caf7-420624d1bdd3.jpg', 500, 500, 1);

$result = $api->getLocations();

$result = $api->getLocations(1);

$result = $api->getPalletizationById(1);

$result = $api->getPalletizationByGtin('4011100091108');

$result = $api->getPalletizationByLtin('4011100091108', 1);

$result = $api->getPalletizationBySku('4011100091108', 1);
```

## Methods for technology partner

```php

$payload = [
    [
        'sku' => '6628',
        'product_name' => 'Напиток безалкогольный сильногазированный на ароматизаторах Original taste Coca-Cola п/бут 500мл',
        'on_shelves' => true,
        'planogram_create_date' => time(),
        'other_identifiers' => [
            [
                'id' => '54491472',
                'supplier_name' => 'The Coca-Cola Company',
                'supplier_identifier' => '35957550'
            ]       
        ]
    ]  
];

try {
    $result = $api->putPlanogramAssortment(2, $payload);
    $result = $api->postPlanogramAssortment(2, $payload);
   
} catch (\Listex\RequestErrorListexApiException $e) {
    echo $e->getResponseBody();
}

$payload = [
    'sku' => [
        '6628'
    ]
];

try {
    $result = $api->deletePlanogramAssortment(2, $payload);
   
} catch (\Listex\RequestErrorListexApiException $e) {
    echo $e->getResponseBody();
}

$result = $api->getPlanogramAssortment(2, ['6628']);

$result = $api->getPlanogramAssortmentETagList(2);

$result = $api->getRetailers();

$result = $api->getRetailers('11111111');
```
