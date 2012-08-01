<?php
/**
 * @version    $Id$
 */

/**
 * Global Constants
 * ====================================================================================================
 */
// SFZ 身份证
// HZ 护照
// JGZ 军官证
// QT 其它
define('SYSTEM_IDTYPE_SFZ', '0');
define('SYSTEM_IDTYPE_HZ' , '1');
define('SYSTEM_IDTYPE_JGZ', '2');
define('SYSTEM_IDTYPE_QT' , '9');

// AUTO 系统
// USER 用户
// GSER 旅客
// MBER 会员
define('SYSTEM_GROUPS_AUTO', '0');
define('SYSTEM_GROUPS_USER', '1');
define('SYSTEM_GROUPS_MBER', '2');
define('SYSTEM_GROUPS_GSER', '4');

// '账单过期' => '1',
define('HOTEL_ATTR_ZDGQ', '1');

// '允许换房' => '1',
// '在线订单' => '2',
define('ORDER_ATTR_YXHF', '1');
define('ORDER_ATTR_ZXDD', '2');

// 'bldd' => array('name' => '保留订单', 'code' => 'bldd'),
// 'qxdd' => array('name' => '取消订单', 'code' => 'qxdd'),
// 'blrz' => array('name' => '办理入住', 'code' => 'blrz'),
// 'blhf' => array('name' => '办理换房', 'code' => 'blhf'),
// 'bltf' => array('name' => '办理退房', 'code' => 'bltf'),
define('ORDER_ACTION_BLDD', 'bldd');
define('ORDER_ACTION_QXDD', 'qxdd');
define('ORDER_ACTION_BLRZ', 'blrz');
define('ORDER_ACTION_BLHF', 'blhf');
define('ORDER_ACTION_BLTF', 'bltf');

// '预订' => '1',
// '保留' => '2',
// '在住' => '4',
// '查房中' => '8',
// '已结束' => '16',
// '已取消' => '32',
define('ORDER_STATUS_YD', '1');
define('ORDER_STATUS_BL', '2');
define('ORDER_STATUS_ZZ', '4');
define('ORDER_STATUS_YJS', '16');
define('ORDER_STATUS_YQX', '32');

// '应入住'     => '256',
// '应退房'     => '512',
// '应住未住'   => '1024',
// '应退未退'   => '2048',
// '过期未入住' => '4096',
// '房态冲突'   => '8192',
define('ORDER_REALTIME_STATUS_YRZ', '256');
define('ORDER_REALTIME_STATUS_YTF', '512');
define('ORDER_REALTIME_STATUS_YZWZ', '1024');
define('ORDER_REALTIME_STATUS_YTWT', '2048');
define('ORDER_REALTIME_STATUS_GQWZ', '4096');
define('ORDER_REALTIME_STATUS_FTCT', '8192');

// '干净空房' => '1',
// '清洁中' => '2',
// '查房中' => '4',
// '入住中' => '8',
define('ROOM_STATUS_GJKF', '1');
define('ROOM_STATUS_RZZ', '8');

// '停用' => '256'
// '隐藏' => '512'
define('ROOM_REALTIME_STATUS_TY', '256');
define('ROOM_REALTIME_STATUS_YC', '512');

// 'ckct' => array('name' => '查看冲突', 'code' => 'ckct'),
// 'tyfj' => array('name' => '停用房间', 'code' => 'tyfj'),
// 'qyfj' => array('name' => '启用房间', 'code' => 'qyfj'),
// 'klfj' => array('name' => '克隆房间', 'code' => 'klfj'),
// 'bltf' => array('name' => '办理查房', 'code' => 'blcf'),
// 'tfjz' => array('name' => '退房结帐', 'code' => 'tfjz'),
define('ROOM_ACTION_KLFJ', 'klfj');
define('ROOM_ACTION_TFJZ', 'tfjz');

// 'ycft' => 隐藏房态
define('ROOM_ATTR_YCFT', '4');

// 'GQTX' => 过期提醒
define('BILL_ATTR_GQTX', '1');

