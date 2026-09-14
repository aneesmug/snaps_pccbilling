<?php
use Illuminate\Database\Eloquent\Model;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mime\Email as SymfonyEmail;
use Symfony\Component\Mime\Address as SymfonyAddress;

class Email extends Model
{
    protected $table = 'sys_email_logs';
    public $timestamps = false;

    public static function _log($userid, $email, $subject, $message, $iid = '0')
    {
        $date = date('Y-m-d H:i:s');
        $d = ORM::for_table('sys_email_logs')->create();
        $d->userid = $userid;
        $d->sender = '';
        $d->email = $email;
        $d->subject = $subject;
        $d->message = $message;
        $d->date = $date;
        $d->iid = $iid;
        $d->save();
        return $d->id();
    }

    public static function sendEmail(
        $config,
        $_L,
        $name,
        $to,
        $subject,
        $message,
        $userid = '0',
        $iid = '0',
        $cc = '',
        $bcc = '',
        $attachment_path = '',
        $attachment_file = ''
    ) {
        $message = str_replace(
            BASE_URL . 'settings/email-templates/',
            '',
            $message
        );

        if (APP_STAGE == 'Demo') {
            return true;
        }

        if(empty($to)){
            return false;
        }

        $email_log = new self();
        $email_log->userid = $userid;
        $email_log->sender = '';
        $email_log->email = $to;
        $email_log->subject = $subject;
        $email_log->message = $message;
        $email_log->date = date('Y-m-d H:i:s');
        $email_log->iid = $iid;
        $email_log->save();

        $email_config = EmailConfig::first();

        $method = $email_config->method;

        switch ($method) {
            case 'smtp':
//                try{
//                    $transport = (new Swift_SmtpTransport(
//                        $email_config->host,
//                        $email_config->port,
//                        $email_config->secure
//                    ))
//                        ->setUsername($email_config->username)
//                        ->setPassword($email_config->password)
//                        ->setStreamOptions([
//                            'ssl' => [
//                                'allow_self_signed' => true,
//                                'verify_peer' => false,
//                            ],
//                        ]);
//                } catch (\Exception $e) {
//                    return false;
//                }

                $dsn = sprintf(
                    'smtp://%s:%s@%s:%d?encryption=%s',
                    urlencode($email_config->username),
                    urlencode($email_config->password),
                    $email_config->host,
                    $email_config->port,
                    $email_config->secure
                );
                $transport = Transport::fromDsn($dsn);

                break;
            case 'sparkpost':
                return;
                break;
            case 'mailgun':
                break;
            default:
                $transport = Transport::fromDsn('sendmail://default');
                break;
        }

        $mailer = new Mailer($transport);

//        $message = (new Swift_Message($subject))
//            ->setFrom([
//                $config['sysEmail'] => $config['CompanyName'],
//            ])
//            ->setTo([$to => $name])
//            ->setBody($message, 'text/html');

        $email_message = (new SymfonyEmail())
            ->from(new SymfonyAddress($config['sysEmail'], $config['CompanyName']))
            ->to(new SymfonyAddress($to, $name))
            ->subject($subject)
            ->html($message);

        if (!empty($cc)) {
            $email_message->cc($cc);
        }

        if (!empty($bcc)) {
            $email_message->bcc($bcc);
        }

        if ($attachment_path != '') {
           // $message->attach(Swift_Attachment::fromPath($attachment_path));
            $email_message->attachFromPath($attachment_path);
        }

        $mailer->send($email_message);
    }

    public static function send_client_welcome_email(
        $data,
        $send_password = false
    ) {

        $e = ORM::for_table('sys_email_templates')
            ->where('tplname', 'Client:Client Signup Email')
            ->first();

        if (!isset($data['account']) || !isset($data['email'])) {
            return false;
        }

        if ($e) {
            global $config;

            $subject = new Template($e['subject']);
            $subject->set('business_name', $config['CompanyName']);
            $subj = $subject->output();

            $message = new Template($e['message']);
            $message->set('client_name', $data['account']);
            $message->set('client_email', $data['email']);
            $message->set('client_password', $data['password']);
            $message->set('business_name', $config['CompanyName']);
            $message->set('client_login_url', U . 'client/login/');
            $message_o = $message->output();


            $email_settings = EmailConfig::first();

            if($email_settings)
            {
                $method = $email_settings->method;

                // Create the transport based on the email settings
                $dsn = sprintf(
                    '%s://%s:%s@%s:%d',
                    $email_settings->secure === 'ssl' ? 'smtps' : 'smtp',
                    $email_settings->username,
                    $email_settings->password,
                    $email_settings->host,
                    $email_settings->port
                );

                $transport = Transport::fromDsn($dsn);

                $mailer = new Mailer($transport);

                $email = (new SymfonyEmail())
                    ->from(new SymfonyAddress($config['sysEmail'], $config['CompanyName']))
                    ->to(new SymfonyAddress($data['email'], $data['account']))
                    ->subject($subj)
                    ->html($message_o);

                try {
                    // Send the email
                    $mailer->send($email);
                } catch (TransportExceptionInterface $e) {
                    addActivityLog('Error sending welcome email: ' . $e->getMessage());
                }

            }

        }

        return false;

    }
}
