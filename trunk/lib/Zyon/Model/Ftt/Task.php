<?php
/**
 * @version    $Id$
 */
class Zyon_Model_Ftt_Task extends Zyon_Model_Ftt
{
    /**
     * getTodoWaitByHotel
     * 
     * @param array $hotel
     * @return int
     */
    public function getTodoWaitByHotel($hotel)
    {
        return getSysLimit('TASK_TODO_WAIT', isset($hotel['h_level']) ? $hotel['h_level'] : null);
    }

    /**
     * getTodoDaysByHotel
     * 
     * @param array $hotel
     * @return int
     */
    public function getTodoDaysByHotel($hotel)
    {
        return getSysLimit('TASK_TODO_DAYS', isset($hotel['h_level']) ? $hotel['h_level'] : null);
    }

    /**
     * getTodoQntyByHotel
     * 
     * @param array $hotel
     * @return int
     */
    public function getTodoQntyByHotel($hotel)
    {
        return getSysLimit('TASK_TODO_QNTY', isset($hotel['h_level']) ? $hotel['h_level'] : null);
    }
}
// End of file : Task.php
