<?php

namespace Zeratulmdq\Prologix;

use Zeratulmdq\Prologix\Interfaces\GpibInterface;
use Zeratulmdq\Prologix\Interfaces\Prologix;

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
     * Prepare the query to send to the device
     * 
     * @param  string $command
     * @return string
     */
    private function prepareCommand($command, $address = null)
    {
        $return = $command.$this->eol;

        if($address !== null && in_array($address, range(0, 30), true))
            $return = $this->prepareCommand('++addr'.$address).$return;

        return $return;
    }

    /**
     * Generate command to send to the device using the stored ones
     * 
     * @param  int $address
     * @return string
     */
    private function prepareStoredCommands($address = null)
    {
        return $this->prepareCommand(implode($this->eol, $this->commands), $address);   
    }

    /**
     * Check if value is inside range
     * 
     * @param  mixed  $value
     * @param  mixed  $start
     * @param  mixed  $end
     * @return boolean
     */
    private function isInsideRange($value, $start, $end)
    {
        return in_array($value, range($start, $end), true);
    }

    /**
     * Check for valid GPIB address values
     * 
     * @param  mixed $address
     * @return boolean
     */
    private function checkAddress($address)
    {
        return $address === null || $this->isInsideRange($address, 0, 30);
    }

    /**
     * Standarize options array
     * 
     * @param  array  $options
     * @return array
     */
    private function sanitize(array $options)
    {
        $options['value'] = isset($options['value']) ? $options['value'] : null;
        $options['address'] = isset($options['address']) ? $options['address'] : null;

        return $options;
    }

    /**
     * Add a command to the stored list
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
    public function clearCommands()
    {
        $this->commands = [];

        return $this;
    }

    /**
     * Send command to the device
     * 
     * @param  string $command
     * @return mixed
     */
    public function send($command, $address = null)
    {
        $this->socket->write($this->prepareCommand($command, $address));

        return $this;
    }

    /**
     * Send stored commands to the device
     * 
     * @return mixed
     */
    public function sendAll($address = null)
    {
        $this->socket->write($this->prepareStoredCommands($address));

        return $this->clearCommands();
    }

    /**
     * Read an specific config value
     * 
     * @param  string $command
     * @return mixed
     */
    public function receive($command, $address = null)
    {
        return $this->socket->readLine($this->prepareCommand($command, $address));
    }

    /**
     * Generic Prologix specific query
     * 
     * @param  array  $options
     * @param  string $command
     * @param  mixed $start
     * @param  mixed $end
     * @return mixed
     */
    private function genericQuery(array $options, $command, $start = null, $end = null)
    {
        $options = $this->sanitize($options);

        if(!$this->checkAddress($options['address']))
            throw new \UnexpectedValueException("Address value must be 0..30");

        if($options['value'] === null)
            return (int) $this->receive($command, $options['address']);

        if(!in_array($options['value'], range($start, $end), true))
            throw new \UnexpectedValueException("Value must be $start..$end");

        return $this->send($command.$options['value'], $options['address']);
    }

    /**
     * Set or get address parameter
     * 
     * @param  mixed $value
     * @return mixed
     */
    public function address(array $options = [])
    {
        return $this->genericQuery($options, '++addr', 0, 30);
    }

    /**
     * Set or get auto parameter
     * 
     * @param  mixed $value
     * @return mixed
     */
    public function auto(array $options = [])
    {
        return $this->genericQuery($options, '++auto', 0, 30);
    }

    /**
     * Send the SDR message to the current addressed device
     * 
     * @param  array  $options
     * @return mixed
     */
    public function clr(array $options = [])
    {
        return $this->genericQuery($options, '++clr');
    }
    
    /**
     * Enable or disable EOI signal
     *  
     * @param  array  $options 
     * @return mixed
     */
    public function eoi(array $options = [])
    {
        return $this->genericQuery($options, '++clr', 0, 1);
    }

    /**
     * Set or get termination characters
     *  
     * @param  array  $options 
     * @return mixed
     */
    public function eos(array $options = [])
    {
        return $this->genericQuery($options, '++clr', 0, 1);
    }

    /**
     * Set or get eot_enable parameter
     *  
     * @param  array  $options 
     * @return mixed
     */
    public function eot_enable(array $options = [])
    {
        return $this->genericQuery($options, '++eot_enable', 0, 1);
    }

    /**
     * Set or get eot_char parameter
     *  
     * @param  array  $options 
     * @return mixed
     */
    public function eot_char(array $options = [])
    {
        return $this->genericQuery($options, '++eot_char', 0, 255);
    }

    /**
     * Send the IFC signal
     * 
     * @param  array  $options
     * @return mixed
     */
    public function ifc(array $options = [])
    {
        return $this->genericQuery($options, '++ifc');
    }

    /**
     * Disable front panel operation of the currently addressed device
     * 
     * @param  array  $options
     * @return mixed
     */
    public function llo(array $options = [])
    {
        return $this->genericQuery($options, '++llo');
    }

    /**
     * Enable front panel operation of the currently addressed device
     * 
     * @param  array  $options
     * @return mixed
     */
    public function loc(array $options = [])
    {
        return $this->genericQuery($options, '++loc');
    }

    /**
     * Set or get lon parameter
     *  
     * @param  array  $options 
     * @return mixed
     */
    public function lon(array $options = [])
    {
        return $this->genericQuery($options, '++lon', 0, 1);
    }

    /**
     * Set or get mode parameter
     *  
     * @param  array  $options 
     * @return mixed
     */
    public function mode(array $options = [])
    {
        return $this->genericQuery($options, '++mode', 0, 1);
    }

    public function read($address)
    {
        //implement
    }

    /**
     * Set or get read_tmo_ms parameter
     *  
     * @param  array  $options 
     * @return mixed
     */
    public function read_tmo_ms(array $options = [])
    {
        return $this->genericQuery($options, '++read_tmo_ms', 0, 3000);
    }

    /**
     * Reset Prologix GPIB interface
     * 
     * @param  array  $options
     * @return mixed
     */
    public function rst(array $options = [])
    {
        return $this->genericQuery($options, '++rst');
    }

    /**
     * Set or get savecfg parameter
     *  
     * @param  array  $options 
     * @return mixed
     */
    public function savecfg(array $options = [])
    {
        return $this->genericQuery($options, '++savecfg', 0, 1);
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