// 'KF' => '开放'
// 'GB' => '关闭'
define('BILL_STATUS_KF', '1');
define('BILL_STATUS_GB', '2');

// '已过期'     => '128',
define('BILL_REALTIME_STATUS_YGQ', '128');

// kfzd => 开放账单
// gbzd => 关闭账单
// qtfy => 其它费用
// xgbz => 修改备注
// jsfs => 结算方式
// gqsj => 过期时间
define('BILL_ACTION_KFZD', 'kfzd');
define('BILL_ACTION_GBZD', 'gbzd');
define('BILL_ACTION_QTFY', 'qtfy');
define('BILL_ACTION_SKTK', 'sktk');
define('BILL_ACTION_XGBZ', 'xgbz');
define('BILL_ACTION_JSFS', 'jsfs');
define('BILL_ACTION_GQSJ', 'gqsj');

// JH 激活
// DL 登录
define('USER_ACTIVE_JH', '1');
define('USER_ACTIVE_DL', '2');

// rooter 根用户
// master 管理者
define('USER_ROLE_ROOTER', '1');
define('USER_ROLE_MASTER', '0');

// 报表统计周期
define('STAT_LGTH_D', 'D'); // 日
define('STAT_LGTH_W', 'W'); // 周
define('STAT_LGTH_M', 'M'); // 月
define('STAT_LGTH_S', 'S'); // 季
define('STAT_LGTH_Y', 'Y'); // 年

// 0.	    异常，仅供浏览
// 1.		结束
// 2.		空房
// 3.		预订
// 4.		保留
// 5.		在住
define('ROSTA_TS', 0);
define('ROSTA_JS', 1);
define('ROSTA_KF', 2);
define('ROSTA_YD', 3);
define('ROSTA_BL', 4);
define('ROSTA_ZZ', 5);

/**
 * 旅店统计
 */
define('HSTAT_CLASS_HOTEL_RZL', '0'); // 旅店入住率
define('HSTAT_CLASS_RTYPE_RZL', '1'); // 房型入住率

/**
 * 旅店旅客类型
 */
define('HOTEL_GUEST_TYPE_LIVE', '0'); // 旅店入住人
define('HOTEL_GUEST_TYPE_BOOK', '1'); // 旅店预订人


/**
 * Global functions
 * ====================================================================================================
 */

/**
 * __
 * Translates the given string
 * returns the translation
 *
 * @param  string $msg Translation string
 * @return string
 */
if (!function_exists('__'))
{function __($msg)
{
    if (!Zend_Registry::isRegistered('translate'))
    {
        $translate = new Zend_Translate('array', __DIR__ . '/Zyon.lang.php', 'auto');
        if (is_file($file = APPLICATION_PATH . '/languages/' . $translate->getLocale() . '.php'))
        {
            $translate->addTranslation($file);
        }

        Zend_Registry::set('translate', $translate);
    }

    $msg = (string)$msg;
    $ret = (string)Zend_Registry::get('translate')->getAdapter()->translate($msg, null);

    if (func_num_args() > 1)
    {
        $args = func_get_args();
        $args[0] = $ret;

        return call_user_func_array('sprintf', $args);
    }

    return $ret;
}}

/**
 * getSysLimit
 * 
 * @param string $key
 * @param string $ver
 * @return mixed
 */
