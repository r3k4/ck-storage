<?php 

namespace Reka\S3\App;

class Files extends Api 
{

	public function getAllFiles($uri = '')
	{		
		$result = $this->connect()->listFiles($uri);
		return collect($result);
	}

	/**
	 * hapus object yg ada di uri
	 * @param  [string] $uri 
	 * @return [string]      
	 */
	public function deleteObject($uri)
	{
        $this->request = [
            'method' => 'DELETE',
            'bucket' => ($bucket) ? $bucket : $this->getBucket(),
            'uri'    => $uri
        ];
		return $this->connect()->getResponse();
	}


	/**
	 * menampilkan nama file dari full path yg ada di uri
	 * @param  [string] $uri 
	 * @return [string]     
	 */
	public function getName($uri)
	{
		return basename($uri);
	}


}