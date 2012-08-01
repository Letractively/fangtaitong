<?php
/**
 * @version    $Id$
 */
class Model_Room_PriceTest extends ModelTestCase
{
    public function testGetPriceDotAry()
    {
        var_dump($this->model('room.price')->getPriceDotAry(
            2, '12000', strtotime('2012-02-01'), strtotime('2012-02-28')
        ));
    }
}
// End of file : Equip.php
