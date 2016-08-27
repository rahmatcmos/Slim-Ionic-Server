<?php

namespace App\Services;

use App\Models\User;
use Carbon\Carbon;

class Mailer
{
	protected $setting;
	protected $recipient = [];
	protected $attachment = [];
	protected $message = [];

	public function __construct($setting)
	{
		$this->setting = $setting;
	}

	public function addRecipient($user)
	{
		if(is_array($user))
		{
			foreach($user as $index => $value)
			{
				$this->recipient[] = [$value->email, $value->name];
			}
		}
		elseif(is_object($user))
		{
			$this->recipient[] = [$user->email, $user->name];
		}
		return $this;
	}

	public function addAttachment($attachmentFile, $attachmentName = 'Attachment')
	{
		if(is_array($attachmentFile))
		{
			foreach($attachmentFile as $index => $attachment)
			{
				if(is_array($attachment))
				{
					$name = isset($attachment[1]) ? $attachment[1] : 'Attachment';
					$this->attachment[] = [$attachment[0], $name];
				}
				$this->attachment[] = [$attachment, 'Attachment'];
			}
		}
		else
		{
			$this->attachment[] = [$attachmentFile, $attachmentName];
		}
		return $this;
	}

	public function addMessage($subject, $body, $altBody = '', $isHTML = true)
	{
		$this->message = [$subject, $body, $altBody , $isHTML];
		return $this;
	}

	public function init()
	{
		$mail = new \PHPMailer;

		if($this->setting['email']['protocol'] === 'smtp')
		{
			$mail->isSMTP();
			//$mail->SMTPDebug = 3;
		}
		elseif($this->setting['email']['protocol'] === 'sendmail')
		{
			$mail->isSendmail();
		}

		$mail->Host = $this->setting['email']['host'];
		$mail->SMTPAuth = $this->setting['email']['smtpauth'];
		$mail->Username = $this->setting['email']['username'];
		$mail->Password = $this->setting['email']['password'];

		if($this->setting['email']['smtpsecure'] === 'ssl' || $this->setting['email']['smtpsecure'] === 'tsl')
		{
			$mail->SMTPSecure = $this->setting['email']['smtpsecure'];
		}

		$mail->Port = $this->setting['email']['port'];

		$mail->setFrom($this->setting['email']['username'], $this->setting['config']['app_name']);
		$mail->addReplyTo($this->setting['email']['username'], 'ReplyTo');

		foreach($this->recipient as $recipient)
		{
			$mail->addAddress($recipient[0], $recipient[1]);
		}

		foreach($this->attachment as $attachment)
		{
			$mail->addAttachment($attachment[0], $attachment[1]);
		}

		$mail->Subject = $this->message[0];
		$mail->Body    = $this->message[1];
		$mail->AltBody = $this->message[2];
		$mail->isHTML($this->message[3]);

		if(!$mail->send()) {
			return $mail->ErrorInfo;
		} else {
			return true;
		}
	}
}
