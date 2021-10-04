<?php declare(strict_types=1);
namespace configuration;

use ReflectionClass;

final class Config {
    const ENVIRONMENT_DEBUG = 'DEBUG';
    const ENVIRONMENT_LIVE  = 'LIVE';
    const CURRENT_ENV       = self::ENVIRONMENT_DEBUG;

    const BASE_URL              = 'http://localhost/DMSF/';
    const ICON                  = '';
    const DEFAULT_CONTROLLER    = 'Index';
    const DEFAULT_TITLE         = 'DMSF Project';

    const JAVASCRIPT_MINIFIER   = 'https://javascript-minifier.com/raw';

    const IP_WHITELIST = [
        '127.0.0.1',
        '::1'
    ];

    const SESSION_NAME = 'dmsf_session';
}