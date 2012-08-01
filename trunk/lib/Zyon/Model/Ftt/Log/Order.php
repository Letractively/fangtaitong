<?php
/**
 * @version    $Id$
 */
class Zyon_Model_Ftt_Log_Order extends Zyon_Model_Ftt_Log
{
    /**
     * _acts
     * 
     * @var array
     */
    protected $_acts = array(
        'create' => 'ORDER_DO_CREATE',
        'update' => 'ORDER_DO_UPDATE',
        'delete' => 'ORDER_DO_DELETE',

        'change_room' => 'ORDER_ROOM_DO_CHANGE',

        'update_guest' => 'ORDER_GUEST_DO_UPDATE',

        'update_status' => 'ORDER_DO_UPDATE_STATUS',

        'create_by_gser' => 'ORDER_DO_CREATE_BY_GSER',
    );

    /**
     * getNewCreateLog
     * 
     * @param array $oper
     * @param array $order
     * @return array
     */
    public function getNewCreateLog(array $oper, array $order)
    {
        return $this->getNewLog('create',
            $order['o_hid'], $order['o_id'], '', $oper['u_id'], $oper['u_realname'], null, array($order));
    }

    /**
     * getNewUpdateLog
     * 
     * @param array  $oper
     * @param array  $order_old
     * @param array  $order_new
     * @param string $memo
     * @return array
     */
    public function getNewUpdateLog(array $oper, array $order_old, array $order_new, $memo = null)
    {
        return $this->getNewLog('update',
            $order_old['o_hid'], $order_old['o_id'], '', $oper['u_id'], $oper['u_realname'], $memo, array($order_old, $order_new));
    }

    /**
     * getNewDeleteLog
     * 
     * @param array $oper
     * @param array $order
     * @return array
     */
    public function getNewDeleteLog(array $oper, array $order)
    {
        return $this->getNewLog('delete',
            $order['o_hid'], $order['o_id'], '', $oper['u_id'], $oper['u_realname'], null, array($order));
    }

    /**
     * getNewCreateByGserLog
     * 
     * @param array $oper
     * @param array $order
     * @return array
     */
    public function getNewCreateByGserLog(array $oper, array $order)
    {
        return $this->getNewLog('create_by_gser',
            $order['o_hid'], $order['o_id'], '', 0, $oper['o_gbker_name'], null, array($order));
    }

    /**
     * getNewUpdateGuestLog
     * 
     * @param array $oper
     * @param array $order_old
     * @param array $order_new
     * @return array
     */
    public function getNewUpdateGuestLog(array $oper, array $order_old, array $order_new, $memo = null)
    {
        return $this->getNewLog('update_guest',
            $order_old['o_hid'], $order_old['o_id'], '', $oper['u_id'], $oper['u_realname'], $memo, array($order_old, $order_new));
    }

    /**
     * getNewChangeRoomLog
     * 
     * @param array  $oper
     * @param array  $order_old
     * @param array  $order_new
     * @return array
     */
    public function getNewChangeRoomLog(array $oper, array $order_old, array $order_new)
    {
        return $this->getNewLog('change_room',
            $order_old['o_hid'], $order_old['o_id'], '', $oper['u_id'], $oper['u_realname'],
            "{$order_old['o_room']}=>{$order_new['o_room']}",
            array($order_old, $order_new)
        );
    }

    /**
     * getNewUpdateStatusLog
     * 
     * @param array $oper
     * @param array $order
     * @param array $
     * @return array
     */
    public function getNewUpdateStatusLog(array $oper, array $order_old, array $order_new)
    {
        return $this->getNewLog('update_status',
            $order_old['o_hid'], $order_old['o_id'], '', $oper['u_id'], $oper['u_realname'],
            getOrderStatusNameByCode($order_old['o_status']) . '=>' . getOrderStatusNameByCode($order_new['o_status']),
            array($order_old, $order_new)
        );
    }

    /**
     * getLastLogByOid
     * 
     * @param int $oid
     * @return array
     */
    public function getLastLogByOid($oid)
    {
        if (empty($oid) || !is_numeric($oid))
        {
            return false;
        }

        try
        {
            $sql = $this->dbase()->select()->from($this->tname($this->_tbl))
                ->where("{$this->_pre}xid = ?")
                ->order("{$this->_pre}id DESC")
                ->limit(1);
            return $this->decode($this->dbase()->fetchRow($sql, $oid));
        }
        catch (Exception $e)
        {
            $this->log($e);
            return false;
        }
    }
}
// End of file : Order.php
