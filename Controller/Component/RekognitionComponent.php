<?php
use Aws\Rekognition;
use Aws\Credentials\Credentials;

class RekognitionComponent extends Component {
	public $settings = array(
		'accessKey' => '',
		'secretKey' => '',
		'sessionToken' => '',
		'region' => 'eu-west-2'
	);
	private $tag = 'Rekognition';

	public function initialize(Controller $controller, $settings = array()) {
		$this->controller = $controller;
		$this->settings = array_merge($this->settings, $settings);
	}

	public function verify($image_1, $image_2) {
		$credentials = new Aws\Credentials\Credentials($this->settings['accessKey'], $this->settings['secretKey'], $this->settings['sessionToken']);
		$client = new Rekognition\RekognitionClient(array(
			'configuration_mode' => 'standard',
			'credentials' => $credentials,
			'region' => $this->settings['region'],
			'version' => 'latest'
		));
		$result = $client->compareFaces(array(
			'QualityFilter' => 'AUTO',
			'SimilarityThreshold' => 90,
			'SourceImage' => array('Bytes' => $image_1),
			'TargetImage' => array('Bytes' => $image_2)
		));
		return $result;
	}
}