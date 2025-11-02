# Laravel Temporary URLs for MinIO

[![Latest Version on Packagist](https://img.shields.io/packagist/v/hosni/laravel-temporary-urls-minio.svg?style=flat-square)](https://packagist.org/packages/hosni/laravel-temporary-urls-minio)
[![Total Downloads](https://img.shields.io/packagist/dt/hosni/laravel-temporary-urls-minio.svg?style=flat-square)](https://packagist.org/packages/hosni/laravel-temporary-urls-minio)
[![License](https://img.shields.io/packagist/l/hosni/laravel-temporary-urls-minio.svg?style=flat-square)](LICENSE)
[![Laravel](https://img.shields.io/badge/Laravel-9|10%20|11|12-green.svg)](https://laravel.com)

---

## 🚀 Introduction

By default, **Laravel’s `temporaryUrl()` method** works perfectly with S3-compatible storage.  
It generates a temporary URL to an object in your S3 bucket, easy peasy!

But when you switch to **[MinIO](https://min.io/)**, **[Garage](https://garagehq.deuxfleurs.fr/)**, **[SeaweedFS](https://github.com/seaweedfs/seaweedfs)**, **[RustFS](https://github.com/rustfs/rustfs)** .. things can get a little wild. Temporary URLs might ***not work*** if the generated endpoint isn’t directly accessible by your client (browser, mobile app, etc).

You may have run into this when using `temporaryUrl()` directly, or when a package like [spatie/laravel-medialibrary](https://github.com/spatie/laravel-medialibrary) calls `$media->getTemporaryUrl()` behind the scenes.

From the Laravel documentation:

> [!WARNING]
> Generating temporary storage URLs via the `temporaryUrl` method may not work when using S3 if the `endpoint` is not accessible by the client.
> ,  [Laravel Filesystem Docs](https://laravel.com/docs/12.x/filesystem#minio)

**But here’s the BIG question:**
### How do you make S3’s `temporaryUrl()` work in Laravel?

This package is your **plug-and-play hero!** Just install it, and let the magic happen. 🎩✨

---

## 📦 Installation

Install the package via [Composer](https://getcomposer.org/):

```bash
composer require hosni/laravel-temporary-urls-minio
````

Laravel will auto-discover the service provider using [Package Discovery](https://laravel.com/docs/12.x/packages#package-discovery). No extra steps, just sit back and relax!

---

## ⚙️ Configuration

In your `.env` file, set your MinIO configuration like a boss:

```ini
AWS_ACCESS_KEY_ID=minioadmin
AWS_SECRET_ACCESS_KEY=minioadmin
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=my-bucket

# Internal MinIO endpoint (used for signing)
AWS_ENDPOINT=http://minio:9000

# Publicly accessible endpoint (used in signed URLs)
AWS_URL=https://storage.example.com
```

Next, in `config/filesystems.php`:

```php
'disks' => [
    's3' => [
        'driver' => 's3',
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION'),
        'bucket' => env('AWS_BUCKET'),
        'url' => env('AWS_URL'),
        'endpoint' => env('AWS_ENDPOINT'),
        'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false),
        'throw' => false,
        'report' => false,

        /**
         * Add the environment key (e.g. MINIO_TEMPORARY_URL) to the s3 disk configuration.
         * This is where the magic happens! 🪄
         */
        'temporary_url' => env('AWS_URL'), // 👈 used for rewriting signed URLs
    ],
],
```

---

## 🛠 Usage

Just use Laravel’s filesystem like you always do:

```php
use Illuminate\Support\Facades\Storage;

$url = Storage::disk('minio')->temporaryUrl(
    'uploads/myfile.jpg',
    now()->addMinutes(5)
);

return $url;
```

👉 The returned `$url` will always be valid and accessible from your client. You can thank me later! 😎

You can also use this with [spatie/laravel-medialibrary](https://github.com/spatie/laravel-medialibrary):

```php
$mediaItem = $yourModel->getFirstMedia('thumbnail');
$temporaryS3Url = $mediaItem->getTemporaryUrl(now()->addMinutes(10));
```

No need to install packages like [coreproc/laravel-minio-media-library-provider](https://github.com/CoreProc/laravel-minio-media-library-provider), which take a different (and less effective) approach! Their solution simply returns the S3 endpoint URL, which is only accessible inside your Docker network, not so helpful if you want to share files with the outside world. 😅

With this package, your temporary URLs are always accessible, inside or outside Docker. No hacks, no headaches, just happy URLs!

---

## 🔍 How It Works

1. Laravel signs the request using your internal S3 endpoint (`AWS_ENDPOINT`).
2. This package swoops in and intercepts the signed URL.
3. It **rewrites the host** from the internal endpoint to the public URL you configured (`AWS_URL`, `MINIO_TEMPORARY_URL`, ..).
4. The result? A valid, signed, **publicly accessible** temporary URL. 🎉

---

## ✅ Example

If your `.env` looks like this:

```dotenv
AWS_ENDPOINT=http://minio:9000
AWS_URL=https://cdn.example.com
```

Then, when you do:

```php
Storage::disk('minio')->temporaryUrl('photos/pic.jpg', now()->addMinutes(10));
```

You’ll get something like:

```
https://cdn.example.com/photos/pic.jpg?X-Amz-Algorithm=AWS4-HMAC-SHA256&X-Amz-Credential=...
```

Instead of:

```
http://minio:9000/photos/pic.jpg?X-Amz-Algorithm=AWS4-HMAC-SHA256&X-Amz-Credential=...
```

---

## 📖 Credits

* [Laravel Documentation: MinIO](https://laravel.com/docs/12.x/filesystem#minio)
* Original idea from [ Łukasz Tkacz](https://tkacz.pro/laravel-temporary-urls-minio/)

---

## 📝 License

This package is open-sourced software, licensed under the [MIT license](LICENSE.md).
