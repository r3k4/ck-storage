<?php 

namespace Reka\S3\App;

class Url extends Api 
{

	public function baseUrl()
	{
		$bucket = $this->bucket;
		$endpoint = $this->endpoint;
		$host = $this->custom_domain;
		if($this->custom_domain == ""){
			$host = $this->endpoint;
		}
		$host = preg_replace('#^https?://#', '', $host);
		if($this->custom_domain == '' || $this->custom_domain == $this->endpoint){
			$host = 'http://'.$bucket.'.'.$host;			
		}else{
			$host = 'http://'.$host;
		}
		return $host;
	}

	public function generate($uri = '')
	{
		return $this->baseUrl().'/'.$uri;
	}


}