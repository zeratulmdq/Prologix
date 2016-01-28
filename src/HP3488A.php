<?php

namespace Zeratulmdq\Prologix;

use Zeratulmdq\Prologix\Interfaces\GpibInterface;

class HP3488A
{
	/**
	 * Device GPIB address.
	 * 
	 * @var int
	 */
	protected $address;

	/**
	 * Gpib accessor instance.
	 * 
	 * @var \Zeratulmdq\Prologix\GpibInterface
	 */
	protected $gpib;

	/**
	 * List of ports to open
	 * 
	 * @var array
	 */
	protected $open = [];
	
	/**
	 * List of ports to close
	 * 
	 * @var array
	 */
	protected $close = [];

	/**
     * Minimum allowed gpib address.
     *
     * @var int
     */
	const MIN_ADDRESS = 0;

	/**
     * Maximum allowed gpib address.
     *
     * @var int
     */
	const MAX_ADDRESS = 30;

	public function __construct($address, GpibInterface $gpib)
	{
		$this->address($address);
		$this->gpib = $gpib;
	}

	/**
	 * Set the GPIB address
	 * 
	 * @param  int $address
	 * @return $this
	 *
	 * @throws \RangeException
	 */
	public function address($address)
	{
		if(!$this->isInsideRange($address, self::MIN_ADDRESS, self::MAX_ADDRESS))
			throw new \RangeException("GPIB address out of range.");

		$this->address = $address;

		return $this;
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
		return $value >= $start && $value <= $end;
	}

	/**
	 * Transform $input into a valid array
	 * 
	 * @param  mixed $input
	 * @return array
	 */
	public function sanitize($input)
	{
		$ports = is_array($input) ? $input : [$input];

		return array_filter($ports, function($port)
		{
			return is_int($port);
		});
	}

	/**
	 * Add ports to open on the device
	 * 
	 * @param  int|array $port
	 * @return $this
	 *
	 * @throws \InvalidArgumentException
	 */
	public function open($ports)
	{
		$this->open = $this->sanitize($ports);

		if(!count($this->open))
			throw new \InvalidArgumentException("There must be at least one port to open");
			
		$this->gpib->addCommand('OPEN'.implode(',', $this->open));

		return $this;
	}

	/**
	 * Add ports to close on the device
	 * 
	 * @param  int|array $port
	 * @return $this
	 *
	 * @throws \InvalidArgumentException
	 */
	public function close($ports)
	{
		$this->close = $this->sanitize($ports);

		if(!count($this->close))
			throw new \InvalidArgumentException("There must be at least one port to close");
			
		$this->gpib->addCommand('CLOSE'.implode(',', $this->close));

		return $this;
	}

	/**
	 * Write the parameters to the device
	 * 
	 * @return void
	 */
	public function set()
	{
		$this->gpib->set($this->address);
	}

}