<?php

namespace ListexApi;

final class ListexApi
{
	const API_URL = 'https://listex.info/api';
	const VERSION = 'v2';

	const RESPONSE_FORMAT_JSON = 'json';
	const RESPONSE_FORMAT_XML = 'xml';

	const REQUEST_ENTITY_ATTRIBUTES = 'attr';
	const REQUEST_ENTITY_BARCODE = 'gtin';
	const REQUEST_ENTITY_CATEGORIES = 'cat';
	const REQUEST_ENTITY_PARTIES = 'party';
	const REQUEST_ENTITY_PRODUCTS = 'goods';
	const REQUEST_ENTITY_TRADEMARKS = 'tm';

	const BARCODE_TYPE_ALL = 0; 	// all barcode
	const BARCODE_TYPE_GTIN = 1; 	// global barcode
	const BARCODE_TYPE_LTIN = 2;	// local barcode
	const BARCODE_TYPE_ARTICLE = 3;	// article

	const API_KEY = '0eg43ihs0khejal';

	protected $apiKey;
	protected $apiUrl;

	/** @var string */
	private $error;

	/**
	 * ListexApi constructor.
	 */
	public function __construct()
	{
		$this->apiUrl = self::API_URL . '/' . self::VERSION;
		$this->apiKey = self::API_KEY;
		$this->error = null;
	}

	/**
	 * @return bool
	 */
	public function hasError()
	{
		return null !== $this->error;
	}


	/**
	 * Return error
	 * @return null|string
	 */
	public function getError()
	{
		return $this->error; 
	}

