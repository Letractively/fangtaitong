<?php
/**
 * @version    $Id$
 */
class Master_MberController extends MasterController
{
    /**
     * 查询旅店当前可用会员编号接口
     */
    public function validMuqnoAction()
    {
        $uqno = $this->input('uqno');
        if ($uqno != '' && mb_strlen($uqno) <= 30 && !$this->model('mber')->getMberByNo($uqno, $this->_hostel['h_id']))
        {
            $this->close(json_encode(true));
        }

        $this->close(json_encode(false));
    }

    /**
     * 根据会员编号获取会员信息接口
     */
    public function fetchAbledMberByUqnoAction()
    {
        $uqno = $this->input('uqno');
        if ($uqno != '' && ($mber = $this->model('mber')->getMberByNo($uqno, $this->_hostel['h_id'])))
        {
            if (!$mber['m_status'])
            {
                $this->flash(0, '会员已停用');
            }

            $this->flash(1, array('context' => array(
                'uqid'   => $mber['m_id'],
                'uqno'   => $mber['m_no'],
                'name'   => $mber['m_name'],
                'idno'   => $mber['m_idno'],
                'type'   => $mber['m_type'],
                'mail'   => $mber['m_email'],
                'call'   => $mber['m_phone'],
                'memo'   => $mber['m_memo'],
                'idtype' => $mber['m_idtype'],
            )));
        }

        $this->flash(0, '找不到指定的会员');
    }

    /**
     * 会员帐号列表
     */
    public function indexAction()
    {
        $where = array(
            'm_hid = ' . $this->model('mber')->quote($this->_master['u_hid']),
        );
        $mbers = array();

        $pager = array();

        $pager['qnty'] = (int)$this->input('qnty', 'numeric');
        empty($pager['qnty']) AND $pager['qnty'] = 25;
        $pager['qnty'] = min($pager['qnty'], 30);

        $pager['page'] = (int)$this->input('page', 'numeric');
        empty($pager['page']) AND $pager['page'] = 1;

        $query = array();
        $query['qnty'] = $pager['qnty'];

        $query['name'] = $this->input('name');

        $query['type'] = $this->input('type');
        in_array($query['type'], array('uqid', 'uqno', 'idno', 'name', 'mail', 'call'), true) OR $query['type'] = $query['name'] = null;

        $query['state'] = $this->input('state', 'array');
        $query['state'] OR $query['state'] = array();
        $query['state'] = array_unique($query['state']);
        if (!empty($query['state']))
        {
            $where[] = 'm_status IN (' . implode(',', array_map(array($this->model('mber'), 'quote'), $query['state'])) . ')';
        }

        $query['types'] = $this->input('types', 'array');
        $query['types'] OR $query['types'] = array();
        $query['types'] = array_unique($query['types']);
        if (!empty($query['types']))
        {
            $where[] = 'm_type IN (' . implode(',', array_map(array($this->model('mber'), 'quote'), $query['types'])) . ')';
        }

        if ($query['name'] != '' && $query['type'] != '')
        {
            if ($query['type'] == 'uqid')
            {
                $where[] = 'm_id = ' . $this->model('mber')->quote($query['name']);
            }
            if ($query['type'] == 'uqno')
            {
                $where[] = 'm_no = ' . $this->model('mber')->quote($query['name']);
            }
            if ($query['type'] == 'idno')
            {
                $where[] = 'm_idno = ' . $this->model('mber')->quote($query['name']);
            }
            else if ($query['type'] == 'name')
            {
                $where[] = 'm_name = ' . $this->model('mber')->quote($query['name']);
            }
            else if ($query['type'] == 'mail')
            {
                $where[] = 'm_email = ' . $this->model('mber')->quote($query['name']);
            }
            else if ($query['type'] == 'call')
            {
                $where[] = 'm_phone = ' . $this->model('mber')->quote($query['name']);
            }
        }

        $mbers = Zyon_Array::keyto(
            $this->model('mber')->fetchAry(
                $where,
                'm_id DESC',
                array($pager['qnty'], $pager['qnty']*($pager['page'] - 1))
            ),
            'm_id'
        );
        empty($mbers) AND $mbers = array();

        $types = $this->model('mber')->getMberTypeAryByHid($this->_hostel['h_id']);

        $pager['list'] = count($mbers);
        $pager['args'] = $query;

        $this->view->pager = $pager;
        $this->view->query = $query;
        $this->view->mbers = $mbers;
        $this->view->types = $types;
    }

