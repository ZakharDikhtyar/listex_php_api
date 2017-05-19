<?php

namespace Listex;

/**
 * Class Api
 *
 * @property string HeaderResponseCode
 * @property string HeaderETag
 * @property string HeaderAPIUsageLimit
 * @property string HeaderRetryAfter
 *
 * @package Listex
 */
final class Api
{
	const API_URL = 'https://api.listex.info';
	const VERSION = 'v3';

	const RESPONSE_FORMAT_JSON = 'json';
	const RESPONSE_FORMAT_XML = 'xml';

	const REQUEST_ENTITY_ATTRIBUTES = 'attributes';
	const REQUEST_ENTITY_BRANDS = 'brands';
	const REQUEST_ENTITY_CATEGORIES = 'categories';
	const REQUEST_ENTITY_PRODUCTS = 'product';
	const REQUEST_ENTITY_ETAGS_LIST= 'etagslist';
	const REQUEST_ENTITY_SUGGESTIONS = 'suggestions';
	const REQUEST_ENTITY_ADD_REVIEW = 'addreview';

	const CODE_STATUS_OK = 200;
	const CODE_STATUS_NOT_MODIFIED	= 304;
	const CODE_STATUS_REQUEST_ERROR	= 400;
	const CODE_STATUS_NOT_AUTHORIZED = 401;
	const CODE_STATUS_NO_ACCESS	= 403;
	const CODE_STATUS_NO_DATA_FOUND	= 404;
	const CODE_STATUS_REQUEST_LIMIT_REACHED	= 429;
	const CODE_STATUS_INTERNAL_SERVER_ERROR	= 500;
	const CODE_STATUS_METHOD_NOT_FOUND = 501;
	const CODE_STATUS_SERVICE_NOT_AVAILABLE	= 503;

	const ATTRIBUTE_TYPE_ALL = 'a';
	const ATTRIBUTE_TYPE_MANDATORY = 'm';
	const ATTRIBUTE_TYPE_RECOMMEND = 'r';
	const ATTRIBUTE_TYPE_OPTIONAL = 'o';

	const SOCIAL_TYPE_GOOGLE_PLUS = 'gp';
	const SOCIAL_TYPE_FACEBOOK = 'fb';
	const SOCIAL_TYPE_TWITTER = 'tw';
	const SOCIAL_TYPE_VK = 'vk';

	protected $apiKey;
	protected $apiUrl;

	/** @var string */
	private $_error;

	/** @var array */
	private $_headers;

	/**
	 * ListexApi constructor.
	 * @param $apiKey
	 */
	public function __construct($apiKey)
	{
		$this->apiUrl = self::API_URL . '/' . self::VERSION;
		$this->apiKey = $apiKey;
		$this->_error = null;
		$this->_headers = null;
	}

	/**
	 * @param $property
	 * @return mixed
	 */
	public function __get($property)
	{
		if ( 0 === strpos($property, 'Header') && array_key_exists($header = str_replace('Header', '', $property), $this->_headers) )
		{
			return $this->_headers[$header];
		}

		return null;
	}

	/**
	 * @return bool
	 */
	public function hasError()
	{
		return null !== $this->_error;
	}

	/**
	 * Return last error
	 * @return null|string
	 */
	public function getError()
	{
		return $this->_error;
	}

	/**
	 * Return last HTTP Code
	 * @return null|string
	 */
	public function getHttpCode()
	{
		return $this->HeaderResponseCode;
	}

	/**
	 * Return last ETag
	 * @return null|string
	 */
	public function getLastETag()
	{
		return $this->HeaderETag;
	}

	/**
	 * Return current usage count requests
	 * @return null|string
	 */
	public function getCurrentUsageCount()
	{
		if ( null !== $this->HeaderAPIUsageLimit && false !== strpos($this->HeaderAPIUsageLimit, '/') )
		{
			return explode('/', $this->HeaderAPIUsageLimit)[0];
		}

		return null;
	}

	/**
	 * Return requests limit
	 * @return null|string
	 */
	public function getUsageLimit()
	{
		if ( null !== $this->HeaderAPIUsageLimit && false !== strpos($this->HeaderAPIUsageLimit, '/') )
		{
			return explode('/', $this->HeaderAPIUsageLimit)[1];
		}

		return $this->HeaderAPIUsageLimit;
	}

	/**
	 * @return null|string
	 */
	public function getRetryAfter()
	{
		return $this->HeaderRetryAfter;
	}

	/**
	 * Send request and return pure response
	 *
	 * @param string $requestEntity
	 * @param array $params
	 * @param string $format
	 * @param string $ETag ETag
	 * @return bool|string Return the result on success, FALSE on failure
	 */
	public function getPureResponse($requestEntity, array $params=[], $format=self::RESPONSE_FORMAT_JSON, $ETag=null)
	{
		$this->_error = null;
		$this->_headers = null;

		if ( !array_key_exists('format', $params) )
		{
			$params['format'] = $format;
		}
		$params['apikey'] = $this->apiKey;

		$curl = curl_init();

		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_URL, $this->getUrl($requestEntity));
		curl_setopt($curl, CURLOPT_USERAGENT, $this->getUserAgent());
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
		curl_setopt($curl, CURLOPT_HEADER, true);
		if ( null !== $ETag )
		{
			curl_setopt($curl, CURLOPT_HTTPHEADER, ['If-None-Match: "' . $ETag . '"']); // fix
		}