function getSysLimit($key, $ver = null)
{
    static $limit = array(
        // 报表统计规则
        'STAT_WAIT' => array('0' => 43200, '5' => 28800, '10' => 28800, '20' => 21600, '30' => 14400, '40' => 7200, '50' => 3600),
        'STAT_DAYS' => array(
            '0'   => array('min' => '100',  'max' => '31',  'len' => array('D', 'W', 'M')),
            '5'   => array('min' => '100',  'max' => '31',  'len' => array('D', 'W', 'M')),
            '10'  => array('min' => '500',  'max' => '31',  'len' => array('D', 'W', 'M')),
            '20'  => array('min' => '500',  'max' => '92',  'len' => array('D', 'W', 'M')),
            '30'  => array('min' => '600',  'max' => '92',  'len' => array('D', 'W', 'M')),
            '40'  => array('min' => '800',  'max' => '366', 'len' => array('D', 'W', 'M')),
            '50'  => array('min' => '1000', 'max' => '366', 'len' => array('D', 'W', 'M')),
        ),

        // 重要任务提醒
        'TASK_TODO_DAYS' => array('0' => 3, '5' => 3, '10' => 3, '20' => 3, '30' => 3, '40' => 3, '50' => 3),
        'TASK_TODO_QNTY' => array('0' => 30, '5' => 30, '10' => 30, '20' => 30, '30' => 30, '40' => 30, '50' => 30),
        'TASK_TODO_WAIT' => array('0' => 3600, '5' => 1800, '10' => 1800, '20' => 1200, '30' => 900, '40' => 600, '50' => 300),

        // 预订订单数量
        'BOOKING_LIVE' => array('0' => 259200, '5' => 259200, '10' => 259200, '20' => 259200, '30' => 259200, '40' => 259200, '50' => 259200),
        'BOOKING_PERN' => array('0' => 30, '5' => 30, '10' => 30, '20' => 30, '30' => 30, '40' => 30, '50' => 30),
        'BOOKING_QNTY' => array('0' => 120, '5' => 150, '10' => 150, '20' => 240, '30' => 360, '40' => 540, '50' => 750),
    );

    $ver == '' AND $ver = '50';
    return isset($limit[$key][$ver]) ? $limit[$key][$ver] : null;
}

/**
 * getSysIdTypes
 * 
 * @return array
 */
function getSysIdTypes()
{
    static $names;
    if ($names === null)
    {
        $names = array();
        foreach (array(
            'SYSTEM_IDTYPE_SFZ',
            'SYSTEM_IDTYPE_HZ',
            'SYSTEM_IDTYPE_JGZ',
            'SYSTEM_IDTYPE_QT',
        ) as $name)
        {
            $names[constant($name)] = __($name);
        }
    }

    return $names;
}

/**
 * getDepositOpers
 * 
 * @return array
 */
function getDepositOpers()
{
    static $opers;
    if ($opers === null)
    {
        $opers = array();
        foreach (array(
            'DEPOSIT_OPERS_XF',
            'DEPOSIT_OPERS_TK',
            'DEPOSIT_OPERS_CZ',
            'DEPOSIT_OPERS_ZS',
        ) as $oper)
        {
            $opers[constant($oper)] = __($oper);
        }
    }

    return $opers;
}

/**
 * getDepositOperNameByCode
 * 
 * @param mixed $code
 * @return mixed
 */
function getDepositOperNameByCode($code)
{
    $opers = getDepositOpers();
    return isset($opers[$code]) ? $opers[$code] : null;
}

/**
 * getDepositRoles
 * 
 * @return array
 */
function getDepositRoles()
{
    static $roles;
    if ($roles === null)
    {
        $roles = array();
        foreach (array(
            'DEPOSIT_ROLES_AUTO',
            'DEPOSIT_ROLES_USER',
            'DEPOSIT_ROLES_OPER',
        ) as $role)
        {
            $roles[constant($role)] = __($role);
        }
    }

    return $roles;
}

/**
 * getDepositRoleNameByCode
 * 
 * @param mixed $code
 * @return mixed
 */
function getDepositRoleNameByCode($code)
{
    $roles = getDepositRoles();
    return isset($roles[$code]) ? $roles[$code] : null;
}

/**
 * getPaymentCodes
 * 
 * @return array
 */
function getPaymentCodes()
{
    static $codes;
    if ($codes === null)
    {
        $codes = array();
        foreach (array(
            'PAYMENT_CODES_YHHK',
            'PAYMENT_CODES_KQZF',
        ) as $code)
        {
            $codes[constant($code)] = __($code);
        }
    }

    return $codes;
}

/**
 * getPaymentCodeNameByCode
 * 
 * @param mixed $code
 * @return mixed
 */
