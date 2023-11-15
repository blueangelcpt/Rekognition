<?php
use Aws\Rekognition;
App::import('Vendor', 'RekognitionClient', array('file' => 'Aws/src/Rekognition/RekognitionClient.php'));

class RekognitionComponent extends Component {
	public $settings = array('key' => '', 'secret' => '');
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
		$client = new Rekognition\RekognitionClient(array('credentials' => $this->settings));
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
		$client = new Rekognition\RekognitionClient(array('credentials' => $this->settings));
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