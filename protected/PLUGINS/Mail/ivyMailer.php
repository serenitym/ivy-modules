<?php
/**
 * Class that deals mainly with email sending, aiming to provide a small API
 * to PEAR's SASL/SMTP monster classes
 * @author Victor Nitu
 * @desc Class that deals mainly with email sending, aiming to provide a small
 * API to PEAR's SASL/SMTP monster classes
 *
 * As of May 2013, integrated into Ivy Framework as default mailer and baptised
 * as ivyMailer / Ivy Mailer
 *
 */

class ivyMailer
{

    const CRLF = "\r\n";

     private
        $_username,
        $_password,
        $_server,
        $_port,
        $_connectTimeout,
        $_responseTimeout,
        $_skt;

      public
        $contentType,
        $headers,
        $body,
        $from,
        $mailTo,
        $mailCc,
        $subject,
        $log;

      public $message = array();

    public function __construct($server = "127.0.0.1", $port = 25)
    {/*{{{*/
        $this->_server = $server;
        $this->_port = $port;
        $this->_connectTimeout = 30;
        $this->_responseTimeout = 8;
        $this->from = array();
        $this->mailTo = array();
        $this->mailCc = array();
        $this->log = array();
        $this->headers['MIME-Version'] = "1.0";
        //$this->headers['Content-type'] = "text/plain; charset=utf-8";
    }/*}}}*/

    /*********************[ API public functions ]**********************/

    public function defineText($text)
    {/*{{{*/
        $this->_message['text'] = $text;
    }/*}}}*/

    public function defineHtml($html)
    {/*{{{*/
        $this->_message['html'] = $html;
    }/*}}}*/

    public function setSubject($subject)
    {/*{{{*/
        $this->subject = $subject;
    }/*}}}*/

    public function setFrom($addr, $name="")
    {/*{{{*/
        $this->from = array($addr, $name);
    }/*}}}*/

    public function AddTo($addr, $name="")
    {/*{{{*/
        $this->mailTo[] = array($addr, $name);
    }/*}}}*/

    public function send()
    {/*{{{*/
        $newline = self::CRLF;

        $errno = $errstr = NULL;

        // Connect to the server (open socket)
        $this->_skt = fsockopen(
            $this->_server, $this->_port,
            $errno, $errstr, $this->_connectTimeout
        );

        if (empty($this->_skt)) {
            return false
                && error_log(
                    "Cannot open SMTP socket (error $errno: $errstr)"
                );
        }

        $this->log['connection'] = $this->getResponse();

        // Say hello to SMTP
        $this->log['hello']    = $this->sendCmd("EHLO {$this->_server}");

        // Request auth login
        $this->log['auth']     = $this->SendCMD("AUTH LOGIN");
        $this->log['username'] = $this->SendCMD(base64_encode($this->_username));
        $this->log['password'] = $this->SendCMD(base64_encode($this->_password));

        //Email from
        $this->log['mailfrom'] = $this->SendCMD("MAIL FROM:<{$this->from[0]}>");

        //Email to
        $cnt = 1;
        foreach (array_merge($this->mailTo, $this->mailCc) as $addr)
          $this->log['rcptto'.$cnt++] = $this->SendCMD("RCPT TO:<{$addr[0]}>");

        //The Email
        $this->log['data1'] = $this->SendCMD("DATA");

        //Construct headers
        if (!empty($this->contentType))
            $this->headers['Content-type'] = $this->contentType;
        $this->headers['From'] = $this->formatAddress($this->from);
        $this->headers['To'] = $this->formatAddressList($this->mailTo);

        if (!empty($this->mailCc))
            $this->headers['Cc'] = $this->FmtAddrList($this->mailCc);

        $this->headers['Subject'] = $this->subject;
        $this->headers['Date'] = date('r');

        $headers = '';
        foreach ($this->headers as $key => $val)
          $headers .= $key . ': ' . $val . self::CRLF;

        $this->log['data2'] = $this->SendCMD(
            "{$headers}$newline{$this->body}$newline."
        );

        // Say Bye to SMTP
        $this->log['quit']  = $this->SendCMD("QUIT");

        fclose($this->_skt);
    }/*}}}*/

    /********************[ Protected class members ]********************/

    protected function sendCmd($cmd)
    {/*{{{*/
        fputs($this->_skt, $cmd . self::CRLF);

        return $this->GetResponse();
    }/*}}}*/

    protected function formatAddress( &$addr )
    {/*{{{*/
        if ($addr[1] == "")
            return $addr[0];
        else
            return "\"{$addr[1]}\" <{$addr[0]}>";
    }/*}}}*/

    protected function formatAddressList( &$addrList )
    {/*{{{*/
        $list = "";
        foreach ($addrList as $addr) {
            if ($list) $list .= ", ".self::CRLF."\t";
            $list .= $this->formatAddress($addr);
        }
        return $list;
    }/*}}}*/

    protected function generateMultipart()
    {/*{{{*/

    }/*}}}*/

    protected function generateText()
    {/*{{{*/

    }/*}}}*/

    protected function generateHtml()
    {/*{{{*/
    }/*}}}*/

    protected function setHeaders( &$headers )
    {/*{{{*/
        foreach ($headers as $key => $value) {
            $this->headers .= "$key: $value" . self::CRLF;
        }
    }/*}}}*/

    private function getResponse()
    {/*{{{*/
        stream_set_timeout($this->_skt, $this->_responseTimeout);
        $response = '';
        while (($line = fgets($this->_skt, 515)) != false) {
            $response .= trim($line) . "\n";
            if (substr($line, 3, 1)==' ') break;
        }
        return trim($response);
    }/*}}}*/

}
