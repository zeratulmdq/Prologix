<?php

namespace Zeratulmdq\Prologix;

class Socket
{
	protected $fp;
	protected $ip;
	protected $port;
	protected $timeout;

	public function __construct($ip, $port, $timeout = 3)
	{
		$this->ip = $ip;
		$this->port = $port;
		$this->timeout = $timeout;
	}

	public function open()
	{
		if(!($this->fp = fsockopen($this->ip, $this->port, $errno, $errstr, $this->timeout)))
			throw new \Exception("Cannot open socket");
			

	}

	public function write($command)
	{
		if($this->fp)
			fwrite($this->fp, $command);
	}

	public function close()
	{
		if($this->fp)
			fclose($this->fp);
	}
}

/*

SL9K
OPEN 102, 201, 502
CLOSE 401, 402


Banda C
OPEN 300
CLOSE 302, 502

 */