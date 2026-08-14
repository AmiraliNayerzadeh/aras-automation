<?php

use App\Providers\AppServiceProvider;
use App\Providers\FileServiceProvider;
use App\Providers\HrServiceProvider;
use App\Providers\TaskServiceProvider;

return [
    AppServiceProvider::class,
    HrServiceProvider::class,
    TaskServiceProvider::class,
    FileServiceProvider::class,
];
