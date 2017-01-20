<?php 
return [
	 'secretKey'	=>  env("CK_SECRET_KEY", "my-secret-key"),
	 'accessKey'	=> env("CK_ACCESS_KEY", "my-access-key"),
	 'bucket'		=> env("CK_BUCKET", "my-bucket"),
	 'endpoint'		=> env("CK_ENDPOINT", "http://kilatstorage.com"),	
	 'custom_domain'		=> env("CK_CUSTOM_DOMAIN", env('CK_ENDPOINT')),
];