    /**
     * 创建会员帐号
     */
    public function createAction()
    {
        $this->view->types = $this->model('mber')->getMberTypeAryByHid($this->_hostel['h_id']);
    }

    public function doCreateAction()
    {
        $memo   = $this->input('memo');
        $name   = $this->input('name');
        $type   = $this->input('type');
        $uqno   = $this->input('uqno');
        $idno   = $this->input('idno');
        $idtype = $this->input('idtype');
        $status = $this->input('status') ? '1' : '0';
        $email  = $this->input('email');
        $phone  = $this->input('phone');

        $mber = $this->model('mber')->getNewMber($this->_hostel['h_id'], $uqno, $name, $status);
        $mber['m_type']   = $type;
        $mber['m_memo']   = $memo;
        $mber['m_idno']   = $idno;
        $mber['m_idtype'] = $idtype;
        $mber['m_email']  = $email;
        $mber['m_phone']  = $phone;

        if (!$this->model('mber')->verify($mber))
        {
            $this->flash(0, '会员资料不符合系统规范');
        }

        if ($info = $this->model('mber')->getDupMber($mber))
        {
            $this->flash(0, array(
                'forward' => null,
                'message' => __('会员资料与编号为 %s 的会员重复', $info['m_no']),
                'content' => __('<a target="_blank" href="/master/mber/update?mid=%s">点击这里查看详情</a>', $info['m_id']),
            ));
        }

        if ($mid = $this->model('mber')->addMber($mber))
        {
            $this->flash($mid, array(
                'timeout' => 3,
                'forward' => "/master/mber/",
                'message' => '创建会员成功',
            ));
        }

        $this->flash(0);
    }

    /**
     * 更新会员信息
     */
    public function updateAction()
    {
        $this->view->mber  = $this->loadUsableMber($this->input('mid'));
        $this->view->types = $this->model('mber')->getMberTypeAryByHid($this->_hostel['h_id']);
    }

    public function doUpdateAction()
    {
        $this->loadUsableMber($mbid = $this->input('mid'));

        $memo   = $this->input('memo');
        $name   = $this->input('name');
        $type   = $this->input('type');
        $idno   = $this->input('idno');
        $idtype = $this->input('idtype');
        $status = $this->input('status') ? '1' : '0';
        $email  = $this->input('email');
        $phone  = $this->input('phone');

        $mber['m_memo']   = $memo;
        $mber['m_name']   = $name;
        $mber['m_type']   = $type;
        $mber['m_idno']   = $idno;
        $mber['m_idtype'] = $idtype;
        $mber['m_status'] = $status;
        $mber['m_email']  = $email;
        $mber['m_phone']  = $phone;

        if (!$this->model('mber')->verify($mber))
        {
            $this->flash(0, '会员资料不符合系统规范');
        }

        if ($info = $this->model('mber')->getDupMber($mber))
        {
            if ($info['m_id'] != $mbid)
            {
                $this->flash(0, array(
                    'forward' => null,
                    'message' => __('会员资料与编号为 %s 的会员重复', $info['m_no']),
                    'content' => __('<a target="_blank" href="/master/mber/update?mid=%s">点击这里查看详情</a>', $info['m_id']),
                ));
            }
        }

        if ($this->model('mber')->modMber($mbid, $mber))
        {
            $this->flash(1);
        }

        $this->flash(0);
    }
}
// End of file : MberController.php
