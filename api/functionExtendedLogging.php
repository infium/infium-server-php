<?php
/*
 * Copyright 2012-2017 Infium AB
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */


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