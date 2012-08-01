<?php
/**
 * @version    $Id$
 */
class Controller extends Zend_Controller_Action
{
    /**
     * @var array
     */
    protected $_caches = array();

    /**
     * @var array
     */
    protected $_extras = array();

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

    /**
     * input
     * 
     * @param string $name
     * @param string $type
     * @return mixed
     */
    public function input($name = null, $type = 'string')
    {
        if ($name === null)
        {
            return $this->getRequest();
        }

        if (call_user_func('is_' . $type, ($ret = $this->getRequest()->getParam($name))))
        {
            if ($type === 'string')
            {
                return trim($ret);
            }

            return $ret;
        }

        if ($type === 'string')
        {
            return '';
        }
    }

    /**
     * cache
     * 
     * @param string $name 
     * @return mixed
     */
    public function cache($name = null)
    {
        if (!isset($this->_caches[$name]))
        {
            $this->_caches[$name] = $this->getInvokeArg('bootstrap')->getCache($name);
        }

        return $this->_caches[$name];
    }

    /**
     * extra
     * 
     * @param string $name 
     * @return mixed
     */
    public function extra($name)
    {
        if (!isset($this->_extras[$name]))
        {
            $this->_extras[$name] = call_user_func(array($this->getInvokeArg('bootstrap'), 'get' . ucfirst($name)));
        }

        return $this->_extras[$name];
    }

    /**
     * error
     * 
     * @param mixed $info 
     * @return void
     */
    public function error($info)
    {
        $this->getInvokeArg('bootstrap')->getLog()->err($info);
    }

    /**
     * debug
     * 
     * @param mixed $info 
     * @return void
     */
    public function debug($info)
    {
        $this->getInvokeArg('bootstrap')->getLog()->debug($info);
    }

    /**
     * close
     * 
     * @param mixed $body
     * @return void
     */
    public function close($body = null)
    {
        if (func_num_args())
        {
            $this->getResponse()->setBody((string)$body);
        }

        $this->getResponse()->sendResponse();exit;
    }

    /**
     * flash
     * Send flash message to client
     * 
     * @param int          $stat status code, usually 1(success) or 0(failure).
     * @param array|string $vars message or storage of vars.
     * @return void
     */
    public function flash($stat, $vars = null)
    {
        $req = $this->getRequest();
        $res = $this->getResponse();

        if (!is_array($vars))
        {
            $vars = array('message' => ($vars === null ? null : (string)$vars));
        }

        $vars['stacode'] = (int)$stat;
        $vars['success'] = $vars['stacode'] > 0 ? 1 : 0;
        $vars['message'] = array_key_exists('message', $vars) ? htmlspecialchars(__((string)$vars['message'])) : null;
        $vars['content'] = array_key_exists('content', $vars) ? $vars['content'] : null;
        $vars['context'] = array_key_exists('context', $vars) ? $vars['context'] : null;
        $vars['forward'] = array_key_exists('forward', $vars) ? $vars['forward'] : ($req->getServer('HTTP_REFERER') ? 'javascript:history.go(-1)' : ('/' . $req->getModuleName()));
        $vars['timeout'] = array_key_exists('timeout', $vars) ? (int)$vars['timeout'] : 3;

        if (isset($vars['rescode']) && is_numeric($vars['rescode']))
        {
            $res->setHttpResponseCode($vars['rescode']);
        }

        if ($req->getParam('ct') == 'json') // 'content-type'
        {
            // $res->setHeader('Content-Type', 'text/json');
            $res->setBody(json_encode($vars), 'json');
        }
        elseif (!$vars['timeout'])
        {
            $this->_helper->getHelper('Redirector')
                ->setGotoUrl($vars['forward']);
        }
        else
        {
            $this->view->assign($vars);

            $res->setBody(
                $this->view->render('flash.' . $this->_helper->viewRenderer->getViewSuffix()),
                'flash'
            );
        }

        $this->_helper->viewRenderer->setNoRender();
        $res->sendResponse();exit;
    }

    /**
     * force
     *
     * @param mixed  $data
     * @param string $name
     * @param string $type
     * @return void
     */
    public function force($data, $name, $type)
    {
        $size = strlen($data);

        $this->getResponse()->clearAllHeaders()->clearBody();

        // Support chinese filename.
        if (preg_match("/msie/i", $this->getRequest()->getServer('HTTP_USER_AGENT')))
        {
            $this->getResponse()->setHeader(
                'Content-Disposition', 'attachment; filename="' . str_replace("+", "%20", urlencode($name)) . '"'
            );
        }
        else if (preg_match("/firefox/i", $this->getRequest()->getServer('HTTP_USER_AGENT')))
        {
            $this->getResponse()->setHeader(
                'Content-Disposition', "attachment; filename*=\"utf8''" . str_replace("+", "%20", urlencode($name)) . '"'
            );
        }
        else
        {
            $this->getResponse()->setHeader(
                'Content-Disposition', "attachment;filename={$name}"
            );
        }

        $this->getResponse()
            ->setHeader('Content-type', $type)
            ->setHeader('Content-Length', $size)
            ->setHeader('Cache-Control', 'must-revalidate,post-check=0,pre-check=0')
            ->setHeader('Pragma', 'public')
            ->setHeader('Expires', '0')
            ->setBody($data)
            ->sendResponse();

        exit;
    }

    /**
     * check
     * Check user permission
     * 
     * @param mixed $accessor
     * @param mixed $resource
     * @return bool
     */
    public function check($accessor = null, $resource = null)
    {
        $req = $this->getRequest();

        if ($resource === null)
        {
            $resource = $req->getModuleName() . '/' . $req->getControllerName() . '/' . $req->getActionName();
        }

        if (!($accessor instanceof Zyon_Acl_Accessor))
        {
            $accessor = new Zyon_Acl_Accessor($accessor);
        }

        return $this->getInvokeArg('bootstrap')->getAcl()->verify($accessor, $resource);
    }
}
// End of file : Controller.php
