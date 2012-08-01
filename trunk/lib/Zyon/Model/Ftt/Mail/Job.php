<?php
/**
 * @version    $Id$
 */
class Zyon_Model_Ftt_Mail_Job extends Zyon_Model_Ftt
{
    /**
     * addJob
     * 
     * @param array $map
     * @return string
     */
    public function addJob($map)
    {
        if (empty($map) || !is_array($map))
        {
            return false;
        }

        if (!isset($map['mj_from_name'][0]) || !is_string($map['mj_from_name'])
            || !isset($map['mj_to_name']) || !is_string($map['mj_to_name'])
            || !isset($map['mj_to_email'][5]) || !Zyon_Util::isEmail($map['mj_to_email'])
            || !isset($map['mj_title'][0]) || !is_string($map['mj_title'])
            || !isset($map['mj_content'][0]) || !is_string($map['mj_content'])
        )
        {
            return false;
        }

        if (!isset($map['mj_ctime']))
        {
            $map['mj_ctime'] = time();
        }

        try
        {
            $this->dbase()->insert($this->tname('mail_job'), $map);
            return $this->dbase()->lastInsertId();
        }
        catch (Exception $e)
        {
            $this->log($e);
            return false;
        }
    }

    /**
     * getJob
     * 
     * @param int $id 
     * @return array
     */
    public function getJob($id)
    {
        if (empty($id) || !is_numeric($id))
        {
            return false;
        }

        try
        {
            $sql = $this->dbase()->select()->from($this->tname('mail_job'))->where('mj_id = ?')->limit(1);
            return $this->dbase()->fetchRow($sql, $id);
        }
        catch (Exception $e)
        {
            $this->log($e);
            return false;
        }
    }

    /**
     * modJob
     * 
     * @param int   $id
     * @param array $map 
     * @return int
     */
    public function modJob($id, $map)
    {
        if (empty($id) || !is_numeric($id) || empty($map) || !is_array($map))
        {
            return false;
        }

        try
        {
            return $this->dbase()->update($this->tname('mail_job'), $map, 'mj_id = ' . $this->quote($id));
        }
        catch (Exception $e)
        {
            $this->log($e);
            return false;
        }
    }

    /**
     * getUndoJobIds
     * 
     * @param int $remainder
     * @param int $base
     * @param int $limit
     * @return array
     */
    public function getUndoJobIds($remainder, $base, $limit = 0)
    {
        if (!Zyon_Util::isUnsignedInt($remainder) || !Zyon_Util::isUnsignedInt($base) || !Zyon_Util::isUnsignedInt($limit))
        {
            return false;
        }

        try
        {
            $sql = $this->dbase()->select()->from($this->tname('mail_job'), 'mj_id')
                ->where('mj_sendtimes = 0 OR (mj_sendtimes < 3 && mj_ecode > 0)')
                ->where('mj_id%' . $base . ' = ' . $remainder)
                ->order('mj_id');

            $limit AND $sql->limit($limit);

            return $this->dbase()->fetchCol($sql);
        }
        catch (Exception $e)
        {
            $this->log($e);
            return false;
        }
    }

    /**
     * checkTimesLimit
     * 
     * @param string $to_email 
     * @param string $title 
     * @param string $from_name 
     * @return bool
     */
    public function checkTimesLimit($to_email, $title, $from_name)
    {
        if (!isset($from_name[0]) || !is_string($from_name)
            || !isset($title[0]) || !is_string($title)
        )
        {
            return false;
        }

        if (!Zyon_Util::isEmail($to_email))
        {
            return false;
        }

        $now   = time();
        $rules = array(
            $now - 86400 => 5, // 24小时内只允许发5次
            $now - 3600  => 3, // 1小时内只允许发3次
            $now - 600   => 1, // 10分钟内只允许发1次
        );
        // ksort($rules, SORT_NUMERIC);

        try
        {
            $sql = $this->dbase()->select()->from($this->tname('mail_job'), 'mj_ctime as ctime')
                ->where('mj_to_email = :to_email')
                ->where('mj_title = :title')
                ->where('mj_from_name = :from_name')
                ->where('mj_ctime >= ?', min(array_keys($rules)))
                ->order('mj_ctime ASC')
                ->limit(max($rules));

            if ($ret = $this->dbase()->fetchCol($sql, array(
                'to_email' => $to_email,
                'title' => $title,
                'from_name' => $from_name
            )))
            {
                if (isset($ret[max($rules)-1]))
                {
                    return false;
                }

                if (!isset($ret[min($rules)-1]))
                {
                    return true;
                }

                foreach ($rules as $ctime => $limit)
                {
                    foreach ($ret as $idx => $val)
                    {
                        if ($val < $ctime)
                        {
                            unset($ret[$idx]);
                        }
                        else
                        {
                            break;
                        }
                    }

                    if (empty($ret))
                    {
                        return true;
                    }

                    if (count($ret) >= $limit)
                    {
                        return false;
                    }
                }
            }
        }
        catch (Exception $e)
        {
            $this->log($e);
            return false;
        }

        return true;
    }

    /**
     * getNewJob
     * 
     * @param string $to_name 
     * @param string $to_email 
     * @param string $title 
     * @param string $content 
     * @param string $from_name 
     * @return array
     */
    public function getNewJob($to_name, $to_email, $title, $content, $from_name)
    {
        return array(
            'mj_to_name' => trim((string)$to_name),
            'mj_to_email' => trim((string)$to_email),
            'mj_title' => trim((string)$title),
            'mj_content' => trim((string)$content),
            'mj_from_name' => trim((string)$from_name),
        );
    }
}
// End of file : Job.php