function getPaymentCodeNameByCode($code)
{
    $codes = getPaymentCodes();
    return isset($codes[$code]) ? $codes[$code] : null;
}

/**
 * getPaymentTypes
 * 
 * @return array
 */
function getPaymentTypes()
{
    static $types;
    if ($types === null)
    {
        $types = array();
        foreach (array(
            'PAYMENT_TYPES_CTHD',
            'PAYMENT_TYPES_ZXZF',
        ) as $type)
        {
            $types[constant($type)] = __($type);
        }
    }

    return $types;
}

/**
 * getPaymentTypeNameByCode
 * 
 * @param mixed $code
 * @return mixed
 */
function getPaymentTypeNameByCode($code)
{
    $types = getPaymentTypes();
    return isset($types[$code]) ? $types[$code] : null;
}

/**
 * getServiceClasses
 * 
 * @return array
 */
function getServiceClasses()
{
    static $classes;
    if ($classes === null)
    {
        $classes = array();
        foreach (array(
            'SERVICE_CLASS_BBSJ',
            // 'SERVICE_CLASS_DXZL',
        ) as $class)
        {
            $classes[constant($class)] = __($class);
        }
    }

    return $classes;
}

/**
 * getServiceClassNameByCode
 * 
 * @param mixed $code
 * @return mixed
 */
function getServiceClassNameByCode($code)
{
    $classes = getServiceClasses();
    return isset($classes[$code]) ? $classes[$code] : null;
}

/**
 * getSysIdTypeNameByCode
 * 
 * @param mixed $code
 * @return mixed
 */
function getSysIdTypeNameByCode($code)
{
    $types = getSysIdTypes();
    return isset($types[$code]) ? $types[$code] : null;
}

/**
 * getRoomStatusNameByCode
 * 
 * @param mixed $code
 * @return mixed
 */
function getRoomStatusNameByCode($code)
{
    static $names;
    if ($names === null)
    {
        $names = array();
        foreach (array(
            'ROOM_STATUS_GJKF',
            'ROOM_STATUS_RZZ',
        ) as $name)
        {
            $names[constant($name)] = __($name);
        }
    }

    return isset($names[$code]) ? $names[$code] : null;
}

/**
 * getRoomActionNameByCode
 * 
 * @param mixed $code
 * @return mixed
 */
function getRoomActionNameByCode($code)
{
    static $names;
    if ($names === null)
    {
        $names = array();
        foreach (array(
            'ROOM_ACTION_KLFJ',
            'ROOM_ACTION_TFJZ',
        ) as $name)
        {
            $names[constant($name)] = __($name);
        }
    }

    return isset($names[$code]) ? $names[$code] : null;
}

/**
 * getOrderStatusNameByCode
 * 
 * @param mixed $code
 * @return mixed
 */
function getOrderStatusNameByCode($code)
{
    static $names;
    if ($names === null)
    {
        $names = array();
        foreach (array(
            'ORDER_STATUS_YD',
            'ORDER_STATUS_BL',
            'ORDER_STATUS_ZZ',
            'ORDER_STATUS_YJS',
            'ORDER_STATUS_YQX',
        ) as $name)
        {
            $names[constant($name)] = __($name);
        }
    }

    return isset($names[$code]) ? $names[$code] : null;
}

/**
 * getOrderAttrNameByCode
 * 
 * @param mixed $code
 * @return mixed
 */
function getOrderAttrNameByCode($code)
{
    static $names;
    if ($names === null)
    {
        $names = array();
        foreach (array(
            'ORDER_ATTR_YXHF',
        ) as $name)
        {
            $names[constant($name)] = __($name);
        }
    }

    return isset($names[$code]) ? $names[$code] : null;
}

/**
 * getOrderActionNameByCode
 * 
 * @param mixed $code
 * @return mixed
 */
