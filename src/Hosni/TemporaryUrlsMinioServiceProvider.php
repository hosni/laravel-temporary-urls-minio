<?php

namespace Hosni;

use Illuminate\Filesystem\AwsS3V3Adapter;
use Illuminate\Filesystem\FilesystemManager;
use Illuminate\Filesystem\MinioS3V3Adapter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Foundation\Application;

class TemporaryUrlsMinioServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->app->extend('filesystem', $this->extendFilesystem(...));
        $this->app->extend(FilesystemManager::class, $this->extendFilesystem(...));
    }

    protected function extendFilesystem(FilesystemManager $filesystem): FilesystemManager
    {
        return $filesystem->extend(
            's3',
            static function (Application $app, array $config) use ($filesystem) {
                $adapter = $filesystem->createS3Driver($config);

                if (isset($config['temporary_url']) and $adapter instanceof AwsS3V3Adapter) {
                    /**
                     * @var AwsS3V3Adapter
                     */
                    $virtualAdapter = $filesystem->createS3Driver(array_merge($config, [
                        'endpoint' => $config['temporary_url'],
                    ]));

                    return MinioS3V3Adapter::fromAwsS3V3AdapterWithVirtualAdapter($adapter, $virtualAdapter);
                }

                return $adapter;
            }
        );
    }
}