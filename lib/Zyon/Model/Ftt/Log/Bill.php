<?php
/**
 * @version    $Id$
 */
class Zyon_Model_Ftt_Log_Bill extends Zyon_Model_Ftt_Log
{
    /**
     * _acts
     * 
     * @var array
     */
    protected $_acts = array(
        'create' => 'BILL_DO_CREATE',
        'update' => 'BILL_DO_UPDATE',
        'create_by_gser' => 'BILL_DO_CREATE_BY_GSER',
    );

    /**
     * getNewCreateLog
     * 
     * @param array $oper
     * @param array $bill
     * @return array
     */
    public function getNewCreateLog(array $oper, array $bill)
    {
        return $this->getNewLog('create',
            $bill['b_hid'], $bill['b_id'], $bill['b_name'], $oper['u_id'], $oper['u_realname'], null, array($bill));
    }

    /**
     * getNewUpdateLog
     * 
     * @param array  $oper
     * @param array  $bill_old
     * @param array  $bill_new
     * @param string $memo
     * @return array
     */
    public function getNewUpdateLog(array $oper, array $bill_old, array $bill_new, $memo = null)
    {
        return $this->getNewLog('update',
            $bill_old['b_hid'], $bill_old['b_id'], $bill_old['b_name'], $oper['u_id'], $oper['u_realname'], $memo, array($bill_old, $bill_new));
    }

    /**
     * getNewCreateByGserLog
     * 
     * @param array $oper
     * @param array $bill
     * @return array
     */
    public function getNewCreateByGserLog(array $oper, array $bill)
    {
        return $this->getNewLog('create_by_gser',
            $bill['b_hid'], $bill['b_id'], $bill['b_name'], 0, $oper['o_gbker_name'], null, array($bill));
    }
}
// End of file : Bill.php
