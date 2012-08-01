<?php
/**
 * @version    $Id$
 */
class Process_DelUser extends Process
{
    /**
     * _execute
     * 
     * @return mixed
     */
    protected function _execute()
    {
        $result = $this->model('user')->delNonactivatedUser();
        $status = $result === false ? 'failure' : ($result > 0 ? 'success' : 'done');

        return $this->flash(0, "Delete nonactivated user {$status}! Success:" . (int)$result);
    }
}
// End of file : DelUser.php
