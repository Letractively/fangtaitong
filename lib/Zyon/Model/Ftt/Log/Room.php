<?php
/**
 * @version    $Id$
 */
class Zyon_Model_Ftt_Log_Room extends Zyon_Model_Ftt_Log
{
    /**
     * _acts
     * 
     * @var array
     */
    protected $_acts = array(
        'create' => 'ROOM_DO_CREATE',
        'update' => 'ROOM_DO_UPDATE',
        'delete' => 'ROOM_DO_DELETE',

        'retain_tolive' => 'ROOM_DO_RETAIN_TOLIVE',
        'retain_tostop' => 'ROOM_DO_RETAIN_TOSTOP',

        'update_status' => 'ROOM_DO_UPDATE_STATUS',

        'update_basic_price' => 'ROOM_DO_UPDATE_BASIC_PRICE',
    );

    /**
     * getNewCreateLog
     * 
     * @param array $oper
     * @param array $room
     * @return array
     */
    public function getNewCreateLog(array $oper, array $room)
    {
        return $this->getNewLog('create',
            $room['r_hid'], $room['r_id'], $room['r_name'], $oper['u_id'], $oper['u_realname'], null, array($room));
    }

    /**
     * getNewUpdateLog
     * 
     * @param array $oper
     * @param array $room_old
     * @param array $room_new
     * @param mixed $memo
     * @return array
     */
    public function getNewUpdateLog(array $oper, array $room_old, array $room_new, $memo = null)
    {
        return $this->getNewLog('update',
            $room_old['r_hid'], $room_old['r_id'], $room_old['r_name'], $oper['u_id'], $oper['u_realname'], $memo, array($room_old, $room_new));
    }

    /**
     * getNewDeleteLog
     * 
     * @param array $oper
     * @param array $room
     * @return array
     */
    public function getNewDeleteLog(array $oper, array $room)
    {
        return $this->getNewLog('delete',
            $room['r_hid'], $room['r_id'], $room['r_name'], $oper['u_id'], $oper['u_realname'], null, array($room));
    }

    /**
     * getNewRetainToLiveLog
     * 
     * @param array $oper
     * @param array $room_old
     * @param array $room_new
     * @return array
     */
    public function getNewRetainToLiveLog(array $oper, array $room_old, array $room_new)
    {
        return $this->getNewLog('retain_tolive',
            $room_old['r_hid'], $room_old['r_id'], $room_old['r_name'], $oper['u_id'], $oper['u_realname'], null, array($room_old, $room_new));
    }

    /**
     * getNewRetainToStopLog
     * 
     * @param array $oper
     * @param array $room_old
     * @param array $room_new
     * @return array
     */
    public function getNewRetainToStopLog(array $oper, array $room_old, array $room_new)
    {
        return $this->getNewLog('retain_tostop',
            $room_old['r_hid'], $room_old['r_id'], $room_old['r_name'], $oper['u_id'], $oper['u_realname'], null, array($room_old, $room_new));
    }

    /**
     * getNewUpdateStatusLog
     * 
     * @param array $oper
     * @param array $room_old
     * @param array $room_new
     * @return array
     */
    public function getNewUpdateStatusLog(array $oper, array $room_old, array $room_new)
    {
        return $this->getNewLog('update_status',
            $room_old['r_hid'], $room_old['r_id'], $room_old['r_name'], $oper['u_id'], $oper['u_realname'],
            getRoomStatusNameByCode($room_old['r_status']) . '=>' . getRoomStatusNameByCode($room_new['r_status']),
            array($room_old, $room_new)
        );
    }

    /**
     * getNewUpdateBasicPriceLog
     * 
     * @param array $oper
     * @param array $room_old
     * @param array $room_new
     * @return array
     */
    public function getNewUpdateBasicPriceLog(array $oper, array $room_old, array $room_new)
    {
        return $this->getNewLog('update_basic_price',
            $room_old['r_hid'], $room_old['r_id'], $room_old['r_name'], $oper['u_id'], $oper['u_realname'],
            $room_old['r_price']/100 . ' => ' . $room_new['r_price']/100,
            array($room_old, $room_new)
        );
    }
}
// End of file : Room.php
