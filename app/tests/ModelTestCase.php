<?php
/**
 * @version    $Id$
 */
require_once 'TestCase.php';
class ModelTestCase extends TestCase
{
    /**
     * model
     * 
     * @param string $name 
     * @param string $base
     * @return mixed
     */
    public function model($name, $base = 'ftt')
    {
        return Model::factory($name, $base);
    }
}
// End of file : ModelTestCase.php
