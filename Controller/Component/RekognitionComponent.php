<?php
App::uses('HttpSocket', 'Network/Http');
class RekognitionComponent extends Component {
	public $settings = array(
		'ApiKey' => '',
		'url' => 'https://rekognition.us-east-2.amazonaws.com/face/v1.0'
	);
	private $socket = '';
	private $tag = 'Rekognition';

	public function initialize(Controller $controller, $settings = array()) {
		$this->controller = $controller;
		$this->settings = array_merge($this->settings, $settings);
		$this->socket = new HttpSocket(array(
			'ssl_verify_peer' => false,
			'ssl_verify_peer_name' => false,
			'ssl_allow_self_signed' => true,
			'ssl_verify_depth' => 0,
			'timeout' => 60
		));
	}

	public function detect($image_url) {
		if (filter_var($image_url, FILTER_VALIDATE_URL)) {
			$contentType = 'application/json';
			$header = array(
				'Ocp-Apim-Subscription-Key' => $this->settings['ApiKey'],
				'Content-Type' => $contentType
			);
			$payload = json_encode(array(
				'url' => $image_url
			));
			$endpoint = $this->settings['url'] . '/detect?recognitionModel=recognition_04&returnRekognitionId=true&detectionModel=detection_03';
		} else {
			$contentType = 'application/octet-stream';
			$header = array(
				'Ocp-Apim-Subscription-Key' => $this->settings['ApiKey'],
				'Content-Type' => $contentType,
				'Content-Length' => strlen($image_url)
			);
			$payload = $image_url;
			$endpoint = $this->settings['url'] . '/detect?overload=stream&recognitionModel=recognition_04&returnRekognitionId=true&detectionModel=detection_03';
		}
		$response = $this->socket->post(
			$endpoint,
			$payload,
			compact('header')
		);
		$this->log('Rekognition detect API request: ' . $this->socket->request['raw'], $this->tag);
		$this->log('Rekognition detect API response: ' . $response, $this->tag);
		$result = json_decode($response->body, true);
		return $result[0]['faceId'];
	}

	public function verify($face_id_1, $face_id_2) {
		$payload = json_encode(array(
			'faceId1' => $face_id_1,
			'faceId2' => $face_id_2
		));
		$result = $this->socket->post(
			$this->settings['url'] . '/verify',
			$payload,
			array('header' => array(
				'Ocp-Apim-Subscription-Key' => $this->settings['ApiKey'],
				'Content-Type' => 'application/json',
			))
		);
		$this->log('Rekognition verify API request: ' . $this->socket->request['raw'], $this->tag);
		$this->log('Rekognition verify API response: ' . $result, $this->tag);
		return json_decode($result->body, true);
	}
}