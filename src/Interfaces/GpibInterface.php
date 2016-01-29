<?php

namespace Zeratulmdq\Prologix\Interfaces;

Interface GpibInterface
{
	public function addCommand($command);
	public function clear();
	public function setAll($address);
	public function set($command, $address);
}