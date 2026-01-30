<?php
require 'Exception.php';
require 'PHPMailer.php';
require 'SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;


function sendCallEmail(string $toEmail, string $toName): array {
    
    



    $smtpUser = 'filavirtualetesc@gmail.com';
    $smtpPass = 'hkilfzxendhkozmg'; 
    $fromEmail = 'filavirtualetesc@gmail.com';
    $fromName = 'Fila Virtual';

    if (!filter_var($toEmail, FILTER_VALIDATE_EMAIL)) {
        return ['success'=>false, 'message'=>'E-mail inválido: '.$toEmail];
    }

    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = $smtpUser;
        $mail->Password = $smtpPass;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        $mail->SMTPOptions = [
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            ]
        ];

        $mail->setFrom($fromEmail, $fromName);
        $mail->addAddress($toEmail, $toName);

        $mail->isHTML(true);
        $mail->Subject = 'Sua vez chegou na Sala de Jogos!';
        $mail->Body = '<div style="background:#181f2a;padding:28px 18px;border-radius:12px;color:#e6eef8;font-family:Poppins,sans-serif;max-width:480px;margin:auto;box-shadow:0 4px 18px #0002">
            <h2 style="color:#b30000;margin-top:0">Olá, ' . htmlspecialchars($toName) . '!</h2>
            <p style="font-size:1.1rem;margin:18px 0 8px 0">🎲 Chegou a sua vez na <strong>Sala de Jogos</strong>!</p>
            <p style="margin:8px 0 18px 0">Venha se divertir com nossos jogos e aproveite seu tempo. Compareça a sala LAB A!</p>
            <div style="background:#b30000;color:#fff;padding:10px 16px;border-radius:8px;display:inline-block;font-weight:600;margin-bottom:12px">Apresente este e-mail na entrada</div>
            <p style="font-size:.95rem;color:#9aa4b2;margin-top:18px">Se você não puder comparecer, responda este e-mail ou avise a equipe da sala.</p>
            <hr style="border:none;border-top:1px solid #222;margin:24px 0 8px 0">
            <div style="font-size:.9rem;color:#9aa4b2">Equipe ...</div>
        </div>';

        $mail->send();
        return ['success'=>true, 'message'=>'E-mail enviado com sucesso para '.$toEmail];
    } catch (Exception $e) {
        return ['success'=>false, 'message'=>'Erro ao enviar e-mail: '.$mail->ErrorInfo];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'])) {
    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $res = sendCallEmail($email, $nome);
    if ($res['success']) {
        echo $res['message'];
    } else {
        echo $res['message'];
    }
}

?>
