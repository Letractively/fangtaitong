<?php
/**
 * @version    $Id$
 */
class Master_HostelController extends MasterController
{
    public function indexAction()
    {
        $this->updateAction();
        $this->_helper->viewRenderer->setScriptAction('update');
    }

    /**
     * 旅店信息编辑页
     */
    public function updateAction()
    {
    }

    public function doUpdateAction()
    {
        $address  = $this->input('address');
        $contact  = $this->input('contact');
        $website  = $this->input('website');

        if ($address == '' || empty($contact))
        {
            $this->flash(0, '必填项没有填写完整');
        }

        if ($website !== '' && !Zyon_Util::isUrl($website))
        {
            $this->flash(0, '网站地址格式错误');
        }

        $map = array(
            'h_phone'    => $contact,
            'h_address'  => $address,
            'h_website'  => $website,
            'h_province' => $this->input('province'),
            'h_city'     => $this->input('city'),
            'h_title'    => $this->input('title'),
            'h_note'     => $this->input('note'),
        );

        if ($this->model('hotel')->modHotel($this->_hostel['h_id'], $map))
        {
            $this->flash(1);
        }
        else
        {
            $this->flash(0);
        }
    }

    /**
     * 旅店的房态规则编辑页
     */
    public function updateRuleAction()
    {
    }

    public function doUpdateRuleAction()
    {
        $h_order_default_stacode   = $this->input('order_default_stacode', 'numeric');
        $h_order_enddays           = $this->input('order_enddays'        , 'numeric');
        $h_order_minlens           = $this->input('order_minlens'        , 'numeric');
        $h_order_maxlens           = $this->input('order_maxlens'        , 'numeric');
        $h_prompt_checkin          = $this->input('prompt_checkin'       , 'numeric');
        $h_prompt_checkout         = $this->input('prompt_checkout'      , 'numeric');

        $checkin_time_hour         = $this->input('checkin_time_hour'    , 'numeric');
        $checkin_time_min          = $this->input('checkin_time_min'     , 'numeric');
        $checkout_time_hour        = $this->input('checkout_time_hour'   , 'numeric');
        $checkout_time_min         = $this->input('checkout_time_min'    , 'numeric');

        $keptime_hour              = $this->input('keptime_hour'         , 'numeric');

        foreach (get_defined_vars() as $key => $val)
        {
            if ($key !== 'this' && !isset($val[0]))
            {
                $this->flash(0, '必须的项没有填写完整');
            }
        }

        if ($h_order_default_stacode !== ORDER_STATUS_YD && $h_order_default_stacode !== ORDER_STATUS_BL)
        {
            $this->flash(0, '不被允许的新订单默认状态');
        }

        isset($checkin_time_hour[1])  OR $checkin_time_hour = '0' . $checkin_time_hour;
        isset($checkin_time_min[1])   OR $checkin_time_min = '0' . $checkin_time_min;
        isset($checkout_time_hour[1]) OR $checkout_time_hour = '0' . $checkout_time_hour;
        isset($checkout_time_min[1])  OR $checkout_time_min = '0' . $checkout_time_min;

        if (!Zyon_Util::isTime($checkin_time_hour . ':' . $checkin_time_min))
        {
            $this->flash(0, '入住时间格式错误');
        }

        if (!Zyon_Util::isTime($checkout_time_hour . ':' . $checkout_time_min))
        {
            $this->flash(0, '离店时间格式错误');
        }

        $h_checkin_time = $checkin_time_hour*3600 + $checkin_time_min*60;
        $h_checkout_time = $checkout_time_hour*3600 + $checkout_time_min*60;

        if ($h_checkin_time < $h_checkout_time)
        {
            $this->flash(0, '入住时间必须大于离店时间');
        }

        if ($keptime_hour < 0 || $keptime_hour > 72 || !Zyon_Util::isUnsignedInt($keptime_hour))
        {
            $this->flash(0, '账单预订保留过期时间错误');
        }

        $h_obill_keptime = $keptime_hour*3600;

        $map = array(
            'h_attr'               => $this->model('hotel')->expr(
                $this->input('keptime', 'numeric')
                ? 'h_attr | ' . HOTEL_ATTR_ZDGQ : 'h_attr & ' . ~(int)HOTEL_ATTR_ZDGQ
            ),

            'h_rosta_visible'       => $this->model('hotel')->expr(
                $this->input('rosta_visible', 'numeric')
                ? 'h_rosta_visible | ' . SYSTEM_GROUPS_GSER : 'h_rosta_visible & ' . ~(int)SYSTEM_GROUPS_GSER
            ),

            'h_order_enabled'       => $this->model('hotel')->expr(
                $this->input('order_enabled', 'numeric')
                ? 'h_order_enabled | ' . SYSTEM_GROUPS_GSER : 'h_order_enabled & ' . ~(int)SYSTEM_GROUPS_GSER
            ),

            'h_order_default_stacode' => $h_order_default_stacode,
            'h_obill_keptime'         => $h_obill_keptime,
            'h_order_enddays'         => $h_order_enddays,
            'h_order_minlens'         => $h_order_minlens,
            'h_order_maxlens'         => $h_order_maxlens,
            'h_prompt_checkin'        => $h_prompt_checkin,
            'h_prompt_checkout'       => $h_prompt_checkout,
            'h_checkin_time'          => $h_checkin_time,
            'h_checkout_time'         => $h_checkout_time,
        );

        $this->flash((bool)$this->model('hotel')->modHotel($this->_hostel['h_id'], $map));
    }
}
// End of file : HostelController.php
