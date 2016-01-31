<?php

namespace Zeratulmdq\Prologix\Interfaces;

Interface GpibInterface
{
	public function addCommand($command);
	public function clearCommands();
	public function sendAll($address);
	public function send($command, $address);
	public function receive($command);
}