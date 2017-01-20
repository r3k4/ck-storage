<?php 
namespace Reka\S3\App;

abstract class Api 
{

    const ACL_PRIVATE = 'private';
    const ACL_PUBLIC_READ = 'public-read';
    const ACL_PUBLIC_READ_WRITE = 'public-read-write';
    const ACL_AUTHENTICATED_READ = 'authenticated-read';

    const STORAGE_CLASS_STANDARD = 'STANDARD';
    const STORAGE_CLASS_RRS = 'REDUCED_REDUNDANCY';
    const STORAGE_CLASS_STANDARD_IA = 'STANDARD_IA';


	protected $secretKey;

	protected $accessKey;

	protected $endpoint;

	protected $bucket;

	protected $custom_domain;

	protected $acl;

	protected $urlhttp;

	protected $urlhttps;

	protected $defaultHeaders = [];

    protected $request = [
        'method' => '',
        'bucket' => '',
        'uri'    => ''
    ];

    protected $storage;


	public function __construct()
	{
		$this->endpoint = config('cloudkilatstorage.endpoint', 'http://kilatstorage.com');
		$this->accessKey = config('cloudkilatstorage.accessKey', '');
		$this->secretKey = config('cloudkilatstorage.secretKey', '');
		$this->bucket = config('cloudkilatstorage.bucket', '');
		$this->custom_domain = config('cloudkilatstorage.custom_domain', '');

		$this->storage = self::STORAGE_CLASS_STANDARD;
		$this->acl = self::ACL_PUBLIC_READ;
	}

	public function connect()
	{
		S3::setAuth($this->accessKey, $this->secretKey);
		S3::setBucket($this->bucket);
		return new S3;
	}

    protected function hasAuth()
    {
        return ($this->accessKey !== null && $this->$secretKey !== null);
    }

