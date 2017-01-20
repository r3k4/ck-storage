<?php 
namespace Reka\S3;

use Reka\S3\App\Files;
use Reka\S3\App\Url;

class CloudKilatStorage 
{

	public function Files(){
		return new Files;
	}

	public function Url(){
		return new Url;
	}

}