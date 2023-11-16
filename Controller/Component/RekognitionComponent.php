<?php
use Aws\Rekognition;
use Aws\AwsClient;
use Aws\AwsClientInterface;
use Aws\AwsClientTrait;
use Aws\Configuration\ConfigurationResolver;
use Aws\Signature\SignatureProvider;
use Aws\Api\ApiProvider;
use Aws\Api\Service;
use Aws\Api\ShapeMap;
use Aws\Api\ErrorParser\JsonRpcErrorParser;
use Aws\Api\ErrorParser\AbstractErrorParser;
use Aws\Api\Parser\JsonRpcParser;
use Aws\Api\Parser\AbstractParser;
use Aws\Api\Parser\PayloadParserTrait;
use Aws\Api\Parser\JsonParser;
use Aws\Api\Parser\MetadataParserTrait;
use Aws\DefaultsMode\ConfigurationProvider;
use Aws\ConfigurationProviderInterface;
use Aws\AbstractConfigurationProvider;
use Aws\DefaultsMode\Configuration;
use Aws\DefaultsMode\ConfigurationInterface;

App::import('Vendor', 'ConfigurationInterface', array('file' => 'Aws/src/DefaultsMode/ConfigurationInterface.php'));
App::import('Vendor', 'Configuration', array('file' => 'Aws/src/DefaultsMode/Configuration.php'));
App::import('Vendor', 'AbstractConfigurationProvider', array('file' => 'Aws/src/AbstractConfigurationProvider.php'));
App::import('Vendor', 'ConfigurationProviderInterface', array('file' => 'Aws/src/ConfigurationProviderInterface.php'));
App::import('Vendor', 'UseFipsEndpointConfigurationProvider', array('file' => 'Aws/src/Endpoint/UseFipsEndpoint/ConfigurationProvider.php'));
App::import('Vendor', 'DefaultsModeConfigurationProvider', array('file' => 'Aws/src/DefaultsMode/ConfigurationProvider.php'));
App::import('Vendor', 'JsonParser', array('file' => 'Aws/src/Api/Parser/JsonParser.php'));
App::import('Vendor', 'PayloadParserTrait', array('file' => 'Aws/src/Api/Parser/PayloadParserTrait.php'));
App::import('Vendor', 'JsonParserTrait', array('file' => 'Aws/src/Api/ErrorParser/JsonParserTrait.php'));
App::import('Vendor', 'MetadataParserTrait', array('file' => 'Aws/src/Api/Parser/MetadataParserTrait.php'));
App::import('Vendor', 'AbstractErrorParser', array('file' => 'Aws/src/Api/ErrorParser/AbstractErrorParser.php'));
App::import('Vendor', 'JsonRpcErrorParser', array('file' => 'Aws/src/Api/ErrorParser/JsonRpcErrorParser.php'));
App::import('Vendor', 'AbstractParser', array('file' => 'Aws/src/Api/Parser/AbstractParser.php'));
App::import('Vendor', 'JsonRpcParser', array('file' => 'Aws/src/Api/Parser/JsonRpcParser.php'));
App::import('Vendor', 'ShapeMap', array('file' => 'Aws/src/Api/ShapeMap.php'));
App::import('Vendor', 'AbstractModel', array('file' => 'Aws/src/Api/AbstractModel.php'));
App::import('Vendor', 'Service', array('file' => 'Aws/src/Api/Service.php'));
App::import('Vendor', 'ApiProvider', array('file' => 'Aws/src/Api/ApiProvider.php'));
App::import('Vendor', 'SignatureProvider', array('file' => 'Aws/src/Signature/SignatureProvider.php'));
App::import('Vendor', 'ConfigurationResolver', array('file' => 'Aws/src/Configuration/ConfigurationResolver.php'));
App::import('Vendor', 'ClientResolver', array('file' => 'Aws/src/ClientResolver.php'));
App::import('Vendor', 'HandlerList', array('file' => 'Aws/src/HandlerList.php'));
App::import('Vendor', 'Functions', array('file' => 'Aws/src/functions.php'));
App::import('Vendor', 'AwsClientTrait', array('file' => 'Aws/src/AwsClientTrait.php'));
App::import('Vendor', 'AwsClientInterface', array('file' => 'Aws/src/AwsClientInterface.php'));
App::import('Vendor', 'AwsClient', array('file' => 'Aws/src/AwsClient.php'));
App::import('Vendor', 'RekognitionClient', array('file' => 'Aws/src/Rekognition/RekognitionClient.php'));

