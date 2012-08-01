<?php
/**
 * @version    $Id$
 */
class Model_OrderTest extends ModelTestCase
{
    public function testAddOrder()
    {
        $order = $this->model('order')->getNewOrder('1', '1', time(), time() + 86400, $this->model('order')->getStateCodeByName('已结束'));
        $this->assertSame(true, is_numeric($this->model('order')->addOrder($order)));
    }
}
