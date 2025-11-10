<?php

namespace Listex;

/**
 * Class Api
 *
 * @package Listex
 */
class Api
{
    const API_URL = 'https://api.listex.info';

    const VERSION = 'v3';

    const RESPONSE_FORMAT_JSON = 'json';
    const RESPONSE_FORMAT_XML = 'xml';

    const METHOD_ATTRIBUTES = 'attributes';
    const METHOD_BRANDS = 'brands';
    const METHOD_SUPPLIERS = 'suppliers';
    const METHOD_CATEGORIES = 'categories';
    const METHOD_PRODUCTS = 'product';
    const METHOD_SUPPLIER_PRODUCTS = 'supplier-product';
    const METHOD_ETAGS_LIST = 'etagslist';
    const METHOD_SUPPLIER_ETAGS_LIST = 'supplier-etagslist';
    const METHOD_ETAGS_LIST_PAGINATED = 'etagslist-paginated';
    const METHOD_SUGGESTIONS = 'suggestions';
    const METHOD_ADD_REVIEW = 'addreview';
    const METHOD_IMAGE = 'image';
    const METHOD_LOCATIONS = 'locations';
    const METHOD_PALLETIZATION = 'palletization';
    const METHOD_NOVELTY_PRODUCTS = 'novelty-products';
    const METHOD_PLANOGRAM_ASSORTMENT = 'planogram-assortment';
    const METHOD_PLANOGRAM_ASSORTMENT_ETAGLIST = 'planogram-assortment-etaglist';

    const CODE_STATUS_OK = 200;
    const CODE_STATUS_NOT_MODIFIED = 304;
    const CODE_STATUS_REQUEST_ERROR = 400;
    const CODE_STATUS_NOT_AUTHORIZED = 401;
    const CODE_STATUS_NO_ACCESS = 403;
    const CODE_STATUS_NO_DATA_FOUND = 404;
    const CODE_STATUS_METHOD_NOT_ALLOWED = 405;
    const CODE_STATUS_LOCKED = 423;
    const CODE_STATUS_REQUEST_LIMIT_REACHED = 429;
    const CODE_STATUS_INTERNAL_SERVER_ERROR = 500;
    const CODE_STATUS_METHOD_NOT_FOUND = 501;
    const CODE_STATUS_SERVICE_NOT_AVAILABLE = 503;

    const HTTP_METHOD_GET = 'GET';
    const HTTP_METHOD_POST = 'POST';
    const HTTP_METHOD_PUT = 'PUT';
    const HTTP_METHOD_DELETE = 'DELETE';

    const ATTRIBUTE_TYPE_ALL = 'a';
    const ATTRIBUTE_TYPE_MANDATORY = 'm';
    const ATTRIBUTE_TYPE_RECOMMEND = 'r';
    const ATTRIBUTE_TYPE_OPTIONAL = 'o';

    const SOCIAL_TYPE_GOOGLE_PLUS = 'gp';
    const SOCIAL_TYPE_FACEBOOK = 'fb';
    const SOCIAL_TYPE_TWITTER = 'tw';
    const SOCIAL_TYPE_VK = 'vk';

    protected $apiKey;

    /** @var string */
    protected $format;

    /** @var int */
    protected $lastHttpCode;

    /** @var array */
    protected $lastHeaders;

    /**
     * ListexApi constructor.
     * @param string $apiKey
     */
    public function __construct(string $apiKey)
    {
        $this->apiKey = $apiKey;
        $this->setFormatJson();
    }

    /**
     * Set json results format
     * @return void
     */
    public function setFormatJson(): void
    {
        $this->format = self::RESPONSE_FORMAT_JSON;
    }

    /**
     * Set xml results format
     * @return void
     */
    public function setFormatXml(): void
    {
        $this->format = self::RESPONSE_FORMAT_XML;
    }

    /**
     * Return last HTTP Code
     * @return int
     */
    public function getLastHttpCode(): int
    {
        return $this->lastHttpCode;
    }

    /**
     * Return last ETag
     * @return null|string
     */
    public function getLastETag(): ?string
    {
        if (!isset($this->lastHeaders['ETag'])) {
            return null;
        }

        return $this->lastHeaders['ETag'];
    }

