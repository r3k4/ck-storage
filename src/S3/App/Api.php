<?php 
namespace Reka\S3\App;

abstract class Api 
{

	protected $secretKey;

	protected $accessKey;

	protected $endpoint;

	protected $bucket;

	protected $custom_domain;

	public function __construct()
	{
		$this->endpoint = config('cloudkilatstorage.endpoint', 'http://kilatstorage.com');
		$this->accessKey = config('cloudkilatstorage.accessKey', '');
		$this->secretKey = config('cloudkilatstorage.secretKey', '');
		$this->bucket = config('cloudkilatstorage.bucket', '');
		$this->custom_domain = config('cloudkilatstorage.custom_domain', '');
	}

	public function connect()
	{
		S3::setAuth($this->accessKey, $this->secretKey);
		S3::setBucket($this->bucket);
		return new S3;
	}


}