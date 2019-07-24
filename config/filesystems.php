<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default filesystem disk that should be used
    | by the framework. A "local" driver, as well as a variety of cloud
    | based drivers are available for your choosing. Just store away!
    |
    | Supported: "local", "ftp", "s3", "rackspace"
    |
    */

    'default' => env('FILESYSTEM_DRIVER', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Default Cloud Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Many applications store files both locally and in the cloud. For this
    | reason, you may specify a default "cloud" driver here. This driver
    | will be bound as the Cloud disk implementation in the container.
    |
    */


    'cloud' => env('FILESYSTEM_CLOUD', 's3'),

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Here you may configure as many filesystem "disks" as you wish, and you
    | may even configure multiple disks of the same driver. Defaults have
    | been setup for each driver as an example of the required options.
    |
    | Supported Drivers: "local", "ftp", "sftp", "s3", "rackspace"
    |
    */

    'disks' => [
        'adPicsSFTP'     => [
            'driver'     => 'sftp',
            'host'       => env('SFTP_HOST', ''),
            'port'       => env('SFTP_PORT', '22'),
            'username'   => env('SFTP_USERNAME', ''),
            'password'   => env('SFTP_PASSSWORD', ''),
            'privateKey' => env('SFTP_PRIVATE_KEY_PATH', ''),
            'root'       => env('SFTP_ROOT','').env('DOWNLOAD_SERVER_IMAGES_PARTIAL_PATH','').'/ads',
            'timeout'    => env('SFTP_TIMEOUT', '10'),
            'prefix'     => null,
            'dHost'      => env('DOWNLOAD_SERVER_NAME',''),
            'dProtocol'  => env('DOWNLOAD_SERVER_PROTOCOL',''),
        ],
        's3' => [
            'driver' => 's3',
            'key'    => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => env('AWS_BUCKET'),
            'url'    => env('AWS_URL'),
        ],
    ],

];