class RekognitionComponent extends Component {
	public $settings = array(
		'key' => '',
		'secret' => '',
		'region' => 'us-west-2'
	);
	private $tag = 'Rekognition';

	public function initialize(Controller $controller, $settings = array()) {
		$this->controller = $controller;
		$this->settings = array_merge($this->settings, $settings);
	}

	public function detect($image_url) {
		if (filter_var($image_url, FILTER_VALIDATE_URL)) {
			$payload = array(
				'Attributes' => 'DEFAULT',
				'Image' => array(
					'S3Object' => array(
						'Bucket' => 'string',
						'Name' =>  'string',
						'Version' => 'string'
					)
				)
			);
		} else {
			$payload = array(
				'Attributes' => 'DEFAULT',
				'Image' => array('Bytes' => $image_url)
			);
		}
		$client = new Rekognition\RekognitionClient(array(
			'configuration_mode' => 'standard',
			'credentials' => $this->settings,
			'region' => $this->settings['region'],
			'version' => '2016-06-27'
		));
		$response = $client->detectFaces($payload);
		$this->log('Rekognition detect API response: ' . $response, $this->tag);
		$result = json_decode($response->body, true);
		/* [
			'FaceDetails' => [
				[
					'AgeRange' => [
						'High' => <integer>,
						'Low' => <integer>,
					],
					'Beard' => [
						'Confidence' => <float>,
						'Value' => true || false,
					],
					'BoundingBox' => [
						'Height' => <float>,
						'Left' => <float>,
						'Top' => <float>,
						'Width' => <float>,
					],
					'Confidence' => <float>,
					'Emotions' => [
						[
							'Confidence' => <float>,
							'Type' => 'HAPPY|SAD|ANGRY|CONFUSED|DISGUSTED|SURPRISED|CALM|UNKNOWN|FEAR',
						],
						// ...
					],
					'EyeDirection' => [
						'Confidence' => <float>,
						'Pitch' => <float>,
						'Yaw' => <float>,
					],
					'Eyeglasses' => [
						'Confidence' => <float>,
						'Value' => true || false,
					],
					'EyesOpen' => [
						'Confidence' => <float>,
						'Value' => true || false,
					],
					'FaceOccluded' => [
						'Confidence' => <float>,
						'Value' => true || false,
					],
					'Gender' => [
						'Confidence' => <float>,
						'Value' => 'Male|Female',
					],
					'Landmarks' => [
						[
							'Type' => 'eyeLeft|eyeRight|nose|mouthLeft|mouthRight|leftEyeBrowLeft|leftEyeBrowRight|leftEyeBrowUp|rightEyeBrowLeft|rightEyeBrowRight|rightEyeBrowUp|leftEyeLeft|leftEyeRight|leftEyeUp|leftEyeDown|rightEyeLeft|rightEyeRight|rightEyeUp|rightEyeDown|noseLeft|noseRight|mouthUp|mouthDown|leftPupil|rightPupil|upperJawlineLeft|midJawlineLeft|chinBottom|midJawlineRight|upperJawlineRight',
							'X' => <float>,
							'Y' => <float>,
						],
						// ...
					],
					'MouthOpen' => [
						'Confidence' => <float>,
						'Value' => true || false,
					],
					'Mustache' => [
						'Confidence' => <float>,
						'Value' => true || false,
					],
					'Pose' => [
						'Pitch' => <float>,
						'Roll' => <float>,
						'Yaw' => <float>,
					],
					'Quality' => [
						'Brightness' => <float>,
						'Sharpness' => <float>,
					],
					'Smile' => [
						'Confidence' => <float>,
						'Value' => true || false,
					],
					'Sunglasses' => [
						'Confidence' => <float>,
						'Value' => true || false,
					],
				],
				// ...
			],
			'OrientationCorrection' => 'ROTATE_0|ROTATE_90|ROTATE_180|ROTATE_270',
		]
		*/
		return $result[0]['faceId'];
	}

