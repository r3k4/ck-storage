# cloudkilat storage API For Laravel 5

 
**Instalasi**

Download package dengan composer
```
composer require r3k4/ck-storage
```
atau
```
{
	"require": {
		"r3k4/ck-storage" : "dev-master"
	}
}
```

Tambahkan service provider ke config/app.php
```php
'providers' => [
	....
	
	Reka\S3\CloudKilatServiceProvider::class,
]
```

Tambahkan juga aliasnya ke config/app.php
```php
'aliases' => [
	....
	
	'KilatStorage' => Reka\S3\CloudKilatFacade::class,
]
```

Buat file cloudkilatstorage.php di folder config secara manual atau jalankan command artisan
```
php artisan vendor:publish
```
jika anda menggunakan command artisan diatas, anda akan dibuatkan file cloudkilatstorage.php di folder config

Tambahkan kode berikut di file .env untuk konfigurasi API cloudkilat (custom domain boleh dikosongkan)
```
CK_SECRET_KEY=
CK_ACCESS_KEY=
CK_BUCKET=
CK_ENDPOINT=
CK_CUSTOM_DOMAIN=

```
atau anda juga dapat langsung melakukan konfigurasi di file cloudkilatstorage.php di folder config seperti kode berikut.
```php
'end_point_api' => 'isi_base_url_api_akun_anda_disini',
'api_key' => 'isi_api_key_anda_disini',
'secretKey' => 'isi secretKey api akun anda di sini'
'accessKey' => 'isi accessKey api akun anda di sini'
'bucket' => 'isi bucket di sini'
'endpoint' => 'isi dgn http://kilatstorage.com'
'custom_domain' => 'boleh dikosongkan'

```

**Penggunaan**

Ambil data object yg ada pada bucket
```php
$data = KilatStorage::Files()->getAllFiles();
```
Delete object yg ada pada bucket
```php
$data = KilatStorage::Files()->deleteFile($uri);
```
Menampilkan nama file dari full path yg ada di uri
```php
$data = KilatStorage::Files()->getName($uri);
```