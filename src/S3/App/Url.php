<?php 

namespace Reka\S3\App;

class Url extends Api 
{

	public function baseUrl()
	{
		$bucket = $this->connect()->getBucket();
		$endpoint = $this->endpoint;
		$host = $this->custom_domain;
		$host = parse_url($host)['host'];
		if($this->custom_domain == '' || $this->custom_domain == $this->endpoint){
			$host = 'http://'.$bucket.'.'.$host;			
		}else{
			$host = 'http://'.$host;
		}
		return $host;
	}


}