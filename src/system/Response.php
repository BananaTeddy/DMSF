<?php declare(strict_types=1);
namespace system;

final class Response {
    // 200 - 299 success
    const OK                    = 200;
    const CREATED               = 201;
    const ACCEPTED              = 202;
    const NO_CONTENT            = 204;

    // 300 - 399 redirects
    const MOVED_PERMANENTLY     = 301;
    const MOVED_TEMPORARILY     = 302;

    // 400 - 499 client errors
    const BAD_REQUEST           = 400;
    const UNAUTHORIZED          = 401;
    const FORBIDDEN             = 403;
    const FILE_NOT_FOUND        = 404;
    const GONE                  = 410;
    const URI_TOO_LONG          = 414;

    // 500 - 599 server errors
    const INTERNAL_SERVER_ERROR = 500;
    const NOT_IMPLEMENTED       = 501;
    const SCRIPT_GONE_WRONG     = 555;
}
