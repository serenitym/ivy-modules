<?php
class contact {

var $sp_fullname;
var $sp_address;
var $sp_email;
var $sp_telephone;
var $sp_subject;
var $sp_message;
var $sp_submit;
var $sp_reset;

var $strings_ro = array(  'Numele complet',
                          'Adresa',
                          'Email',
                          'Telefon',
                          'Subiect',
                          'Mesaj',
                          'Trimite',
                          'Reseteaza'
                       );
var $strings_en = array(  'Name',
                          'Address',
                          'E-mail',
                          'Telephone',
                          'Subject',
                          'Message',
                          'Submit',
                          'Reset'
                       );

var $name =   '';
var $address = '';
var $email =   '';
var $phone =    '';
var $subject=   '';
var $message='' ;

 var $content;

    function setcontact($LG, $RESpath)
    {
        list($this->sp_fullname,
        $this->sp_address,
        $this->sp_email,
        $this->sp_telephone,
        $this->sp_subject,
        $this->sp_message,
        $this->sp_submit,
        $this->sp_reset )     =   $this->{'strings_'.$LG};



         extract($_POST, EXTR_REFS | EXTR_OVERWRITE);

         $this->content =  file_get_contents($RESpath);


    }
}
class Ccontact extends contact{

    var $C;               //main object
    var $LG;
    var $idC;
    var $feedback;
    var $feedback_GOOD='';

    var $resPath;


    var $message;
    var $env;


    public function setDISPLAY()  {
        if(isset($_POST['save_contact']))
         {
             $content = $_POST['ContactDdetails_'.$this->LG];
             file_put_contents($this->resPath,$content);
         }

        $this->setcontact($this->LG,$this->resPath);
    }
    public  function _setINI()     {

           $this->resPath = $this->C->get_resPath_forObj($this);
           $this->setDISPLAY();

           if(isset($_POST['action']))    $this->processContact();
       }



    public  function set_message()    {

        $name =  $address =   $email =  $phone = $subject=  $message=''  ;
        extract($_POST, EXTR_REFS | EXTR_OVERWRITE);

        $this->message = "From: <b>{$name}</b>\r\n<br/>
                        <em>Address:</em> {$address} \r\n<br/>
                        <em>Email:</em> <a href='mailto:{$email}'>{$email}</a>
                            \r\n<br/>
                        <em>Phone:</em>{$phone}\r\n<br/><br/>
                        <em>Subject:</em> {$subject}\r\n<br/>
                        \r\n<br/>
                        <em>Message:</em> {$message}\r\n<br/>
                        \r\n<br/>--\r\n<br/>Please do NOT reply to this email";



    }
    private function sendMail()       {
        if (defined('smtpPort'))
            $mail = new Mail(smtpServer,smtpPort);
        else
            $mail = new Mail(smtpServer);
        $mail->Username = smtpUser;
        $mail->Password = smtpPass;

        $mail->SetFrom(smtpUser);    // Name is optional

        // $mail->AddTo('ioana@serenitymedia.ro');       // Name is optional
        $mail->AddTo('vnitu@ceata.org');

        $mail->Subject = $_POST['subject'];
        $mail->Message = $this->message;

        // Chestii optionale
        $mail->ContentType = "text/html";        		    // Default in "text/plain; charset=iso-8859-1"
        $mail->Headers['Reply-To']=$_POST['email'];

        //  unset ($_POST);

        return $mail->Send();
     }
    private function processContact() {
        //envArgs = (' $string,$min,$max ' , '$description')
        $roEnv = array(
            'name'     => array('name, 3, 60',
                            'Numele complet (intre 3 si 60 de caractere)'),
            'address'  => array('text, 10, n',
                            'Full address  (minim 10 caractere)'),
            'email'    => array('email'     ,
                            'Adresa de email'),
            'phone'    => array('numeric_punctuation, 1',
                            'Numarul de telefon (minim 10 caractere, numere si/sau punctuatie)'),
            'subject'  => array('text, 5, 30',
                            'Subiectul (minim 5 caractere)'),
            'message'  => array('text, 10, n',
                            'Mesajul (minim 10 caractere)')
                       );

        $enEnv = array(
            'name'     => array('name, 3, 60',
                            'Numele complet (intre 3 si 60 de caractere)'),
            'address'  => array('text, 10, n',
                            'Full address  (minim 10 caractere)'),
            'email'    => array('email'     ,
                            'Adresa de email'),
            'phone'    => array('numeric_punctuation, 1',
                            'Numarul de telefon (minim 10 caractere, numere si/sau punctuatie)'),
            'subject'  => array('text, 5, 30',
                            'Subiectul (minim 5 caractere)'),
            'message'  => array('text, 10, n',
                            'Mesajul (minim 10 caractere)')
                       );


      #=================================================================================================================

        $this->feedback = '';

        require "./fw/PLUGINS/securimage/securimage.php";
        $securimage = new Securimage();



        $this->env = new Envelope(${$this->LG.'Env'});
        if($this->env->status == TRUE   &&  $securimage->check($_POST['captcha_code']) == TRUE)
        {
            $this->set_message();
            $this->sendMail();
            $this->feedback_GOOD = "<b style='font-size: 14px;'>Mesajul a fost transmis cu succes!</b>";

        }
        else
        {
            $this->feedback .= ($this->LG == 'en' ? '<b>Please correct the following fields:</b><br/>' : '<b>Corectati urmatoarele campuri:</b><br/>');
            if($securimage->check($_POST['captcha_code']) != TRUE)  $this->feedback  .= "<b>Cod gresit</b> <br/>";

            foreach ($this->env->errors as $value)    $this->feedback .= $value."\n<br/>";

        }

    }





    function __construct(&$C){

        #deci ideea este ca _setINI poate fii creat aici dar trebuie preluate manual chestii precum lang , DB sau altele

    }



}
