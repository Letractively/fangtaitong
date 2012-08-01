<?php
/**
 * @version    $Id$
 *
 * Writes DB events as log messages to the console.
 */
class Zyon_Db_Profiler_Console extends Zend_Db_Profiler
{
    /**
     * Intercept the query end and log the profiling data.
     *
     * @param  integer $queryId
     * @throws Zend_Db_Profiler_Exception
     * @return void
     */
    public function queryEnd($queryId)
    {
        $state = parent::queryEnd($queryId);
        if (!$this->getEnabled() || $state == self::IGNORED)
        {
            return;
        }

        $profile = $this->getQueryProfile($queryId);

        echo (string)round($profile->getElapsedSecs(), 5)
            . PHP_EOL . $profile->getQuery()
            . PHP_EOL . (($params = $profile->getQueryParams()) ? $params : null)
            . PHP_EOL;
    }
}
// End of file : Console.php
