<?php

class Ccontact extends contact
{

    public
        $C,               //main object
        $lang,
        $idC,
        $feedback;

    protected
        $_resPath,
        $_template,
        $_emailTemplate,
        $_emailBody,
        $_email,
        $_envelope;

    public function __construct(&$C)
    {/*{{{*/
    }/*}}}*/

    public function _init()
    {/*{{{*/
        $this->resPath = $this->C->get_resPath_forObj($this);
    }/*}}}*/

    private function setDisplay()
    {/*{{{*/
        if (isset($_POST['save_contact'])) {
             $content = $_POST['ContactDdetails_'.$this->lang];
             file_put_contents($this->resPath, $content);
        }

        $this->template = file_get_contents($this->resPath);
    }/*}}}*/

    private function setFeedback()
    {/*{{{*/
    }/*}}}*/

    private function sendMail()
    {/*{{{*/
        if (defined('smtpPort'))
            $mail = new Mail(smtpServer, smtpPort);
        else
            $mail = new Mail(smtpServer);

        $mail->username = smtpUser;
        $mail->password = smtpPass;

        $mail->SetFrom(smtpUser);    // Name is optional

        $mail->AddTo('vnitu@ceata.org');

        $mail->subject = $_POST['subject'];
        $mail->message = $this->_emailBody;

        // Chestii optionale
        // Note: contentType defaults to "text/plain; charset=iso-8859-1"
        $mail->contentType = "text/html";
        $mail->headers['Reply-To']=$_POST['email'];

        //  unset ($_POST);

        return $mail->Send();
    }/*}}}*/

    public function processForm()
    {/*{{{*/
        $envelopeData = array(
            'name'     => array('name, 3, 60',
                                _('Numele complet (între 3 și 60 caractere')),
            'subject'  => array('text, 5, 30',
                            _('Subiectul (minim 5 caractere)')),
            'message'  => array('text, 10, n',
                            _('Mesajul (minim 10 caractere)'))
                       );

        $this->feedback = '';

        require "./assets/securimage/securimage.php";
        $securimage = new Securimage();

        $this->env = new Envelope($envelopeData);
        if ($this->env->status == TRUE
            &&  $securimage->check($_POST['captcha_code']) == TRUE) {
            $this->buildEmail();
            $this->sendMail();
            $this->feedback = "<b style='font-size: 14px;'>"
                ._('Mesajul a fost trimis!')
                ."</b>";

        } else {
            $this->feedback .= '<b>'
                ._('Please correct the following fields:')
                .'</b><br/>';

            if ($securimage->check($_POST['captcha_code']) != TRUE)
                $this->feedback  .= "<b>Cod gresit</b> <br/>";

            foreach ($this->env->errors as $value)
                $this->feedback .= $value."\n<br/>";

        }
    }/*}}}*/

    private function buildEmail()
    {/*{{{*/

    }/*}}}*/
}
