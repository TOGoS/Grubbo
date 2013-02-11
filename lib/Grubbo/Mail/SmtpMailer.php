<?php

require_once 'Grubbo/Mail/Mailer.php';

class Grubbo_Mail_SmtpMailer implements Grubbo_Mail_Mailer {
	protected $smtp;
	protected $connected;

	function __construct( Net_SMTP $smtp ) {
		$this->smtp = $smtp;
	}

	protected function getRecipientArray($o) {
		if( is_string($o) ) {
			return array($o);
		} else if( is_array($o) ) {
			$ss = array();
			foreach( $o as $v ) $ss[] = $this->getRecipientString($v);
			return $ss;
		} else if( $o instanceof Grubbo_User ) {
			return array($o->getName().' <'.$o->getEmailAddress().'>');
		} else {
			if( $o === null ) {
				$s = '(null)';
			} else if( $o === false ) {
				$s = '(false)';
			} else if( $o === true ) {
				$s = '(true)';
			} else if( $o == '' ) {
				$s = '(empty string)';
			} else {
				$s = print_r($o,true);
			}
			throw new Exception( "I don't know how to turn this thing into an e-mail address: $s" );
		}
	}

	protected function getEmailAddress( $addy ) {
		$str = $this->getRecipientString($addy);
		if( preg_match('/<([^>]+)>/',$str,$bif) ) {
			return $bif[1];
		}
	}

	protected function getRecipientString( $o ) {
		return implode(', ', $this->getRecipientArray($o));
	}

	protected function handlePearError( $e ) {
		if( is_object($e) ) {
			throw new Exception( $e->getMessage() );
		}
	}

	protected function connect() {
		if( !$this->connected ) {
			$this->handlePearError( $this->smtp->connect() );
			$this->handlePearError( $this->smtp->helo('grubbo.earthit.com') );
			$this->connected = true;
		}
		return $this->smtp;
	}

	function send( Grubbo_Mail_Message $message ) {
		$s = $this->connect();

		$fromStr = $this->getRecipientString( $message->getFrom() );
		$toStrs = $this->getRecipientArray( $message->getTo() );

		$body = "From: $fromStr\n"
			. "To: ".implode(', ',$toStrs) . "\n"
			. "Date: ".gmdate('D, d M Y H:i:s T') . "\n"
			. "Subject: ".$message->getSubject() . "\n"
			. "\n"
			. $message->getContent()->getData();

		$addr = $this->getEmailAddress( $fromStr );
		$this->handlePearError( $s->mailFrom( $addr ) );
		foreach( $toStrs as $tos ) {
			$addr = $this->getEmailAddress( $tos );
			$this->handlePearError( $s->rcptTo( $addr ) );
		}
		$this->handlePearError( $s->data( $body ) );
	}
}