	public function verify($image_1, $image_2) {
		$payload = array(
			'QualityFilter' => 'AUTO',
			'SimilarityThreshold' => 90
		);
		if (filter_var($image_1, FILTER_VALIDATE_URL)) {
			$payload['SourceImage'] = array(
				'S3Object' => array(
					'Bucket' => 'string',
					'Name' => 'string',
					'Version' => 'string'
				)
			);
		} else {
			$payload['SourceImage'] = array('Bytes' => $image_1);
		}
		if (filter_var($image_2, FILTER_VALIDATE_URL)) {
			$payload['TargetImage'] = array(
				'S3Object' => array(
					'Bucket' => 'string',
					'Name' => 'string',
					'Version' => 'string'
				)
			);
		} else {
			$payload['TargetImage'] = array('Bytes' => $image_2);
		}
		$header = array('Content-Type' => $contentType);
		$client = new Rekognition\RekognitionClient(array(
			'configuration_mode' => 'standard',
			'credentials' => $this->settings,
			'region' => $this->settings['region'],
			'version' => '2016-06-27'
		));
		$result = $client->compareFaces($payload);
		/*
		[
			'FaceMatches' => [
				[
					'Face' => [
						'BoundingBox' => [
							'Height' => <float>,
							'Left' => <float>,
							'Top' => <float>,
							'Width' => <float>,
						],
						'Confidence' => <float>,
						'Emotions' => [
							[
								'Confidence' => <float>,
								'Type' => 'HAPPY|SAD|ANGRY|CONFUSED|DISGUSTED|SURPRISED|CALM|UNKNOWN|FEAR',
							],
							// ...
						],
						'Landmarks' => [
							[
								'Type' => 'eyeLeft|eyeRight|nose|mouthLeft|mouthRight|leftEyeBrowLeft|leftEyeBrowRight|leftEyeBrowUp|rightEyeBrowLeft|rightEyeBrowRight|rightEyeBrowUp|leftEyeLeft|leftEyeRight|leftEyeUp|leftEyeDown|rightEyeLeft|rightEyeRight|rightEyeUp|rightEyeDown|noseLeft|noseRight|mouthUp|mouthDown|leftPupil|rightPupil|upperJawlineLeft|midJawlineLeft|chinBottom|midJawlineRight|upperJawlineRight',
								'X' => <float>,
								'Y' => <float>,
							],
							// ...
						],
						'Pose' => [
							'Pitch' => <float>,
							'Roll' => <float>,
							'Yaw' => <float>,
						],
						'Quality' => [
							'Brightness' => <float>,
							'Sharpness' => <float>,
						],
						'Smile' => [
							'Confidence' => <float>,
							'Value' => true || false,
						],
					],
					'Similarity' => <float>,
				],
				// ...
			],
			'SourceImageFace' => [
				'BoundingBox' => [
					'Height' => <float>,
					'Left' => <float>,
					'Top' => <float>,
					'Width' => <float>,
				],
				'Confidence' => <float>,
			],
			'SourceImageOrientationCorrection' => 'ROTATE_0|ROTATE_90|ROTATE_180|ROTATE_270',
			'TargetImageOrientationCorrection' => 'ROTATE_0|ROTATE_90|ROTATE_180|ROTATE_270',
			'UnmatchedFaces' => [
				[
					'BoundingBox' => [
						'Height' => <float>,
						'Left' => <float>,
						'Top' => <float>,
						'Width' => <float>,
					],
					'Confidence' => <float>,
					'Emotions' => [
						[
							'Confidence' => <float>,
							'Type' => 'HAPPY|SAD|ANGRY|CONFUSED|DISGUSTED|SURPRISED|CALM|UNKNOWN|FEAR',
						],
						// ...
					],
					'Landmarks' => [
						[
							'Type' => 'eyeLeft|eyeRight|nose|mouthLeft|mouthRight|leftEyeBrowLeft|leftEyeBrowRight|leftEyeBrowUp|rightEyeBrowLeft|rightEyeBrowRight|rightEyeBrowUp|leftEyeLeft|leftEyeRight|leftEyeUp|leftEyeDown|rightEyeLeft|rightEyeRight|rightEyeUp|rightEyeDown|noseLeft|noseRight|mouthUp|mouthDown|leftPupil|rightPupil|upperJawlineLeft|midJawlineLeft|chinBottom|midJawlineRight|upperJawlineRight',
							'X' => <float>,
							'Y' => <float>,
						],
						// ...
					],
					'Pose' => [
						'Pitch' => <float>,
						'Roll' => <float>,
						'Yaw' => <float>,
					],
					'Quality' => [
						'Brightness' => <float>,
						'Sharpness' => <float>,
					],
					'Smile' => [
						'Confidence' => <float>,
						'Value' => true || false,
					],
				],
				// ...
			],
		]
		*/
		$this->log('Rekognition verify API response: ' . $result, $this->tag);
		return json_decode($result->body, true);
	}
}