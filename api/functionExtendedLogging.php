<?php

function createExtendedLog(){
    syslog(LOG_INFO, 'SERVER_NAME = ' . $_SERVER['SERVER_NAME']);

    if (isset($_SERVER['HTTP_X_CLIENT_PLATFORM'])){
        syslog(LOG_INFO, 'X-Client-Platform = ' . $_SERVER['HTTP_X_CLIENT_PLATFORM']);
    }

    if (isset($_SERVER['HTTP_X_CLIENT_PLATFORM_VERSION'])){
        syslog(LOG_INFO, 'X-Client-Platform-Version = ' . $_SERVER['HTTP_X_CLIENT_PLATFORM_VERSION']);
    }

    if (isset($_SERVER['HTTP_X_CLIENT_PLATFORM_DEVICE'])){
        syslog(LOG_INFO, 'X-Client-Platform-Device = ' . $_SERVER['HTTP_X_CLIENT_PLATFORM_DEVICE']);
    }

    if (isset($_SERVER['HTTP_X_CLIENT_PLATFORM_LANGUAGE'])){
        syslog(LOG_INFO, 'X-Client-Platform-Language = ' . $_SERVER['HTTP_X_CLIENT_PLATFORM_LANGUAGE']);
    }

    if (isset($_SERVER['HTTP_X_CLIENT_APP_VERSION'])){
        syslog(LOG_INFO, 'X-Client-App-Version = ' . $_SERVER['HTTP_X_CLIENT_APP_VERSION']);
    }
}