<?php

class Ccontact
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


        $gettextDir = fw_pubPath . "MODELS/contact/tmpl_". $this->template ."/i18n";
        $gettextDomain = "messages";
        $encoding = "UTF-8";

        bindtextdomain($gettextDomain, $gettextDir);
        bind_textdomain_codeset($gettextDomain, $encoding);

        textdomain($gettextDomain);

        $this->resPath = $this->C->get_resPath_forObj($this);

        if (isset($_POST['action']) && $_POST['action'] == 'contact') {
            $this->processForm();
        }

        $this->setDisplay();
    }/*}}}*/

    private function setDisplay()
    {/*{{{*/
        if (isset($_POST['save_contact'])) {
             $content = $_POST['ContactDdetails_'.$this->lang];
             file_put_contents($this->resPath, $content);
        }


        foreach ($this->envelopeData as $key => $value) {
            $this->{$key} = isset($_POST[$key])
                ? $_POST[$key]
                : '';
        }

        //$this->template = file_get_contents($this->resPath);
    }/*}}}*/

    private function setFeedback()
    {/*{{{*/
    }/*}}}*/

    private function buildEmail($type = 'html')
    {/*{{{*/

        $emailHtmlTemplate = "MODELS/contact/tmpl_" . $this->template
            ."/tmpl/email.html";
        $emailTextTemplate = "MODELS/contact/tmpl_" . $this->template
            ."/tmpl/email.txt";

        switch ($type) {
            case 'html':
                $this->emailHtml = $this->_emailBody =
                    $this->C->renderDisplay_fromObj(
                        $this, '', $emailHtmlTemplate
                    );
                break;
            default:
            case 'text':
                $this->emailText = sprintf(
                    $emailTextTemplate,
                    $this->subject,
                    $this->senderName,
                    $this->senderMail,
                    $this->message
                );
                break;
        }

        return true;
    }/*}}}*/

    private function sendMail()
    {/*{{{*/
        if (defined('smtpPort'))
            $mail = new Mail(smtpServer, smtpPort);
        else
            $mail = new Mail(smtpServer);

        $mail->username = smtpUser;
        $mail->password = smtpPass;

        $mail->SetFrom(smtpUser, 'Ivy CMS contact form');    // Name is optional

        $mail->AddTo($this->destinationEmail);

        $mail->subject = 'Subject: ' . $_POST['subject'];
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

        $this->feedback = '';

        require "./assets/securimage/securimage.php";
        $securimage = new Securimage();

        $this->env = new Envelope($this->envelopeData);
        if ($this->env->status == TRUE
            &&  $securimage->check($_POST['captcha_code']) == TRUE) {

                $this->senderName = $this->env->items['name']['content'];
                $this->senderMail = $this->env->items['email']['content'];
                $this->subject    = $this->env->items['subject']['content'];
                $this->message    = $this->env->items['message']['content'];

            $this->buildEmail();
            $this->sendMail();
            $this->feedback = "<b style='font-size: 14px;'>"
                ._('Mesajul a fost trimis!')
                ."</b>";
            unset($_POST);

        } else {
            $this->feedback .= '<b>'
                ._('Corectați următoarele câmpuri:')
                .'</b><br/>';

            if ($securimage->check($_POST['captcha_code']) != TRUE)
                $this->feedback  .= "<b>"._('Codul CAPTCHA')."</b> <br/>";

            foreach ($this->env->errors as $value)
                $this->feedback .= _($value)."\n<br/>";

        }
    }/*}}}*/

}
