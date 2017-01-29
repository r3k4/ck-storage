<?php 

namespace Reka\S3\App;

class Files extends Api 
{

	public function getAllFiles($uri = '')
	{		
		$result = $this->listFiles($uri);
		return collect($result);
	}

	/**
	 * hapus object yg ada di uri
	 * @param  [string] $uri 
	 * @return [string]      
	 */
	public function deleteObject($uri, $bucket = false)
	{
        $this->request = [
            'method' => 'DELETE',
            'bucket' => ($bucket) ? $bucket : $this->bucket,
            'uri'    => $uri
        ];
		return $this->getResponse();
	}

 

    public function putObject($file, $uri, $requestHeaders = [])
    {

    	$this->put($file, $uri, $requestHeaders = []);
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