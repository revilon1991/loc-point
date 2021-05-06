<?php

if (file_exists('/tmp/loc-point/cache/prod/App_KernelProdContainer.preload.php')) {
    opcache_compile_file('/tmp/loc-point/cache/prod/App_KernelProdContainer.preload.php');
}
