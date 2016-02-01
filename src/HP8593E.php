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
     * Minimum allowed attenuation level.
     *
     * @var int
     */
	const MIN_ATTENUATION_LEVEL = 0;

	/**
     * Maximum allowed attenuation level.
     *
     * @var int
     */
	const MAX_ATTENUATION_LEVEL = 70;

	/**
     * Minimum allowed bandwidth.
     *
     * @var int
     */
	const MIN_BANDWIDTH = 1;

	/**
     * Maximum allowed bandwidth.
     *
     * @var int
     */
	const MAX_BANDWIDTH = 2000000;

	/**
     * Minimum allowed frecuency.
     *
     * @var int
     */
	const MIN_FRECUENCY = 100;

	/**
     * Maximum allowed frecuency.
     *
     * @var int
     */
	const MAX_FRECUENCY = 23800000000;

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
     * Minimum allowed scale.
     *
     * @var int
     */
	const MIN_SCALE = 1;

	/**
     * Maximum allowed scale.
     *
     * @var int
     */
	const MAX_SCALE = 10;

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
     * Minimum allowed sweep time.
     *
     * @var int
     */
	const MIN_SWEEP_TIME = 20;

	/**
     * Maximum allowed sweep time.
     *
     * @var int
     */
	const MAX_SWEEP_TIME = 100000;

	/**
     * Minimum allowed video average.
     *
     * @var int
     */
	const MIN_VIDEO_AVG = 100;

	/**
     * Maximum allowed video average.
     *
     * @var int
     */
	const MAX_VIDEO_AVG = 238000000000;

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
     * Standarize options array
     * 
     * @param  array  $options
     * @return array
     */
    private function sanitize(array $options, $type)
	{
		$options['unit'] = isset($options['unit']) ? $options['unit'] : $this->defaultUnit($type);
		$options['store'] = isset($options['store']) ? $options['store'] : true;
		$options['address'] = isset($options['address']) ? $options['address'] : $this->address;

		return $options;
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
	private function convert($value, $unit, $type)
	{
		if(!is_numeric($value))
			throw new \InvalidArgumentException("Value must be a number");
			
		return $value * $this->multiplier($unit, $type);
	}

	private function defaultUnit($type)
	{
		switch(strtolower($type))
		{
			case 'frecuency':
				return 'hz';
			case 'time':
				return 's';
			case 'amplitude':
				return 'db';
			case null:
				return null;
			default:
				throw new \UnexpectedValueException("Non existing type");
		}
	}

	/**
	 * Get the multiplier for the specified frecuency unit
	 * 
	 * @param  string $unit
	 * @return int
	 *
	 * @throws \UnexpectedValueException
	 */
	private function frecuencyMultiplier($unit)
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
	 * Get the multiplier for the specified time unit
	 * 
	 * @param  string $unit
	 * @return int
	 *
	 * @throws \UnexpectedValueException
	 */
	private function timeMultiplier($unit)
	{
		switch (strtolower($unit)) {
			case 'ms':
				return 0.001;
			case 's':
				return 1;

			default:
				throw new \UnexpectedValueException("Non existing unit");
		}
	}

	/**
	 * Get the multiplier for the specified level unit
	 * 
	 * @param  string $unit
	 * @return int
	 *
	 * @throws \UnexpectedValueException
	 */
	private function levelMultiplier($unit)
	{
		switch (strtolower($unit)) {
			case 'db':
				return 1;
			case 'dbm':
				return 1;

			default:
				throw new \UnexpectedValueException("Non existing unit");
		}
	}

	/**
	 * Get the multiplier for the specified unit/type
	 * 
	 * @param  string $unit
	 * @param  mixed $type
	 * @return int
	 *
	 * @throws \UnexpectedValueException
	 */
	private function multiplier($unit, $type)
	{
		if($type === null)
			return 1;
		
		switch(strtolower($type))
		{
			case 'frecuency':
				return $this->frecuencyMultiplier($unit);
			case 'time':
				return $this->timeMultiplier($unit);
			case 'amplitude':
				return $this->levelMultiplier($unit);
			default:
				throw new \UnexpectedValueException("Non existing type");
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
		return $value >= $start && $value <=$end;
	}

	/**
	 * Set all stored commands
	 *
	 * @return void
	 */
	public function set()
	{
		$this->gpib->sendAll();
	}

	/**
	 * Generic query to send spectrum specific commands
	 * 
	 * @param  string $command
	 * @param  mixed $value  
	 * @param  array  $options
	 * @param  int $start  
	 * @param  int $end    
	 * @return $this
	 *
	 * @throws \RangeExeption
	 */
	private function genericQuery($command, $value, $type, array $options, $start, $end)
	{
		$options = $this->sanitize($options, $type);
		$converted = $this->convert($value, $options['unit'], $type);
		
		if(!$this->isInsideRange($converted, $start, $end))
			throw new \RangeException("Out of range");

		if($options['store'] === true)
			$this->gpib->addCommand($command.$converted);	
		else
			$this->gpib->send($command.$converted, $options['address']);

		return $this;	
	}

	/**
	 * Generic query for spectrum specific commands
	 * 
	 * @param  string $command
	 * @param  string $value  
	 * @param  array  $options
	 * @return $this
	 */
	private function genericCommand($command, $value, array $options)
	{
		$options = $this->sanitize($options, null);
		
		if($options['store'] === true)
			$this->gpib->addCommand($command.$value);	
		else
			$this->gpib->send($command.$value, $options['address']);

		return $this;	
	}

	/**
	 * Set active marker
	 * 
	 * @param  mixed $value
	 * @param  string $unit
	 * @return $this
	 */
	public function activeMarker($value, $options = [])
	{
		return $this->genericQuery('MKACT', $value, $options, 1, 4);
	}

	/**
	 * Set attenuation level
	 * 
	 * @param  mixed $value
	 * @param  string $unit
	 * @return $this
	 */
	public function attenuation($value, $options = [])
	{
		return $this->genericQuery('AT', $value, 'amplitude', $options, self::MIN_ATTENUATION_LEVEL, self::MAX_ATTENUATION_LEVEL);
	}

	/**
	 * Set center frecuency
	 * 
	 * @param  mixed $value
	 * @param  string $unit
	 * @return $this
	 */
	public function centerFrecuency($value, $options = [])
	{
		return $this->genericQuery('CF', $value, 'frecuency', $options, self::MIN_FRECUENCY, self::MAX_FRECUENCY);
	}

	/**
	 * Set display line on/off
	 * 
	 * @param  mixed $value  
	 * @param  array  $options
	 * @return $this         
	 */
	public function displayLine($value, $options = [])
	{
		if($value === 'off' || $value === 'on')
			return $this->genericCommand('DL', $value, $options);

		return $this->genericQuery('DL', $value, 'amplitude', $options, self::MIN_REFERENCE_LEVEL, self::MAX_REFERENCE_LEVEL);
	}

	/**
	 * Set the span equal to full span
	 * 
	 * @param  array  $options 
	 * @return $this
	 */
	public function fullSpan($options = [])
	{
		return $this->genericCommand('FS', '', $options);
	}

	/**
	 * Set the span to the previous setting
	 * 
	 * @param  array  $options
	 * @return $this
	 */
	public function lastSpan($options = [])
	{
		return $this->genericCommand('LSPAN', '', $options);
	}

	/**
	 * Set active marker amplitude
	 * 
	 * @param  mixed $value
	 * @param  string $unit
	 * @return $this
	 */
	public function markerAmplitude($value, $options = [])
	{
		return $this->genericQuery('MKA', $value, 'amplitude', $options, self::MIN_REFERENCE_LEVEL, self::MAX_REFERENCE_LEVEL);
	}
	
	/**
	 * Set active marker frecuency
	 * 
	 * @param  mixed $value
	 * @param  string $unit
	 * @return $this
	 */
	public function markerFrecuency($value, $options = [])
	{
		return $this->genericQuery('MKF', $value, 'frecuency', $options, self::MIN_FRECUENCY, self::MAX_FRECUENCY);
	}

	/**
	 * Turn off the active/all marker/s
	 * 
	 * @param  mixed $value
	 * @param  string $unit
	 * @return $this
	 */
	public function markerOff($value, $options = [])
	{
		if(strtolower($value) == 'all')
			return $this->genericCommand('MKOFF ', $value, $options);

		return $this->activeMarker($value, $options)->genericCommand('MKOFF', '', $options);
	}

	/**
	 * Set center frecuency equals to active marker frecuency
	 * 
	 * @param  mixed $value
	 * @param  string $unit
	 * @return $this
	 */
	public function markerToCenterFrecuency($options = [])
	{
		return $this->genericCommand('MKCF', '', $options);
	}

	/**
	 * Move the marker to the minimum signal detected
	 * 
	 * @param  mixed $value
	 * @param  string $unit
	 * @return $this
	 */
	public function markerToMinimumSignal($options = [])
	{
		return $this->genericCommand('MKMIN', '', $options);
	}
	/**
	 * Set reference level
	 * 
	 * @param  mixed $value
	 * @param  string $unit
	 * @return $this
	 */
	public function referenceLevel($value, $options = [])
	{
		return $this->genericQuery('RL', $value, 'amplitude', $options, self::MIN_REFERENCE_LEVEL, self::MAX_REFERENCE_LEVEL);
	}

	/**
	 * Set resolution bandwidth
	 * 
	 * @param  mixed $value
	 * @param  string $unit
	 * @return $this
	 */
	public function resolutionBandwidth($value, $options = [])
	{
		return $this->genericQuery('RB', $value, 'frecuency', $options, self::MIN_BANDWIDTH, self::MAX_BANDWIDTH);
	}

	/**
	 * Set scale (dBm)
	 * 
	 * @param  mixed $value
	 * @param  string $unit
	 * @return $this
	 */
	public function scale($value, $options = [])
	{
		return $this->genericQuery('LG', $value, 'amplitude', $options, self::MIN_SCALE, self::MAX_SCALE);
	}

	/**
	 * Set span
	 * 
	 * @param  mixed $value
	 * @param  string $unit
	 * @return $this
	 */
	public function span($value, $options = [])
	{
		return $this->genericQuery('SP', $value, 'frecuency', $options, self::MIN_SPAN, self::MAX_SPAN);
	}

	/**
	 * Set start frecuency
	 * 
	 * @param  mixed $value
	 * @param  string $unit
	 * @return $this
	 */
	public function startFrecuency($value, $options = [])
	{
		return $this->genericQuery('FA', $value, 'frecuency', $options, self::MIN_FRECUENCY, self::MAX_FRECUENCY);
	}

	/**
	 * Set stop frecuency
	 * 
	 * @param  mixed $value
	 * @param  string $unit
	 * @return $this
	 */
	public function stopFrecuency($value, $options = [])
	{
		return $this->genericQuery('FB', $value, 'frecuency', $options, self::MIN_FRECUENCY, self::MAX_FRECUENCY);
	}

	/**
	 * Set sweep time
	 * @param  mixed $value
	 * @param  array  $options
	 * @return $this
	 */
	public function sweepTime($value, $options = [])
	{
		if(strtolower($value) == 'auto')
			return $this->genericCommand('ST ', $value, $options);

		return $this->genericQuery('ST', $value, 'time', $options, self::MIN_REFERENCE_LEVEL, self::MAX_REFERENCE_LEVEL);
	}

	/**
	 * Set video average on/off
	 * 
	 * @param  mixed $value  
	 * @param  array  $options
	 * @return $this         
	 */
	public function videoAverage($value, $options = [])
	{
		if($value === 'off' || $value === 'on')
			return $this->genericCommand('VAVG', $value, $options);

		return $this->genericQuery('VAVG', $value, null, $options, self::MIN_VIDEO_AVG, self::MAX_VIDEO_AVG);
	}

	/**
	 * Set video bandwidth
	 * 
	 * @param  mixed $value
	 * @param  string $unit
	 * @return $this
	 */
	public function videoBandwidth($value, $options = [])
	{
		return $this->genericQuery('VB', $value, 'frecuency', $options, self::MIN_BANDWIDTH, self::MAX_BANDWIDTH);
	}
}