	/**
	 * Send request and return pure response
	 *
	 * @param string $requestEntity
	 * @param array $params
	 * @param string $format
	 * @return bool|string Return the result on success, FALSE on failure
	 */
	public function getPureResponse($requestEntity, array $params=[], $format=self::RESPONSE_FORMAT_JSON)
	{
		$this->error = null;

		if ( !array_key_exists('format', $params) )
		{
			$params['format'] = $format;
		}
		$params['key'] = $this->apiKey;

		$curl = curl_init();

		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_URL, $this->getUrl($requestEntity));
		curl_setopt($curl, CURLOPT_USERAGENT, $this->getUserAgent());

		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $params);

		$response = curl_exec($curl);

		if ( false===$response )
		{
			$this->error = 'Error (' . curl_errno($curl) . '): ' . curl_error($curl);
		}

		curl_close($curl);

		return $response;
	}

	/**
	 * Get response
	 *
	 * @param string $requestEntity
	 * @param array $params
	 * @return bool|array
	 */
	public function getResponse($requestEntity, array $params=[])
	{
		$_response = $this->getPureResponse($requestEntity, $params, self::RESPONSE_FORMAT_JSON);

		$response = false!==$_response ? json_decode($_response) : false;

		if ( $response instanceof \stdClass )
		{
			try
			{
				if ($response->code > 0)
				{
					$this->error = 'Error (' . $response->code . '): ' . $response->message;
					$response = false;
				}
				else
				{
					$response = $response->object;
				}
			}
			catch (\Exception $e)
			{
				$this->error = 'Exception: Response does not contain the required fields!' . PHP_EOL
					. 'Response has fields: ' . implode(', ', get_class_methods($response));
				$response = false;
			}
		}
		else
		{
			$this->error = 'Error: Wrong response!' . PHP_EOL . $_response;
			$response = false;
		}

		return $response;
	}

	/**
	 * Return attribute
	 *
	 * @param int $id Attribute ID
	 * @return bool|object
	 */
	public function getAttribute($id)
	{
		$params = ['id' => $id];
		return $this->getResponse(self::REQUEST_ENTITY_ATTRIBUTES, $params);
	}

	/**
	 * Return list of attributes
	 *
	 * @param int $group Group ID
	 * @param int $good Good ID
	 * @param int $barcode Good GTIN
	 * @param int $cat Category ID
	 * @param int $party Party ID
	 * @param int $page
	 * @param int $limit
	 * @return bool|array of stdClass
	 */
	public function getAttributesList($group=null, $good=null, $barcode=null, $cat=null, $party=null, $page=0, $limit=50)
	{
		$params = [
			'group' => $group,
			'goodid' => $good,
			'barcode' => $barcode,
			'catid' => $cat,
			'partyid' => $party,
			'offset' => $page * $limit,
			'limit' => $limit,
		];

		return $this->getResponse(self::REQUEST_ENTITY_ATTRIBUTES, $params);
	}

	/**
	 * Return info of Barcode
	 *
	 * @param string $barcode Barcode
	 * @return bool|object
	 */
	public function getBarcode($barcode=null)
	{
		$params = ['gtin' => $barcode];
		return $this->getResponse(self::REQUEST_ENTITY_BARCODE, $params);
	}

	/**
	 * Return list of Barcode
	 *
	 * @param int $good Good ID
	 * @param int $type Barcode type
	 * @param int $party Party ID
	 * @param int $page
	 * @param int $limit
	 * @return bool|array of stdClass
	 */
	public function getBarcodesList($good=null, $type=null, $party=null, $page=0, $limit=50)
	{
		$params = [
			'goodid' => $good,
			'type' => $type,
			'partyid' => $party,
			'offset' => $page * $limit,
			'limit' => $limit,
		];

		return $this->getResponse(self::REQUEST_ENTITY_BARCODE, $params);
	}

	/**
	 * Return info of Category
	 *
	 * @param int $id Category ID
	 * @return bool|object
	 */
	public function getCategory($id)
	{
		$params = ['id' => $id];
		return $this->getResponse(self::REQUEST_ENTITY_CATEGORIES, $params);
	}

	/**
	 * Return list of Categories
	 *
	 * @param int $party Party ID
	 * @param int $page
	 * @param int $limit
	 * @return bool|array of stdClass
	 */
	public function getCategoriesList($party=null, $page=0, $limit=50)
	{
		$params = [
			'partyid' => $party,
			'offset' => $page * $limit,
			'limit' => $limit,
		];

		return $this->getResponse(self::REQUEST_ENTITY_CATEGORIES, $params);
	}

	/**
	 * Return info of Party
	 *
	 * @param int $id Party ID
	 * @return bool|object
	 */
	public function getParty($id)
	{
		$params = ['id' => $id];
		return $this->getResponse(self::REQUEST_ENTITY_PARTIES, $params);
	}

	/**
	 * Return list of Parties
	 *
	 * @param int $page
	 * @param int $limit
	 * @return bool|array of stdClass
	 */
	public function getPartiesList($page=0, $limit=50)
	{
		$params = [
			'offset' => $page * $limit,
			'limit' => $limit,
		];

		return $this->getResponse(self::REQUEST_ENTITY_PARTIES, $params);
	}

	/**
	 * Return info of Product
	 *
	 * @param int $id Product ID
	 * @param string $barcode Barcode
	 * @return bool|object
	 */
	public function getProduct($id=null, $barcode=null)
	{
		$params = ['id' => $id, 'gtin' => $barcode];
		return $this->getResponse(self::REQUEST_ENTITY_PRODUCTS, $params);
	}

	/**
	 * Return list of Products
	 *
	 * @param int $party Party ID
	 * @param int $barcodeType Barcode type
	 * @param int $page
	 * @param int $limit
	 * @return bool|array of stdClass
	 */
	public function getProductsList($party=null, $barcodeType=null, $page=0, $limit=50)
	{
		$params = [
			'partyid' => $party,
			'type' => $barcodeType,
			'offset' => $page * $limit,
			'limit' => $limit,
		];

		return $this->getResponse(self::REQUEST_ENTITY_PRODUCTS, $params);
	}

	/**
	 * Return list of Trademarks
	 *
	 * @param int $page
	 * @param int $limit
	 * @return bool|array of stdClass
	 */
	public function getTrademarksList($page=0, $limit=50)
	{
		$params = [
			'offset' => $page * $limit,
			'limit' => $limit,
		];

		return $this->getResponse(self::REQUEST_ENTITY_TRADEMARKS, $params);
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
}