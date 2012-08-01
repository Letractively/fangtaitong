<?php
/**
 * @version    $Id$
 */
class Model_Ftt_Acl extends Zyon_Model_Ftt implements Zyon_Acl_Instance_Discover
{
    /**
     * _role
     * 角色映射
     * 
     * @var array
     */
    protected static $_role = array(
        'master' => array(
        ),
    );

    /**
     * _rule
     * 权限映射
     * 
     * @var array
     */
    protected static $_rule = array(
        'master' => array(
            // 修改旅店信息、房态规则
            array('num' => '1', 'res' => array(
                '/master/hostel/do-.*',
            )),

            // 添加、修改、停用及启用、设置默认预订类型、预订渠道、收款渠道、结算方式
            array('num' => '2', 'res' => array(
                '/master/typedef/do-.*',
                '/master/channel/do-.*',
                '/master/payment/do-.*',
                '/master/settlem/do-.*',
            )),

            // 添加、修改房间资料
            array('num' => '4', 'res' => array(
                '/master/room/do-.*',
            )),

            // 停用及启用房间
            array('num' => '8', 'res' => array(
                '/master/room/do-retain',
            )),

            // 订单修改（房费、属性）
            array('num' => '16', 'res' => array(
                '/master/order/do-update-attr-yxhf',

                '/master/order/do-update-info-xsry',
                '/master/order/do-update-info-ydlx',
                '/master/order/do-update-info-ydqd',
                '/master/order/do-update-info-glhy',
                '/master/order/do-update-info-fxmx',

                '/master/order/do-modify',
            )),

            // 减免账单应收款
            array('num' => '32', 'res' => array(
                '/master/bill/do-discount-expense',
            )),

            // 报表统计（入住类）
            array('num' => '64', 'res' => array(
                '/master/stat/rzjl',
                '/master/stat/tfjl',
                '/master/stat/yzjl',
                '/master/stat/ytjl',
            )),

            // 报表统计（结算类）
            array('num' => '128', 'res' => array(
                '/master/stat/skls',
                '/master/stat/jsbb-skqddzmx',
                '/master/stat/jsbb-ydkrdzmx',
                '/master/stat/jsbb-ydqddzmx',
                '/master/stat/jsbb-xsrydzmx',
            )),

            // 报表统计（销售类）
            array('num' => '256', 'res' => array(
                '/master/stat/xsbb-ydqd',
                '/master/stat/xsbb-ydlx',
                '/master/stat/xsbb-syfj',
            )),

            // 创建订单
            array('num' => '512', 'res' => array(
                '/master/order/do-create',
            )),

            // 账单封解
            array('num' => '1024', 'res' => array(
                '/master/bill/do-locked',
                '/master/bill/do-unlock',
            )),
        ),

        'member' => array(
        ),
    );

    /**
     * discover
     * 
     * @param mixed $accessor
     * @return Zyon_Acl_Instance
     */
    public function discover($accessor)
    {
        $instance = new Zyon_Acl_Instance;
        $instance->extend('');

        if ($accessor instanceof Zyon_Acl_Accessor)
        {
            $identity = $accessor->getIdentity();
            $basename = $accessor->getBasename();

            switch ($basename)
            {
            case 'master':
                if ($user = Model::factory('user', 'ftt')->getUser($identity))
                {
                    $role = ($user['u_role']) & USER_ROLE_ROOTER ? 'rooter' : 'master';

                    $instance->extend($role);
                    foreach (static::$_rule['master'] as $val)
                    {
                        $decision = ((int)$user['u_permit'] & (int)$val['num']) ? true : false;
                        foreach ($val['res'] as $resource)
                        {
                            $instance->handle($resource, $decision);
                        }
                    }
                }

                break;

            default :
                break;
            }
        }

        return $instance;
    }
}
// End of file : Acl.php
