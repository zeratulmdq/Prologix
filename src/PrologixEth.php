<?php

namespace Zeratulmdq\Prologix;

use Zeratulmdq\Prologix\Interfaces\GpibInterface;

class PrologixEth implements GpibInterface
{
    /**
     * Prologix ETH IP address
     * 
     * @var string
     */
    protected $ip;

    /**
     * Prologix ETH Port
     * 
     * @var int
     */
    protected $port;

    /**
     * Socket instance
     * 
     * @var \Zeratulmdq\Prologix\Socket
     */
    protected $socket;

    /**
     * List of passive commands
     * 
     * @var array
     */
    protected $commands = [];

    /**
     * End of line char/s
     *
     * @var mixed
     */
    const EOL = chr(13).chr(10);

    /**
     * Create a new PrologixEth instance
     * 
     * @param string  $ip
     * @param integer $port
     */
    public function __construct($ip, $port = 1234)
    {
        $this->ip = $ip;
        $this->port = $port;
        $this->socket = new Socket($this->ip, $this->port);
    }

    /**
     * Prepare the query to send to the device
     * 
     * @param  int $address
     * @return string
     */
    private function query($address)
    {
        $this->commands = array_merge(['++addr'.$address], $this->commands);
        $this->commands[] = '++loc';
        
        return implode(self::EOL, $this->commands).self::EOL;
    }

    /**
     * Add a command to the list
     * 
     * @param string $command
     * @return $this
     */
    public function addCommand($command)
    {
        $this->commands[] = $command;

        return $this;
    }

    /**
     * Send the query to the device
     * 
     * @param int $address
     * @return $this
     */
    public function set($address)
    {
        $this->socket->open();
        $this->socket->write($this->query($address));
        $this->socket->close();

        return $this->clear();
    }

    /**
     * Clear stored commands
     * 
     * @return $this
     */
    public function clear()
    {
        $this->commands = [];

        return $this;
    }
    
}