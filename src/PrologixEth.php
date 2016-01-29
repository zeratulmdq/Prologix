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
     * End of line char/s
     *
     * @var mixed
     */
    protected $eol;

    /**
     * List of passive commands
     * 
     * @var array
     */
    protected $commands = [];

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
        $this->eol = chr(13).chr(10);
        $this->socket = new Socket($this->ip, $this->port);
    }

    /**
     * Generate query to send to the device
     * 
     * @param  string $command
     * @param  int $address
     * @return string
     */
    private function generateQuery($address, $command)
    {
        return '++addr'.$address.$this->eol.$command.$this->eol;
    }

    /**
     * Generate command to send to the device with the stored ones
     * 
     * @param  int $address
     * @return string
     */
    private function getStoredCommands()
    {
        return implode($this->eol, $this->commands).$this->eol;
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
     * Clear stored commands
     * 
     * @return $this
     */
    public function clear()
    {
        $this->commands = [];

        return $this;
    }

    /**
     * Send query to the device using stored commands
     * 
     * @param int $address
     * @return $this
     */
    public function setAll($address)
    {
        $this->set($address, $this->getStoredCommands());
        
        return $this->clear();
    }

    /**
     * Send the query to the device
     *
     * @param string $command
     * @param int $address
     * @return $this
     */
    public function set($address, $command)
    {
        $this->socket->write($this->generateQuery($address, $command));

        return $this;
    }

    public function auto($address, $value)
    {
        if(in_array($value, range(0,1), true))
            throw new \UnexpectedValueException("Auto value must be 0..1");
            
        $this->set('++auto'.$value, $address);
    }

    public function clr($address)
    {
        $this->set('++clr', $address);
    }
    
    public function eoi($address, $value)
    {
        if(in_array($value, range(0,1), true))
            throw new \UnexpectedValueException("Eoi value must be 0..1");
            
        $this->set('++eoi'.$value, $address);
    }

    public function eos($address, $value)
    {
        if(in_array($value, range(0,3), true))
            throw new \UnexpectedValueException("Eos value must be 0..3");
            
        $this->set('++eos'.$value, $address);
    }

    public function eot_enable($address, $value)
    {
        if(in_array($value, range(0,1), true))
            throw new \UnexpectedValueException("Eot_enable value must be 0..1");
            
        $this->set('++eot_enable'.$value, $address);
    }

    public function eot_char($address, $value)
    {
        if(in_array($value, range(0,255), true))
            throw new \UnexpectedValueException("Eot_enable value must be 0..255");
            
        $this->set('++eot_char'.$value, $address);
    }

    public function ifc($address)
    {
        $this->set('++ifc', $address);
    }

    public function llo($address)
    {
        $this->set('++llo', $address);
    }

    public function loc($address)
    {
        $this->set('++loc', $address);
    }

    public function lon($address, $value)
    {
        if(in_array($value, range(0,1), true))
            throw new \UnexpectedValueException("lon value must be 0..1");
            
        $this->set('++lon'.$value, $address);
    }

    public function mode($address, $value)
    {
        if(in_array($value, range(0,1), true))
            throw new \UnexpectedValueException("mode value must be 0..1");
            
        $this->set('++mode'.$value, $address);
    }

    public function read($address)
    {
        //implement
    }

    public function read_tmo_ms($address, $value)
    {
        if(in_array($value, range(0,3000), true))
            throw new \UnexpectedValueException("read_tmo_ms value must be 1..3000");
            
        $this->set('++read_tmo_ms'.$value, $address);
    }

    public function rst($address)
    {
        $this->set('++rst', $address);
    }

    public function savecfg($address, $value)
    {
        if(in_array($value, range(0,1), true))
            throw new \UnexpectedValueException("savecfg value must be 0..1");
            
        $this->set('++savecfg'.$value, $address);
    }

    public function spoll($address)
    {
        //implement
    }

    public function srq()
    {
        //implement
    }

    public function status()
    {
        //implement
    }

    public function trg()
    {
        //implement
    }

    public function ver()
    {
        //implement
    }

    public function help()
    {
        //implement
    }
}