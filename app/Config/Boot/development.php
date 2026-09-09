<?php

/*
 |--------------------------------------------------------------------------
 | ERROR DISPLAY
 |--------------------------------------------------------------------------
 | In development, we want to show as many errors as possible to help
 | make sure they don't make it to production. And save us hours of
 | painful debugging.
 |
 | If you set 'display_errors' to '1', CI4's detailed error report will show.
 */
error_reporting(E_ALL);
ini_set('display_errors', '1');

/*
 |--------------------------------------------------------------------------
 | DEBUG BACKTRACES
 |--------------------------------------------------------------------------
 | If true, this constant will tell the error screens to display debug
 | backtraces along with the other error information. If you would
 | prefer to not see this, set this value to false.
 */
defined('SHOW_DEBUG_BACKTRACE') || define('SHOW_DEBUG_BACKTRACE', true);

/*
 |--------------------------------------------------------------------------
 | DEBUG MODE
 |--------------------------------------------------------------------------
 | Debug mode is an experimental flag that can allow changes throughout
 | the system. This will control whether Kint is loaded, and a few other
 | items. It can always be used within your own application too.
 */
// Overridable via .env (CI_DEBUG = false) so the Debug Toolbar's per-request
// query/timer/view collection overhead can be turned off during normal
// day-to-day use — while still keeping CI_ENVIRONMENT=development for full
// error detail pages — without switching the whole app to production. Read
// raw from $_ENV/getenv (not the env() helper, which isn't loaded yet at
// this point in bootstrap) and parsed as a real boolean, since the raw
// string "false" is otherwise truthy in PHP.
$ci_debug_env = $_ENV['CI_DEBUG'] ?? getenv('CI_DEBUG');
defined('CI_DEBUG') || define('CI_DEBUG', $ci_debug_env === false ? true : filter_var($ci_debug_env, FILTER_VALIDATE_BOOLEAN));
