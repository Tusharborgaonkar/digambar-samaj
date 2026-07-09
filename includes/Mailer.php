<?php

class Mailer {
    private $host;
    private $port;
    private $username;
    private $password;

    public function __construct($host = 'smtp.hostinger.com', $port = 465, $username = 'help@digambarjainparichay.com', $password = 'King@0706') {
        $this->host = $host;
        $this->port = $port;
        $this->username = $username;
        $this->password = $password;
    }

    public function send($toEmail, $subject, $htmlBody) {
        $boundary = md5(time());
        $headers = "From: Digambar Samaj <" . $this->username . ">\r\n";
        $headers .= "Reply-To: " . $this->username . "\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: multipart/alternative; boundary=\"" . $boundary . "\"\r\n";

        $message = "--" . $boundary . "\r\n";
        $message .= "Content-Type: text/plain; charset=\"utf-8\"\r\n";
        $message .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
        $message .= strip_tags($htmlBody) . "\r\n\r\n";
        
        $message .= "--" . $boundary . "\r\n";
        $message .= "Content-Type: text/html; charset=\"utf-8\"\r\n";
        $message .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
        $message .= $htmlBody . "\r\n\r\n";
        
        $message .= "--" . $boundary . "--";

        // Connect to SMTP server
        $protocol = ($this->port == 465) ? 'ssl://' : '';
        $socket = fsockopen($protocol . $this->host, $this->port, $errno, $errstr, 15);
        if (!$socket) {
            return false;
        }

        $this->server_parse($socket, "220", __LINE__);

        fwrite($socket, "EHLO " . $this->host . "\r\n");
        $this->server_parse($socket, "250", __LINE__);

        fwrite($socket, "AUTH LOGIN\r\n");
        $this->server_parse($socket, "334", __LINE__);

        fwrite($socket, base64_encode($this->username) . "\r\n");
        $this->server_parse($socket, "334", __LINE__);

        fwrite($socket, base64_encode($this->password) . "\r\n");
        $this->server_parse($socket, "235", __LINE__);

        fwrite($socket, "MAIL FROM: <" . $this->username . ">\r\n");
        $this->server_parse($socket, "250", __LINE__);

        fwrite($socket, "RCPT TO: <" . $toEmail . ">\r\n");
        $this->server_parse($socket, "250", __LINE__);

        fwrite($socket, "DATA\r\n");
        $this->server_parse($socket, "354", __LINE__);

        // Send headers
        fwrite($socket, "Subject: " . $subject . "\r\n" . $headers . "\r\n" . $message . "\r\n.\r\n");
        $this->server_parse($socket, "250", __LINE__);

        fwrite($socket, "QUIT\r\n");
        fclose($socket);
        return true;
    }

    private function server_parse($socket, $response, $line = __LINE__) {
        $server_response = '';
        while (substr($server_response, 3, 1) != ' ') {
            if (!($server_response = fgets($socket, 256))) {
                return false;
            }
        }
        if (!(substr($server_response, 0, 3) == $response)) {
            return false;
        }
        return true;
    }
}
