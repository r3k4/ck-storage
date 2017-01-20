<?php 

namespace Reka\S3;

use Illuminate\Support\Facades\Facade;

class CloudKilatFacade extends Facade{

	protected static function getFacadeAccessor() { return 'cloudKilat'; }
	
}