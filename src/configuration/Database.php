<?php declare(strict_types=1);
namespace configuration;

final class Database {
    // DRIVER currently only supports mysqli
    const DRIVER        = 'mysqli';
    const HOST          = '127.0.0.1';
    const USER          = 'root';
    const PASSWORD      = '';
    const DATABASENAME  = 'dmsf_project';

    const USE_UTF8      = true;
}