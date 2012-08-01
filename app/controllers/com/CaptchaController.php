<?php
/**
 * @version    $Id$
 */
class Com_CaptchaController extends Controller
{
    /**
     * Generate captcha image.
     */
    public function imageAction()
    {
        $actions = array(
            '/hostel/order/do-create',

            '/master/index/do-signin',
            '/master/index/do-signup',
            '/master/index/do-password-recovery',
            '/master/index/do-send-activate-mail',
        );

        $action = $this->input('a');
        if (in_array($action, $actions, true))
        {
            $captcha = new Geek_Captcha_Image($action);
            $captcha->setWordlen(4)->render();
        }

        $this->_helper->viewRenderer->setNoRender();
    }
}
// End of file : CaptchaController.php