function getOrderActionNameByCode($code)
{
    static $names;
    if ($names === null)
    {
        $names = array();
        foreach (array(
            'ORDER_ACTION_BLDD',
            'ORDER_ACTION_QXDD',
            'ORDER_ACTION_BLRZ',
            'ORDER_ACTION_BLHF',
            'ORDER_ACTION_BLTF',
        ) as $name)
        {
            $names[constant($name)] = __($name);
        }
    }

    return isset($names[$code]) ? $names[$code] : null;
}

/**
 * getOrderRealTimeStatusNameByCode
 * 
 * @param mixed $code
 * @return mixed
 */
function getOrderRealTimeStatusNameByCode($code)
{
    static $names;
    if ($names === null)
    {
        $names = array();
        foreach (array(
            'ORDER_REALTIME_STATUS_YRZ',
            'ORDER_REALTIME_STATUS_YTF',
            'ORDER_REALTIME_STATUS_YZWZ',
            'ORDER_REALTIME_STATUS_YTWT',
            'ORDER_REALTIME_STATUS_GQWZ',
            'ORDER_REALTIME_STATUS_FTCT',
        ) as $name)
        {
            $names[constant($name)] = __($name);
        }
    }

    return isset($names[$code]) ? $names[$code] : null;
}

/**
 * getRoomRealTimeStatusNameByCode
 * 
 * @param mixed $code
 * @return mixed
 */
function getRoomRealTimeStatusNameByCode($code)
{
    static $names;
    if ($names === null)
    {
        $names = array();
        foreach (array(
            'ROOM_REALTIME_STATUS_TY',
            'ROOM_REALTIME_STATUS_YC',
        ) as $name)
        {
            $names[constant($name)] = __($name);
        }
    }

    return isset($names[$code]) ? $names[$code] : null;
}

/**
 * getStatLgths
 * 
 * @return array
 */
function getStatLgths()
{
    static $lgths;
    if ($lgths === null)
    {
        $lgths = array();
        foreach (array(
            'STAT_LGTH_D',
            'STAT_LGTH_W',
            'STAT_LGTH_M',
            'STAT_LGTH_S',
            'STAT_LGTH_Y',
        ) as $lgth)
        {
            $lgths[constant($lgth)] = __($lgth);
        }
    }

    return $lgths;
}

/**
 * getStatLgthNameByCode
 * 
 * @param mixed $code
 * @return mixed
 */
function getStatLgthNameByCode($code)
{
    $names = getStatLgths();
    return isset($names[$code]) ? $names[$code] : null;
}

/**
 * getBillStatusNameByCode
 * 
 * @param mixed $code
 * @return mixed
 */
function getBillStatusNameByCode($code)
{
    static $names;
    if ($names === null)
    {
        $names = array();
        foreach (array(
            'BILL_STATUS_KF',
            'BILL_STATUS_GB',
        ) as $name)
        {
            $names[constant($name)] = __($name);
        }
    }

    return isset($names[$code]) ? $names[$code] : null;
}

/**
 * getBillActionNameByCode
 * 
 * @param mixed $code
 * @return mixed
 */
function getBillActionNameByCode($code)
{
    static $names;
    if ($names === null)
    {
        $names = array();
        foreach (array(
            'BILL_ACTION_KFZD',
            'BILL_ACTION_GBZD',
            'BILL_ACTION_QTFY',
            'BILL_ACTION_SKTK',
            'BILL_ACTION_XGBZ',
            'BILL_ACTION_JSFS',
            'BILL_ACTION_GQSJ',
        ) as $name)
        {
            $names[constant($name)] = __($name);
        }
    }

    return isset($names[$code]) ? $names[$code] : null;
}

/**
 * getBillRealTimeStatusNameByCode
 * 
 * @param mixed $code
 * @return mixed
 */
function getBillRealTimeStatusNameByCode($code)
{
    static $names;
    if ($names === null)
    {
        $names = array();
        foreach (array(
            'BILL_REALTIME_STATUS_YGQ',
        ) as $name)
        {
            $names[constant($name)] = __($name);
        }
    }

    return isset($names[$code]) ? $names[$code] : null;
}
