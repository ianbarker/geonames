<?php
/**
 * GeoNames API wrapper
 * @author _ianbarker
 */
class GeoNames {

	protected static $username;
	protected static $baseUrl = 'http://api.geonames.org/';
	protected static $defaultStyle = 'FULL';

	public static function setUsername($username) {
		self::$username = $username;
	}

	public static function setBaseUrl($url) {
		self::$baseUrl = $url;
	}

	public static function setDefaultStyle($style) {
		self::$defaultStyle = $style;
	}

	public function __construct() {
		if (empty(self::$username))
			throw new Exception('Username must be set before creating an instance of GeoNames. Set using GeoNames::setUsername');

	}

	private function getUrl($service, $parameters) {

		$url = self::$baseUrl . $service . 'JSON';
		$defaults = array(
			'username' => self::$username,
			'style' => self::$defaultStyle
		);

		$parameters = array_merge($defaults, $parameters);

		$url .= '?' . http_build_query($parameters, null, '&');

		return $url;


	}

	private function getData($url) {

		$ch = curl_init();
		curl_setopt_array($ch, array(
			CURLOPT_URL => $url,
			CURLOPT_FAILONERROR => true,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_FRESH_CONNECT => true,
			CURLOPT_HEADER => false,
			CURLOPT_RETURNTRANSFER => true
		));

		$result = curl_exec($ch);
		if ($result === false) {
			throw new Exception('Failed to load data from url ' . $url . '. ' . curl_error($ch));
		}

		return json_decode($result);

	}

	public function postalCodeSearch($postcode, $limit = 1, $country = 'GB') {

		$return = array();
		$params = array(
			'postalcode' => $postcode,
			'maxRows' => $limit,
			'country' => $country,
			'isReduced' => false
		);
		$url = $this->getUrl('postalCodeSearch', $params);

		try {
			$results = $this->getData($url);
		} catch (Exception $e) {
			throw $e;
		}

		if (empty($results->postalCodes)) {
			throw new Exception('Failed to geocode postcode ' . $postcode);
		} else {

			foreach ($results->postalCodes as $result) {
				$return[] = array(
					'lat' => $result->lat,
					'lng' => $result->lng,
					'name' => $result->placeName,
					'code' => $result->postalCode
				);
			}

		}

		return $return;


	}

	public function findNearbyPlaceName($lat, $lng, $radius = 10, $limit = 5) {

		$return = array();
		$params = array(
			'lat' => $lat,
			'lng' => $lng,
			'radius' => $radius,
			'maxRows' => $limit
		);

		$url = $this->getUrl('findNearbyPlaceName', $params);

		try {
			$results = $this->getData($url);
		} catch (Exception $e) {
			throw $e;
		}

		if (empty($results->geonames)) {
			throw new Exception('Failed to find any nearby placeNames');
		} else {

			foreach ($results->geonames as $result) {
				$return[] = array(
					'lat' => $result->lat,
					'lng' => $result->lng,
					'name' => $result->name,
					'distance' => $result->distance,
					'type' => $result->fcodeName
				);
			}

		}

		return $return;

	}

	public function findNearby($lat, $lng, $radius = 10, $limit = 25) {

		$return = array();
		$params = array(
			'lat' => $lat,
			'lng' => $lng,
			'radius' => $radius,
			'maxRows' => $limit
		);

		$url = $this->getUrl('findNearby', $params);

		try {
			$results = $this->getData($url);
		} catch (Exception $e) {
			throw $e;
		}

		if (empty($results->geonames)) {
			throw new Exception('Failed to find any nearby stuff');
		} else {

			foreach ($results->geonames as $result) {
				$return[] = array(
					'lat' => $result->lat,
					'lng' => $result->lng,
					'name' => $result->name,
					'distance' => $result->distance,
					'type' => $result->fcodeName
				);
			}

		}

		return $return;

	}

	public function findNearbyWikipedia($lat, $lng, $radius = 10, $limit = 10) {

		$return = array();
		$params = array(
			'lat' => $lat,
			'lng' => $lng,
			'radius' => $radius,
			'maxRows' => $limit
		);

		$url = $this->getUrl('findNearbyWikipedia', $params);

		try {
			$results = $this->getData($url);
		}

		catch (Exception $e) {
			throw $e;
		}

		if (empty($results->geonames)) {
			throw new Exception('Failed to find any nearby stuff');
		} else {

			foreach ($results->geonames as $result) {
				$return[] = array(
					'title' => $result->title,
					'summary' => $result->summary,
					'distance' => $result->distance,
					'lat' => $result->lat,
					'lng' => $result->lng,
					'type' => $result->feature,
					'rank' => $result->rank,
					'url' => $result->wikipediaUrl,
					'thumb' => $result->thumbnailImg

				);
			}

		}

		return $return;

	}

	public function findNearByWeather($lat, $lng) {

		$params = array(
			'lat' => $lat,
			'lng' => $lng,
			'maxRows' => 1
		);

		$url = $this->getUrl('findNearByWeather', $params);

		try {
			$results = $this->getData($url);
		}

		catch (Exception $e) {
			throw $e;
		}


		if (empty($results->weatherObservation)) {

			throw new Exception('Failed to find any nearby weather');

		} else {

			return array(
				'name' => $results->weatherObservation->stationName,
				'clouds' => $results->weatherObservation->clouds,
				'conditions' => $results->weatherObservation->weatherCondition,
				'temperature' => $results->weatherObservation->temperature,
				'wind_speed' => $results->weatherObservation->windSpeed,
				'humidity' => $results->weatherObservation->humidity,
				'lat' => $results->weatherObservation->lat,
				'lng' => $results->weatherObservation->lng,
				'date' => strtotime($results->weatherObservation->datetime)
			);
		}

	}


}
