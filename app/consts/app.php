<?php
/**
 * @version    $Id$
 */
if (defined('APP_FTT')) {return;} else {define('APP_FTT', 'OSV');}

date_default_timezone_set('PRC');

defined('APPLICATION_LOG_FILE')
    || define('APPLICATION_LOG_FILE', (
        getenv('APPLICATION_LOG_FILE')
        ? getenv('APPLICATION_LOG_FILE')
        : APPLICATION_PATH . '/../var/logs/app' . date('Ym.W') . '.log'
    )
);

defined('APPLICATION_LOG_CLI_FILE')
    || define('APPLICATION_LOG_CLI_FILE', (
        getenv('APPLICATION_LOG_CLI_FILE')
        ? getenv('APPLICATION_LOG_CLI_FILE')
        : APPLICATION_PATH . '/../var/logs/app.cli.' . date('Ym.W') . '.log'
    )
);

defined('APPLICATION_LOG_SVR_FILE')
    || define('APPLICATION_LOG_SVR_FILE', (
        getenv('APPLICATION_LOG_SVR_FILE')
        ? getenv('APPLICATION_LOG_SVR_FILE')
        : APPLICATION_PATH . '/../var/logs/app.svr.' . date('Ym.W') . '.log'
    )
);


// 旅店根用户最高权限，INT(31)
define('PERMIT_ROOTER', 2147483647);
// 旅店管理员最高权限
define('PERMIT_MASTER', 2047);
