<?php

/*
 * iPocket Standard PHP Library
 * Copyright (C) 2014 iPocket Team <https://github.com/iPocket/iPocket-SPL>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
*/

interface LogLevel{
    const EMERGENCY = "emergency";
    const ALERT = "alert";
    const CRITICAL = "critical";
    const ERROR = "error";
    const WARNING = "warning";
    const NOTICE = "notice";
    const INFO = "info";
    const DEBUG = "debug";
}