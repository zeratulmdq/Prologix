<?php

namespace Zeratulmdq\Prologix;

use Zeratulmdq\Prologix\Interfaces\GpibInterface;

class HP8593E
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

	/**
     * Minimum allowed center frecuency.
     *
     * @var int
     */
	const MIN_CENTER_FRECUENCY = 100;

	/**
     * Maximum allowed center frecuency.
     *
     * @var int
     */
	const MAX_CENTER_FRECUENCY = 23800000000;

	/**
     * Minimum allowed span.
     *
     * @var int
     */
	const MIN_SPAN = 100;

	/**
     * Maximum allowed span.
     *
     * @var int
     */
	const MAX_SPAN = 238000000000;

	/**
     * Minimum allowed reference level.
     *
     * @var int
     */
	const MIN_REFERENCE_LEVEL = -110;

	/**
     * Maximum allowed reference level.
     *
     * @var int
     */
	const MAX_REFERENCE_LEVEL = 30;

	/**
	 * Create a new HP8593E instance
	 * 
	 * @param int $address
	 * @param GpibInterface $gpib
	 */
	public function __construct($address, GpibInterface $gpib)
	{
		$this->gpib = $gpib;
		$this->address($address);
	}

	/**
	 * Set the device GPIB address
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
	 * Converts value according to specified unit
	 * 
	 * @param  mixed $value
	 * @param  string $unit
	 * @return mixed
	 *
	 * @throws \InvalidArgumentException
	 */
	private function convert($value, $unit)
	{
		if(!is_numeric($value))
			throw new \InvalidArgumentException("Value must be a number");
			
		return $value * $this->multiplier($unit);
	}

	/**
	 * Get the multiplier for the specified unit
	 * 
	 * @param  string $unit
	 * @return int
	 *
	 * @throws \UnexpectedValueException
	 */
	private function multiplier($unit)
	{
		switch (strtolower($unit)) {
			case 'hz':
				return 1;
			case 'khz':
				return 1000;
			case 'mhz':
				return 1000000;
			case 'ghz':
				return 1000000000;

			default:
				throw new \UnexpectedValueException("Non existing unit");
		}
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
	 * Set span parameter
	 * 
	 * @param  mixed $value
	 * @param  string $unit
	 * @return $this
	 *
	 * @throws \RangeException
	 */
	public function span($value, $unit = 'hz')
	{
		$converted = $this->convert($value, $unit);
		
		if(!$this->isInsideRange($converted, self::MIN_SPAN, self::MAX_SPAN))
			throw new \RangeException("Span out of range");

		$this->gpib->addCommand('SP'.$converted);

		return $this;	
	}

	/**
	 * Set center frecuency parameter
	 * 
	 * @param  mixed $value
	 * @param  string $unit
	 * @return $this
	 *
	 * @throws \RangeException
	 */
	public function centerFrecuency($value, $unit = 'hz')
	{
		$converted = $this->convert($value, $unit);

		if(!$this->isInsideRange($converted, self::MIN_CENTER_FRECUENCY, self::MAX_CENTER_FRECUENCY))
			throw new \RangeException("Center frecuency out of range");

		$this->gpib->addCommand('CF'.$converted);

		return $this;
	}

	/**
	 * Set reference level parameter
	 * 
	 * @param  mixed $value
	 * @param  string $unit
	 * @return $this
	 *
	 * @throws \RangeException
	 */
	public function referenceLevel($value)
	{
		if(!$this->isInsideRange($value, self::MIN_REFERENCE_LEVEL, self::MAX_REFERENCE_LEVEL))
			throw new \RangeException("Reference level out of range");
			
		$this->gpib->addCommand('RL'.$value);

		return $this;
	}

	/**
	 * Write commands to the device
	 * 
	 * @return void
	 */
	public function set()
	{
		$this->gpib->set($this->address);
	}

}