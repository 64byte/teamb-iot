<?php

namespace Story;

use PHPMailer\PHPMailer\PHPMailer;

class SomiMailer {
    private static $mailHeader = '<div class="grid-container" style="display: grid; grid-template-areas: \'header\'\'main\'\'footer\'; grid-gap: 1px; padding: 10px; width: 60%; margin: auto;">
                  <div class="itemHeader" style="text-align: center; background-color: rgb(218, 222, 215); padding: 20px 0; font-size: 20px; grid-area: header;">
                    <img src="http://teamb-iot.calit2.net/icon/somi.png" width="50px" height="50px"/>
                    <h3>SOMI</h3>
                  </div>
                  <div class="itemMain" style="text-align: center; font-size: 15px; padding: 25px; grid-area: main;">';
    private static $mailFooter = '</div>  
                  <div class="itemFooter" style="text-align: center; padding: 15px 0; font-size: 12px; grid-area: footer;">
                    <hr style="margin-bottom: 25px;"/>
                    <p>Copyright &copy; 2018 teamb-iot.calit2.net </p>
                  </div>
                </div>';

    private $mailer;

    public function __construct($adminEmail = '', $adminPassword = '', $sendName = 'Somi')
    {
        $this->mailer = new PHPMailer(true);
        $this->mailer->isSMTP();
//        $this->mailer->SMTPDebug = 2;
        $this->mailer->Host = 'smtp.gmail.com';  // Specify main and backup SMTP servers
        $this->mailer->SMTPAuth = true;                               // Enable SMTP authentication
        $this->mailer->Username = $adminEmail;                // SMTP username
        $this->mailer->Password = $adminPassword;                   // SMTP password
        $this->mailer->SMTPSecure = 'tls';                            // Enable TLS encryption, `ssl` also accepted
        $this->mailer->Port = 587;                                    // TCP port to connect to
        $this->mailer->SMTPKeepAlive = true;

        $this->mailer->setFrom($adminEmail, $sendName);
    }

    public function sendMail($toEmail, $subject, $msg, $altBody = '', $isHtml = true)
    {
        $this->mailer->addAddress($toEmail);     // Add a recipient
        $this->mailer->isHTML($isHtml);
        $this->mailer->Subject = $subject;
        $this->mailer->Body    = SomiMailer::$mailHeader.$msg.SomiMailer::$mailFooter;
        $this->mailer->AltBody = $altBody;
        $this->mailer->send();
    }
}