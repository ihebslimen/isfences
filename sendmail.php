<?php
// Import PHPMailer classes into the global namespace
// These must be at the top of your script, not inside a function

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

// Instantiation and passing `true` enables exceptions
function sendmail($addressDistinataire, $subject, $body ){
	$mail = new PHPMailer(true);
    //Server settings
    $mail->SMTPOptions = array(

        'ssl' => array(

            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
        )
	);

    //$mail->SMTPDebug = 4;
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'ihebplt1@gmail.com'; 
    $mail->Password   = 'fikuqojxuzfzbvlw';
    $mail->SMTPSecure = 'tls';
    $mail->Port       = '587';                                    
    $mail->setFrom('ihebplt1@gmail.com');
    $mail->addAddress($addressDistinataire); 
    $mail->isHTML(true);
   	$mail->Subject = $subject;
   	$mail->Body    = $body;
   	$mail->send();

	}


    ?>