    /**
     * Return current usage count requests
     * @return null|int
     */
    public function getCurrentUsageCount(): ?int
    {
        if (!isset($this->lastHeaders['APIUsageLimit'])) {
            return null;
        }

        if (false === strpos($this->lastHeaders['APIUsageLimit'], '/')) {
            return null;
        }

        return explode('/', $this->lastHeaders['APIUsageLimit'])[0];
    }

    /**
     * Return requests limit
     * @return null|int
     */
    public function getUsageLimit(): ?int
    {
        if (!isset($this->lastHeaders['APIUsageLimit'])) {
            return null;
        }

        if (false === strpos($this->lastHeaders['APIUsageLimit'], '/')) {
            return null;
        }

        return explode('/', $this->lastHeaders['APIUsageLimit'])[1];
    }

    /**
     * Returns seconds until access is granted
     * @return int|null
     */
    public function getRetryAfter(): ?int
    {
        if (!isset($this->lastHeaders['RetryAfter'])) {
            return null;
        }

        return $this->lastHeaders['RetryAfter'];
    }

    /**
     * Send request and return pure response
     * @param  string  $method
     * @param  array  $params
     * @param  string  $format
     * @param  string|null  $eTag  ETag
     * @param  string  $httpMethod
     * @param  array  $payload
     * @return string
     */
    public function getPureResponse(
        string $method,
        array $params = [],
        string $format = self::RESPONSE_FORMAT_JSON,
        ?string $eTag = null,
        string $httpMethod = self::HTTP_METHOD_GET,
        array $payload = []
    ): string {

        $this->lastHttpCode = 0;
        $this->lastHeaders = [];
        $headers = [];

        $params['apikey'] = $this->apiKey;
        $params['format'] = $format;
        $url = self::getUrl($method) . '?' . http_build_query($params);

        if ($eTag) {
            $headers[] = 'If-None-Match: "' . $eTag . '"';
        }

        $curl = curl_init();
        switch ($httpMethod) {
            case self::HTTP_METHOD_GET:
                break;

            case self::HTTP_METHOD_POST:
                curl_setopt($curl, CURLOPT_POST, true);
                curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($payload, JSON_UNESCAPED_UNICODE));
                $headers[] = 'Content-Type: application/json';
                break;

            case self::HTTP_METHOD_PUT:
            case self::HTTP_METHOD_DELETE:
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $httpMethod);
                curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($payload, JSON_UNESCAPED_UNICODE));
                $headers[] = 'Content-Type: application/json';
                break;

            default:
                throw new \InvalidArgumentException('Unsupported HTTP method: ' . $httpMethod);
        }

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_USERAGENT, self::getUserAgent());
        curl_setopt($curl, CURLOPT_HEADER, true);
        if ($headers) {
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        }

        $response = curl_exec($curl);
        $this->lastHttpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        if (false === $response) {
            throw new UnknownListexApiException('Curl error (' . curl_errno($curl) . '): ' . curl_error($curl));
        }

        $headerSize = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
        $header = substr($response, 0, $headerSize);
        $body = strlen($response) === $headerSize ? '' : substr($response, $headerSize);

        $this->lastHeaders = array_reduce(explode("\r\n", $header), function ($result, $header) {
            if (false === strpos($header, ':')) {
                return $result;
            }
            $key = explode(':', $header)[0];
            $value = trim(str_replace($key . ':', '', $header), " \t\"'");
            $result[str_replace('-', '', $key)] = $value;
            return $result;
        }, []);

        curl_close($curl);

        return $body;
    }

    /**
     * Get response
     * @param  string  $method
     * @param  array  $params
     * @param  string  $format
     * @param  string|null  $eTag  ETag
     * @param  string  $httpMethod
     * @param  array  $payload
     * @return string
     */
    public function getResponse(
        string $method,
        array $params = [],
        string $format = self::RESPONSE_FORMAT_JSON,
        ?string $eTag = null,
        $httpMethod = self::HTTP_METHOD_GET,
        array $payload = []
    ): string {

        $response = $this->getPureResponse($method, $params, $format, $eTag, $httpMethod, $payload);
        $httpCode = $this->getLastHttpCode();

        switch ($httpCode) {
            case self::CODE_STATUS_OK:
                break;
            case self::CODE_STATUS_NOT_MODIFIED:
                throw new NotModifiedListexApiException('Not modified');
            case self::CODE_STATUS_REQUEST_ERROR:
                throw new RequestErrorListexApiException('Request error', $httpCode, null, !empty($response) ? $response : null);
            case self::CODE_STATUS_NOT_AUTHORIZED:
                throw new NotAuthorizedListexApiException('Not authorized');
            case self::CODE_STATUS_NO_ACCESS:
                throw new NoAccessListexApiException('No access');
            case self::CODE_STATUS_NO_DATA_FOUND:
                $response = '';
                break;
            case self::CODE_STATUS_LOCKED:
                throw new LockedListexApiException('Locked');
            case self::CODE_STATUS_REQUEST_LIMIT_REACHED:
                throw new RequestLimitReachedListexApiException('Request limit reached');
            case self::CODE_STATUS_INTERNAL_SERVER_ERROR:
                throw new InternalServerErrorListexApiException('Internal server error');
            case self::CODE_STATUS_METHOD_NOT_FOUND:
                throw new MethodNotFoundListexApiException('Method not found');
            case self::CODE_STATUS_SERVICE_NOT_AVAILABLE:
                throw new ServiceNotAvailableListexApiException('Service not available');
            case self::CODE_STATUS_METHOD_NOT_ALLOWED:
                throw new MethodNotAllowedListexApiException('Http method not allowed');
            default:
                throw new UnknownListexApiException('Unknown error');
        }

        return $response;
    }

    /**
     * Return the user agent string
     * @return string
     */
    protected static function getUserAgent(): string
    {
        return 'Listex PHP API client ' . self::VERSION;
    }

    /**
     * Return method url
     * @param string $method
     * @return string
     */
    protected static function getUrl(string $method): string
    {
        return self::API_URL . '/' . self::VERSION . '/' . $method;
    }

    /**
     * Return list of attributes
     * @param int|null $catId category id
     * @param string|null $attrType attribute type (const)
     * @return bool|array
     */
    public function getAttributes(?int $catId = null, ?string $attrType = null): string
    {
        $params = [];

        if ($catId) {
            $params['cat_id'] = $catId;
        }

        if ($attrType) {
            $params['attr_type'] = $attrType;
        }

        return $this->getResponse(self::METHOD_ATTRIBUTES, $params, $this->format);
    }

    /**
     * Return list of brands
     * @param int|null $partyId
     * @param string|null $eTag ETag
     * @return string
     */
    public function getBrands(?int $partyId = null, ?string $eTag = null): string
    {
        $params = [];

        if ($partyId) {
            $params['party_id'] = $partyId;
        }

        return $this->getResponse(self::METHOD_BRANDS, $params, $this->format, $eTag);
    }

    /**
     * Return list of suppliers
     * @param string $identifier
     * @return string
     */
    public function getSuppliers(string $identifier): string
    {
        $params = [
            'identifier' => $identifier,
        ];

        return $this->getResponse(self::METHOD_SUPPLIERS, $params, $this->format);
    }

    /**
     * Return list of categories
     * @param string|null $eTag ETag
     * @return string
     */
    public function getCategories(string $eTag = null): string
    {
        return $this->getResponse(self::METHOD_CATEGORIES, [], $this->format, $eTag);
    }

    /**
     * Return information about product by id
     * @param int $goodId
     * @param string|null $eTag ETag
     * @return string
     */
    public function getProductsById(int $goodId, ?string $eTag = null): string
    {
        $params = [
            'good_id' => $goodId
        ];

        return $this->getResponse(self::METHOD_PRODUCTS, $params, $this->format, $eTag);
    }

    /**
     * Return information about products by GTIN
     * @param string $gtin
     * @param string|null $eTag ETag
     * @return string
     */
    public function getProductsByGtin(string $gtin, ?string $eTag = null): string
    {
        $params = [
            'gtin' => $gtin
        ];

        return $this->getResponse(self::METHOD_PRODUCTS, $params, $this->format, $eTag);
    }

    /**
     * Return information about products by LTIN
     * @param string $ltin
     * @param int $partyId
     * @param string|null $eTag ETag
     * @return string
     */
    public function getProductsByLtin(string $ltin, int $partyId, ?string $eTag = null): string
    {
        $params = [
            'ltin' => $ltin,
            'party_id' => $partyId
        ];

        return $this->getResponse(self::METHOD_PRODUCTS, $params, $this->format, $eTag);
    }

    /**
     * Return information about products by SKU
     * @param string $sku
     * @param int $partyId
     * @param string|null $eTag ETag
     * @return string
     */
    public function getProductsBySku(string $sku, int $partyId, ?string $eTag = null): string
    {
        $params = [
            'sku' => $sku,
            'party_id' => $partyId
        ];

        return $this->getResponse(self::METHOD_PRODUCTS, $params, $this->format, $eTag);
    }

    /**
     * Return information about product by id
     * @param int $goodId
     * @param string|null $eTag ETag
     * @return string
     */
    public function getSupplierProductsById(int $goodId, ?string $eTag = null): string
    {
        $params = [
            'good_id' => $goodId
        ];

        return $this->getResponse(self::METHOD_SUPPLIER_PRODUCTS, $params, $this->format, $eTag);
    }

    /**
     * Return information about products by GTIN
     * @param string $gtin
     * @param string|null $eTag ETag
     * @return string
     */
    public function getSupplierProductsByGtin(string $gtin, ?string $eTag = null): string
    {
        $params = [
            'gtin' => $gtin
        ];

        return $this->getResponse(self::METHOD_SUPPLIER_PRODUCTS, $params, $this->format, $eTag);
    }

    /**
     * Return information about products by LTIN
     * @param string $ltin
     * @param int $partyId
     * @param string|null $eTag ETag
     * @return string
     */
    public function getSupplierProductsByLtin(string $ltin, int $partyId, ?string $eTag = null): string
    {
        $params = [
            'ltin' => $ltin,
            'party_id' => $partyId
        ];

        return $this->getResponse(self::METHOD_SUPPLIER_PRODUCTS, $params, $this->format, $eTag);
    }

    /**
     * Return information about products by SKU
     * @param string $sku
     * @param int $partyId
     * @param string|null $eTag ETag
     * @return string
     */
    public function getSupplierProductsBySku(string $sku, int $partyId, ?string $eTag = null): string
    {
        $params = [
            'sku' => $sku,
            'party_id' => $partyId
        ];

        return $this->getResponse(self::METHOD_SUPPLIER_PRODUCTS, $params, $this->format, $eTag);
    }

    /**
     * Return products etags
     * @param int $partyId
     * @return string
     */
    public function getETagsList(int $partyId): string
    {
        $params = [
            'party_id' => $partyId
        ];

        return $this->getResponse(self::METHOD_ETAGS_LIST, $params, $this->format);
    }

    /**
     * Return products etags
     * @param int $partyId
     * @return string
     */
    public function getSupplierETagsList(int $partyId): string
    {
        $params = [
            'party_id' => $partyId
        ];

        return $this->getResponse(self::METHOD_SUPPLIER_ETAGS_LIST, $params, $this->format);
    }

    /**
     * Return products etags with pagination
     * @param int $partyId
     * @param int|null $nextPageId
     * @return string
     */
    public function getETagsListPaginated(int $partyId, ?int $nextPageId = null): string
    {
        $params = [
            'party_id' => $partyId
        ];

        if ($nextPageId) {
            $params['next_page_id'] = $nextPageId;
        }

        return $this->getResponse(self::METHOD_ETAGS_LIST_PAGINATED, $params, $this->format);
    }

    /**
     * Return list of products
     * @param string $query
     * @return string
     */
    public function getSuggestions(string $query): string
    {
        $params = [
            'q' => $query
        ];

        return $this->getResponse(self::METHOD_SUGGESTIONS, $params, $this->format);
    }

    /**
     * Add review to good
     * @param int $goodId good id
     * @param string $reviewText message
     * @param string $socialType social network type (const)
     * @param string $socialId social network id
     * @param string $reviewAuthor author name
     * @param int $reviewRating rating
     * @return string
     */
    public function addReviewToGood(int $goodId, string $reviewText, string $socialType, string $socialId, string $reviewAuthor, int $reviewRating): string
    {
        $params = [
            'good_id' => $goodId,
            'review_text' => $reviewText,
            'social_type' => $socialType,
            'social_id' => $socialId,
            'review_author' => $reviewAuthor,
            'review_rating' => $reviewRating
        ];

        return $this->getResponse(self::METHOD_ADD_REVIEW, $params, $this->format);
    }

    /**
     * Add review to party
     * @param int $partyId party id
     * @param string $reviewText message
     * @param string $socialType social network type (const)
     * @param string $socialId social network id
     * @param string $reviewAuthor author name
     * @param int $reviewRating rating
     * @return string
     */
    public function addReviewToParty(int $partyId, string $reviewText, string $socialType, string $socialId, string $reviewAuthor, int $reviewRating): string
    {
        $params = [
            'party_id' => $partyId,
            'review_text' => $reviewText,
            'social_type' => $socialType,
            'social_id' => $socialId,
            'review_author' => $reviewAuthor,
            'review_rating' => $reviewRating
        ];

        return $this->getResponse(self::METHOD_ADD_REVIEW, $params, $this->format);
    }

    /**
     * Add review to brand
     * @param int $brandId brand id
     * @param string $reviewText message
     * @param string $socialType social network type (const)
     * @param string $socialId social network id
     * @param string $reviewAuthor author name
     * @param int $reviewRating rating
     * @return string
     */
    public function addReviewToBrand(int $brandId, string $reviewText, string $socialType, string $socialId, string $reviewAuthor, int $reviewRating): string
    {
        $params = [
            'brand_id' => $brandId,
            'review_text' => $reviewText,
            'social_type' => $socialType,
            'social_id' => $socialId,
            'review_author' => $reviewAuthor,
            'review_rating' => $reviewRating
        ];

        return $this->getResponse(self::METHOD_ADD_REVIEW, $params, $this->format);
    }

    /**
     * Add reply to review
     * @param int $reviewParentId parent review id
     * @param string $reviewText message
     * @param string $socialType social network type (const)
     * @param string $socialId social network id
     * @param string $reviewAuthor author name
     * @param int $reviewRating rating
     * @return string
     */
    public function addReplyToReview(int $reviewParentId, string $reviewText, string $socialType, string $socialId, string $reviewAuthor, int $reviewRating): string
    {
        $params = [
            'review_parent_id' => $reviewParentId,
            'review_text' => $reviewText,
            'social_type' => $socialType,
            'social_id' => $socialId,
            'review_author' => $reviewAuthor,
            'review_rating' => $reviewRating
        ];

        return $this->getResponse(self::METHOD_ADD_REVIEW, $params, $this->format);
    }

    /**
     * Image resize
     * @param string $name
     * @param int $width
     * @param int $height
     * @param int $noBackground
     * @return string
     */
    public function getImage(string $name, int $width, int $height, int $noBackground): string
    {
        $params = [
            'name' => $name,
            'width' => $width,
            'height' => $height,
            'no_background' => $noBackground,
        ];

        return $this->getResponse(self::METHOD_IMAGE, $params, $this->format);
    }

    /**
     * Return list of locations
     * @param int|null $partyId
     * @return string
     */
    public function getLocations(?int $partyId = null): string
    {
        $params = [];

        if ($partyId) {
            $params['party_id'] = $partyId;
        }

        return $this->getResponse(self::METHOD_LOCATIONS, $params, $this->format);
    }

    /**
     * Return information about palletization by id
     * @param int $goodId
     * @return string
     */
    public function getPalletizationById(int $goodId): string
    {
        $params = [
            'good_id' => $goodId
        ];

        return $this->getResponse(self::METHOD_PALLETIZATION, $params, $this->format);
    }

    /**
     * Return information about palletization by GTIN
     * @param string $gtin
     * @return string
     */
    public function getPalletizationByGtin(string $gtin): string
    {
        $params = [
            'gtin' => $gtin
        ];

        return $this->getResponse(self::METHOD_PALLETIZATION, $params, $this->format);
    }

    /**
     * Return information about palletization by LTIN
     * @param string $ltin
     * @param int $partyId
     * @return string
     */
    public function getPalletizationByLtin(string $ltin, int $partyId): string
    {
        $params = [
            'ltin' => $ltin,
            'party_id' => $partyId
        ];

        return $this->getResponse(self::METHOD_PALLETIZATION, $params, $this->format);
    }

    /**
     * Return information about palletization by SKU
     * @param string $sku
     * @param int $partyId
     * @return string
     */
    public function getPalletizationBySku(string $sku, int $partyId): string
    {
        $params = [
            'sku' => $sku,
            'party_id' => $partyId
        ];

        return $this->getResponse(self::METHOD_PALLETIZATION, $params, $this->format);
    }

    /**
     * Return information about novelty products
     * @param string $dateFrom
     * @param string|null $dateTo
     * @return string
     */
    public function getNoveltyProducts(string $dateFrom, ?string $dateTo = null): string
    {
        $params = [
            'date_from' => $dateFrom,
        ];

        if ($dateTo) {
            $params['date_to'] = $dateTo;
        }

        return $this->getResponse(self::METHOD_NOVELTY_PRODUCTS, $params, $this->format);
    }

    /**
     * Completely rewrites the assortment of planograms in Listex system
     * @param int $partyId
     * @param array $payload
     * @return string
     */
    public function putPlanogramAssortment(int $partyId, array $payload): string
    {
        $params = [
            'party_id' => $partyId,
        ];

        return $this->getResponse(
            self::METHOD_PLANOGRAM_ASSORTMENT,
            $params,
            $this->format,
            null,
            self::HTTP_METHOD_PUT,
            $payload
        );
    }

    /**
     * Adds new entries to Listex system or updates the existing assortment of planograms
     * @param int $partyId
     * @param array $payload
     * @return string
     */
    public function postPlanogramAssortment(int $partyId, array $payload): string
    {
        $params = [
            'party_id' => $partyId,
        ];

        return $this->getResponse(
            self::METHOD_PLANOGRAM_ASSORTMENT,
            $params,
            $this->format,
            null,
            self::HTTP_METHOD_POST,
            $payload
        );
    }


    /**
     * Deletes assortment data from planograms in Listex system
     * @param  int  $partyId
     * @param  array  $payload
     * @return string
     */
    public function deletePlanogramAssortment(int $partyId, array $payload): string
    {
        $params = [
            'party_id' => $partyId,
        ];

        return $this->getResponse(
            self::METHOD_PLANOGRAM_ASSORTMENT,
            $params,
            $this->format,
            null,
            self::HTTP_METHOD_DELETE,
            $payload
        );
    }

    /**
     * Obtaining a full range of planograms
     * @param int $partyId
     * @param array $skuList
     * @return string
     */
    public function getPlanogramAssortment(int $partyId, array $skuList = []): string
    {
        $params = [
            'party_id' => $partyId,
            'sku' => implode(',', $skuList)
        ];

        return $this->getResponse(self::METHOD_PLANOGRAM_ASSORTMENT, $params, $this->format);
    }

    /**
     * Facade for the etagslist method, which filters data according to what is available in the assortment of planograms for the account
     * @param int $partyId
     * @return string
     */
    public function getPlanogramAssortmentETagList(int $partyId): string
    {
        $params = [
            'party_id' => $partyId,
        ];

        return $this->getResponse(self::METHOD_PLANOGRAM_ASSORTMENT_ETAGLIST, $params, $this->format);
    }
}

class ListexApiException extends \RuntimeException
{
}

class UnknownListexApiException extends ListexApiException
{
}

class NotModifiedListexApiException extends ListexApiException
{
}

class RequestErrorListexApiException extends ListexApiException
{
    private $responseBody;

    public function __construct($message, $code = 0, $e = null, ?string $responseBody = null)
    {
        $this->responseBody = $responseBody;
        parent::__construct($message, $code, $e);
    }

    public function getResponseBody(): ?string
    {
        return $this->responseBody;
    }
}

class NotAuthorizedListexApiException extends ListexApiException
{
}

class NoAccessListexApiException extends ListexApiException
{
}

class LockedListexApiException extends ListexApiException
{
}

class RequestLimitReachedListexApiException extends ListexApiException
{
}

class InternalServerErrorListexApiException extends ListexApiException
{
}

class MethodNotFoundListexApiException extends ListexApiException
{
}

class ServiceNotAvailableListexApiException extends ListexApiException
{
}

class MethodNotAllowedListexApiException extends ListexApiException
{
}