    protected function getResponse($sourcefile = false, $headers = [])
    {

        if (!$this->hasAuth()) {
            return false;
        }

        $verb = $this->request['method'];
        $bucket = $this->request['bucket'];
        $uri = $this->request['uri'];
        $uri = $uri !== '' ? '/' . str_replace('%2F', '/', rawurlencode($uri)) : '/';

        $headers = array_merge([
            'Content-MD5'         => '',
            'Content-Type'        => '',
            'Date'                => gmdate('D, d M Y H:i:s T'),
            'Host'                => $this->endpoint,
            'x-amz-storage-class' => $this->storage,
            'x-amz-acl'           => $this->acl
        ], $headers, $this->$defaultHeaders);

        $resource = $uri;
        if ($bucket !== '') {
            if ($this->dnsBucketName($bucket)) {
                $headers['Host'] = $bucket . '.' . $this->endpoint;
                $resource = '/' . $bucket . $uri;
            } else {
                $uri = '/' . $bucket . $uri;
                $resource = $uri;
            }
        }

        $response = [];

        $url = 'https://' . $headers['Host'] . $uri;

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_USERAGENT, 'S3/php');
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_URL, $url);

        // PUT
        if ($verb == 'PUT') {

            if ($sourcefile) {

                if ($file = $this->inputFile($sourcefile)) {
                    curl_setopt($curl, CURLOPT_PUT, true);
                    $fp = @fopen($file['file'], 'rb');
                    curl_setopt($curl, CURLOPT_INFILE, $fp);
                    curl_setopt($curl, CURLOPT_INFILESIZE, $file['size']);
                    $headers['Content-Type'] = $file['type'];
                } else {
                    $input = array(
                        'data'   => $sourcefile,
                        'size'   => strlen($sourcefile),
                        'md5sum' => base64_encode(md5($sourcefile, true))
                    );

                    $headers['Content-MD5'] = $input['md5sum'];

                    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $verb);
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $input['data']);
                }


            } else {
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $verb);
            }
        } elseif ($verb == 'DELETE') {
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'DELETE');
        }

        $sendheaders = [];
        $amz = [];

        foreach ($headers as $header => $value) {
            if (strlen($value) > 0) {
                $sendheaders[] = $header . ': ' . $value;
                if (strpos($header, 'x-amz-') === 0) {
                    $amz[] = strtolower($header) . ':' . $value;
                }
            }
        }

        if (sizeof($amz) > 0) {
            usort($amz, array(__CLASS__, 'sortAmzHeaders'));
            $amz = "\n" . implode("\n", $amz);
        } else {
            $amz = '';
        }

        $sendheaders[] = 'Authorization: ' . $this->getSignature(
                $verb . "\n" .
                $headers['Content-MD5'] . "\n" .
                $headers['Content-Type'] . "\n" .
                $headers['Date'] . $amz . "\n" .
                $resource
            );

        curl_setopt($curl, CURLOPT_HTTPHEADER, $sendheaders);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);


        $data = curl_exec($curl);
        $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        $response['code'] = $code;
        $response['error'] = (in_array($code, [200, 204])) ? false : true;
        $response['message'] = $data;
        $response['url'] = [
            'default' => $url,
            'http'    => $this->$urlhttp . $uri,
            'https'   => $this->$urlhttps . $uri
        ];

        @curl_close($curl);

        if (isset($fp) && $fp !== false && is_resource($fp)) {
            fclose($fp);
        }

        return $response;
    }



    protected function dnsBucketName($bucket)
    {
        if (strlen($bucket) > 63 || preg_match("/[^a-z0-9\.-]/", $bucket) > 0) {
            return false;
        }
        if (strstr($bucket, '-.') !== false) {
            return false;
        }
        if (strstr($bucket, '..') !== false) {
            return false;
        }
        if (!preg_match("/^[0-9a-z]/", $bucket)) {
            return false;
        }
        if (!preg_match("/[0-9a-z]$/", $bucket)) {
            return false;
        }
        return true;
    }


    protected function inputFile($file)
    {
        if (!@file_exists($file) || !is_file($file) || !is_readable($file)) {
            return false;
        }

        return [
            'file'   => $file,
            'size'   => filesize($file),
            'type'   => $this->getMIMEType($file),
            'md5sum' => ''
        ];
    }


    protected function getMIMEType($file)
    {
        $exts = [
            'jpg'  => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'gif'  => 'image/gif',
            'png'  => 'image/png',
            'ico'  => 'image/x-icon',
            'pdf'  => 'application/pdf',
            'tif'  => 'image/tiff',
            'tiff' => 'image/tiff',
            'svg'  => 'image/svg+xml',
            'svgz' => 'image/svg+xml',
            'swf'  => 'application/x-shockwave-flash',
            'zip'  => 'application/zip',
            'gz'   => 'application/x-gzip',
            'tar'  => 'application/x-tar',
            'bz'   => 'application/x-bzip',
            'bz2'  => 'application/x-bzip2',
            'rar'  => 'application/x-rar-compressed',
            'exe'  => 'application/x-msdownload',
            'msi'  => 'application/x-msdownload',
            'cab'  => 'application/vnd.ms-cab-compressed',
            'txt'  => 'text/plain',
            'asc'  => 'text/plain',
            'htm'  => 'text/html',
            'html' => 'text/html',
            'css'  => 'text/css',
            'js'   => 'text/javascript',
            'xml'  => 'text/xml',
            'xsl'  => 'application/xsl+xml',
            'ogg'  => 'application/ogg',
            'mp3'  => 'audio/mpeg',
            'wav'  => 'audio/x-wav',
            'avi'  => 'video/x-msvideo',
            'mpg'  => 'video/mpeg',
            'mpeg' => 'video/mpeg',
            'mov'  => 'video/quicktime',
            'flv'  => 'video/x-flv',
            'php'  => 'text/x-php'
        ];

        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        if (isset($exts[$ext])) {
            return $exts[$ext];
        }

        if (extension_loaded('fileinfo')) {
            $finfo = new \finfo(FILEINFO_MIME);
            $type = $finfo->file($file);
            $re = "@/(.*?);@";

            preg_match($re, $type, $matches);

            if (!empty($matches)) {
                $type = $matches[1];
            }

            if (isset($exts[$type])) {
                return $exts[$type];
            }
        }

        return 'application/octet-stream';
    }


    protected function getSignature($string)
    {
        return 'AWS ' . $this->$accessKey . ':' . $this->getHash($string);
    }


    protected function getBucket()
    {
        return $this->bucket;
    }

}