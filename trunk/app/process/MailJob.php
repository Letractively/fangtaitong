<?php
/**
 * @version    $Id$
 */
class Process_MailJob extends Process
{
    /**
     * _options
     * 
     * @var array
     */
    protected $_options;

    /**
     * _remainder
     * 
     * @var int
     */
    protected $_remainder;

    /**
     * _base
     * 
     * @var int
     */
    protected $_base;

    /**
     * _limit
     * 
     * @var mixed
     */
    protected $_limit;

    /**
     * __construct
     * 
     * @param array $options
     * @param int   $remainder
     * @param int   $base
     * @return void
     */
    public function __construct(array $options, $remainder, $base)
    {
        $this->_options = $options;

        if (empty($this->_options['host'])
            || empty($this->_options['port'])
            || empty($this->_options['user'])
            || empty($this->_options['pass'])
        )
        {
            exit($this->flash(1, 'Falied to init mailer: Invalid Arguments'));
        }

        $this->_remainder = (int)$remainder;
        $this->_base      = (int)$base;
        $this->_limit     = 10;
    }

    /**
     * _execute
     * 
     * @return mixed
     */
    protected function _execute()
    {
        $success = $failure = array();

        if ($ids = $this->model('mail.job')->getUndoJobIds($this->_remainder, $this->_base, $this->_limit))
        {
            foreach ($ids as $id)
            {
                if (($row = $this->model('mail.job')->getJob($id)) && $row['mj_sendtimes'] < 3)
                {
                    $mail = new PHPMailer;

                    $mail->CharSet    = 'UTF-8'; // UTF-8 charset
                    $mail->IsSMTP(); // telling the class to use SMTP
                    $mail->SMTPDebug  = 1; // enables SMTP debug information, 1: errors and messages, 2: messages only
                    $mail->SMTPAuth   = true;                  // enable SMTP authentication
                    $mail->SMTPSecure = "ssl";                 // sets the prefix to the servier
                    $mail->Host       = $this->_options['host'];  // sets GMAIL as the SMTP server
                    $mail->Port       = $this->_options['port'];  // set the SMTP port for the GMAIL server
                    $mail->Username   = $this->_options['user'];  // GMAIL username
                    $mail->Password   = $this->_options['pass'];  // GMAIL password
                    $mail->Subject    = "=?UTF-8?B?" . base64_encode($row['mj_title']) . "?=";

                    $mail->SetFrom($this->_options['user'], "=?UTF-8?B?". base64_encode($row["mj_from_name"]) . "?=");
                    $mail->MsgHTML($row['mj_content']);
                    $mail->AddAddress($row['mj_to_email'], "=?UTF-8?B?" . base64_encode($row['mj_to_name']) . "?=");

                    $ecode = $mail->Send() ? 0 : 1;
                    $this->model('mail.job')->modJob($row['mj_id'], array(
                        'mj_ecode'     => $ecode,
                        'mj_sendtimes' => $this->model('mail.job')->expr('mj_sendtimes+1'),
                    ));

                    if ($ecode)
                    {
                        $failure[] = $id;
                        $this->error($mail->ErrorInfo);
                    }
                    else
                    {
                        $success[] = $id;
                    }
                }
            }
        }

        return $this->flash(0, sprintf("Mail job Done! Success:%d[%s], Failure:%d[%s]",
            sizeof($success),
            implode(',', $success),
            sizeof($failure),
            implode(',', $failure))
        );
    }
}
// End of file : MailJob.php
