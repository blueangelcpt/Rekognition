<?php
use Aws\Sts\StsClient;
use Aws\Rekognition;
use Aws\Credentials\Credentials;

class RekognitionComponent extends Component {
	public $settings = array(
		'accessKey' => '',
		'secretKey' => '',
		'region' => 'eu-west-2'
	);

	public function initialize(Controller $controller, $settings = array()) {
		$this->controller = $controller;
		$this->settings = array_merge($this->settings, $settings);
	}

	public function verify($image_1, $image_2) {
		$temporaryCredentials = $this->_generateTemporaryCredentials($this->settings['accessKey'], $this->settings['secretKey']);
		pr($temporaryCredentials);
		die();
		$credentials = new Aws\Credentials\Credentials($temporaryCredentials['accessKey'], $temporaryCredentials['secretKey'], $temporaryCredentials['sessionToken']);
		$client = new Rekognition\RekognitionClient(array(
			'configuration_mode' => 'standard',
			'credentials' => $credentials,
			'region' => $this->settings['region'],
			'version' => 'latest'
		));
		$result = $client->compareFaces(array(
			'QualityFilter' => 'AUTO',
			'SimilarityThreshold' => 0,
			'SourceImage' => array('Bytes' => $image_1),
			'TargetImage' => array('Bytes' => $image_2)
		));
		return $result;
	}

	private function _generateTemporaryCredentials($accessKey, $secretKey, $region = 'us-east-1', $durationSeconds = 3600) {
		$stsClient = new StsClient(array(
			'version' => 'latest',
			'region' => $region,
			'credentials' => array(
				'key' => $accessKey,
				'secret' => $secretKey
			)
		));
		$result = $stsClient->getSessionToken(array('DurationSeconds' => $durationSeconds));
		$credentials = $result['Credentials'];
		return array(
			'accessKey' => $credentials['AccessKeyId'],
			'secretKey' => $credentials['SecretAccessKey'],
			'sessionToken' => $credentials['SessionToken'],
			'expiration' => $credentials['Expiration']
		);
	}
}