<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Dev import (local tooling)
    |--------------------------------------------------------------------------
    |
    | When disabled, /dev/import/* routes are not registered. Production should
    | leave this false (default when APP_ENV is not "local").
    |
    | Set DEV_IMPORT_ENABLED=true explicitly to enable on a non-local server.
    |
    */
    'enabled' => filter_var(
        env('DEV_IMPORT_ENABLED', env('APP_ENV') === 'local' ? '1' : '0'),
        FILTER_VALIDATE_BOOLEAN
    ),

    /*
    |--------------------------------------------------------------------------
    | Optional SQL dump path
    |--------------------------------------------------------------------------
    |
    | Path to the phpMyAdmin-style dump used to parse users/wrestlers rows.
    | If unset or the file is missing, the importer uses cache-only mode:
    | storage/dev_import_cache/{users,wrestlers}.json when present.
    |
    | Production typically has no dump; leave unset and keep dev import off.
    |
    */
    'sql_path' => env('DEV_IMPORT_SQL_PATH'),

];
