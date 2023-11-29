<?php
use Aws\Rekognition;
use Aws\Credentials\Credentials;
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
App::import('Vendor', 'CredentialsInterface', array('file' => 'Aws/src/Credentials/CredentialsInterface.php'));
App::import('Vendor', 'Credentials', array('file' => 'Aws/src/Credentials/Credentials.php'));
App::import('Vendor', 'RekognitionClient', array('file' => 'Aws/src/Rekognition/RekognitionClient.php'));

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