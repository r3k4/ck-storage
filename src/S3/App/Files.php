<?php 

namespace Reka\S3\App;

class Files extends Api 
{

	public function getAllFiles()
	{		
		$result = $this->connect()->listFiles();
		return collect($result);
	}

	/**
	 * hapus file yg ada di uri
	 * @param  [string] $uri 
	 * @return [string]      
	 */
	public function deleteFile($uri)
	{
		return S3::deleteObject($uri);
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