		$response = curl_exec($curl);

		if ( false === $response )
		{
			$this->_error = 'Error (' . curl_errno($curl) . '): ' . curl_error($curl);
		}
		else
		{
			$header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
			$header = substr($response, 0, $header_size);
			if( strlen($response) === $header_size )
			{
				$body = '';
			}
			else
			{
				$body = substr($response, $header_size);
			}
			$response = $body;

			$this->_headers = array_reduce(explode("\r\n", $header), function($result, $header){
				if ( false === strpos($header, ':') )
				{
					return $result;
				}
				$key = explode(':', $header)[0];
				$value = trim(str_replace($key . ':', '', $header), " \t\"'");
				$result[str_replace('-', '', $key)] = $value;
				return $result;
			}, []);
		}

		$this->_headers['ResponseCode'] = curl_getinfo($curl, CURLINFO_HTTP_CODE);

		curl_close($curl);

		return $response;
	}

	/**
	 * Get response
	 *
	 * @param string $requestEntity
	 * @param array $params
	 * @param string $ETag ETag
	 * @return bool|array
	 */
	public function getResponse($requestEntity, array $params=[], $ETag=null)
	{
		$result = $this->getPureResponse($requestEntity, $params, self::RESPONSE_FORMAT_JSON, $ETag);

		$response = false!==$result ? json_decode($result, true) : false;

		if ( false !== $response )
		{
			switch ( $this->getHttpCode() )
			{
				case self::CODE_STATUS_OK:
					break;
				case self::CODE_STATUS_REQUEST_ERROR:
					$this->_error = 'Error (' . $this->getHttpCode() . '): request error';
					break;
				case self::CODE_STATUS_NOT_MODIFIED:
					$this->_error = 'Error (' . $this->getHttpCode() . '): not modified';
					break;
				case self::CODE_STATUS_NOT_AUTHORIZED:
					$this->_error = 'Error (' . $this->getHttpCode() . '): not authorized';
					break;
				case self::CODE_STATUS_NO_ACCESS:
					$this->_error = 'Error (' . $this->getHttpCode() . '): no access';
					break;
				case self::CODE_STATUS_NO_DATA_FOUND:
					$this->_error = 'Error (' . $this->getHttpCode() . '): data not found';
					$response = [];
					break;
				case self::CODE_STATUS_REQUEST_LIMIT_REACHED:
					$this->_error = 'Error (' . $this->getHttpCode() . '): request limit reached';
					break;
				case self::CODE_STATUS_INTERNAL_SERVER_ERROR:
					$this->_error = 'Error (' . $this->getHttpCode() . '): internal server error';
					break;
				case self::CODE_STATUS_METHOD_NOT_FOUND:
					$this->_error = 'Error (' . $this->getHttpCode() . '): method not found';
					break;
				case self::CODE_STATUS_SERVICE_NOT_AVAILABLE:
					$this->_error = 'Error (' . $this->getHttpCode() . '): service not available';
					break;
				default:
					$this->_error = 'Error (' . $this->getHttpCode() . ')';
					break;
			}
		}

		return $response;
	}

	/**
	 * Return the user agent string
	 * @return string
	 */
	protected function getUserAgent()
	{
		return 'Listex PHP API client ' . self::VERSION;
	}

	/**
	 * Return Url string
	 * @param string $requestEntity
	 * @return string
	 */
	protected function getUrl($requestEntity)
	{
		return self::API_URL . '/' . self::VERSION . '/' . $requestEntity;
	}

	/**
	 * Return list of brands
	 *
	 * @param string $ETag ETag
	 * @return bool|array
	 */
	public function getBrands($ETag=null)
	{
		return $this->getResponse(self::REQUEST_ENTITY_BRANDS, [], $ETag);
	}

	/**
	 * Return list of categories
	 *
	 * @param string $ETag ETag
	 * @return bool|array
	 */
	public function getCategories($ETag=null)
	{
		return $this->getResponse(self::REQUEST_ENTITY_CATEGORIES, [], $ETag);
	}

	/**
	 * Return list of products
	 *
	 * @param string $query
	 * @return bool|array
	 */
	public function getSuggestions($query)
	{
		$params = [
			'q' => $query
		];

		return $this->getResponse(self::REQUEST_ENTITY_SUGGESTIONS, $params);
	}

	/**
	 * Add reply to review
	 *
	 * @param int $review_parent_id parent review id
	 * @param string $review_text message
	 * @param string $social_type social network type (const)
	 * @param string $social_id social network id
	 * @param string $review_author author name
	 * @param float $review_rating rating
	 * @return bool|array
	 */
	public function addReplyToReview($review_parent_id, $review_text, $social_type, $social_id, $review_author, $review_rating)
	{
		$params = [
			'review_parent_id' => $review_parent_id,
			'review_text' => $review_text,
			'social_type' => $social_type,
			'social_id' => $social_id,
			'review_author' => $review_author,
			'review_rating' => $review_rating
		];

		return $this->getResponse(self::REQUEST_ENTITY_ADD_REVIEW, $params);
	}

	/**
	 * Add review to party
	 *
	 * @param int $party_id party id
	 * @param string $review_text message
	 * @param string $social_type social network type (const)
	 * @param string $social_id social network id
	 * @param string $review_author author name
	 * @param float $review_rating rating
	 * @return bool|array
	 */
	public function addReviewToParty($party_id, $review_text, $social_type, $social_id, $review_author, $review_rating)
	{
		$params = [
			'party_id' => $party_id,
			'review_text' => $review_text,
			'social_type' => $social_type,
			'social_id' => $social_id,
			'review_author' => $review_author,
			'review_rating' => $review_rating
		];

		return $this->getResponse(self::REQUEST_ENTITY_ADD_REVIEW, $params);
	}

	/**
	 * Add review to brand
	 *
	 * @param int $brand_id brand id
	 * @param string $review_text message
	 * @param string $social_type social network type (const)
	 * @param string $social_id social network id
	 * @param string $review_author author name
	 * @param float $review_rating rating
	 * @return bool|array
	 */
	public function addReviewToBrand($brand_id, $review_text, $social_type, $social_id, $review_author, $review_rating)
	{
		$params = [
			'brand_id' => $brand_id,
			'review_text' => $review_text,
			'social_type' => $social_type,
			'social_id' => $social_id,
			'review_author' => $review_author,
			'review_rating' => $review_rating
		];

		return $this->getResponse(self::REQUEST_ENTITY_ADD_REVIEW, $params);
	}

	/**
	 * Add review to good
	 *
	 * @param int $good_id good id
	 * @param string $review_text message
	 * @param string $social_type social network type (const)
	 * @param string $social_id social network id
	 * @param string $review_author author name
	 * @param float $review_rating rating
	 * @return bool|array
	 */
	public function addReviewToGood($good_id, $review_text, $social_type, $social_id, $review_author, $review_rating)
	{
		$params = [
			'good_id' => $good_id,
			'review_text' => $review_text,
			'social_type' => $social_type,
			'social_id' => $social_id,
			'review_author' => $review_author,
			'review_rating' => $review_rating
		];

		return $this->getResponse(self::REQUEST_ENTITY_ADD_REVIEW, $params);
	}

	/**
	 * Return list of attributes
	 *
	 * @param int $cat_id category id
	 * @param int $attr_type attribute type (const)
	 * @return bool|array
	 */
	public function getAttributes($cat_id=null, $attr_type=null)
	{
		$params = [
			'cat_id' => $cat_id,
			'attr_type' => $attr_type
		];

		return $this->getResponse(self::REQUEST_ENTITY_ATTRIBUTES, $params);
	}

	/**
	 * Return information about product by id
	 *
	 * @param int $good_id
	 * @param string $ETag ETag
	 * @return bool|array
	 */
	public function getProductById($good_id, $ETag=null)
	{
		$params = [
			'good_id' => $good_id
		];

		return $this->getResponse(self::REQUEST_ENTITY_PRODUCTS, $params, $ETag);
	}

	/**
	 * Return information about products by GTIN
	 *
	 * @param string $gtin
	 * @param string $ETag ETag
	 * @return bool|array
	 */
	public function getProductsByGtin($gtin, $ETag=null)
	{
		$params = [
			'gtin' => $gtin
		];

		return $this->getResponse(self::REQUEST_ENTITY_PRODUCTS, $params, $ETag);
	}

	/**
	 * Return information about products by LTIN
	 *
	 * @param string $ltin
	 * @param int $party_id
	 * @param string $ETag ETag
	 * @return bool|array
	 */
	public function getProductsByLtin($ltin, $party_id, $ETag=null)
	{
		$params = [
			'ltin' => $ltin,
			'party_id' => $party_id
		];

		return $this->getResponse(self::REQUEST_ENTITY_PRODUCTS, $params, $ETag);
	}

	/**
	 * Return information about products by SKU
	 *
	 * @param string $sku
	 * @param int $party_id
	 * @param string $ETag ETag
	 * @return bool|array
	 */
	public function getProductsBySku($sku, $party_id, $ETag=null)
	{
		$params = [
			'sku' => $sku,
			'party_id' => $party_id
		];

		return $this->getResponse(self::REQUEST_ENTITY_PRODUCTS, $params, $ETag);
	}

	/**
	 * Return array [ GoodId, ETag, Attributes ] for party
	 *
	 * @param int $party_id
	 * @return bool|array
	 */
	public function getETagsList($party_id)
	{
		$params = [
			'party_id' => $party_id
		];

		return $this->getResponse(self::REQUEST_ENTITY_ETAGS_LIST, $params);
	}
}