# Prologix GPIB ETH/USB classes

A few PHP classes to talk to GPIB capable devices through the Prologix GPIB interface. Currently, just the Prologix GPIB ETH interface is implemented. A work in progress.


#Usage

There are two ways to talk to devices connected to the GPIB bus. The first, using the *set* method, will send any command passed as an argument directly to the device. Though it may be what you are looking for, keep in mind that this interface has a EEPROM in where most of the commands are saved, so it may be a better option to use the *addCommand* method along with *setAll* to extend it's lifespan. The first argument for *set* and *setAll* is the device GPIB address.


```
$interface = new PrologixEth('192.168.254.243');

$interface->set(6, 'CF70000000')
		  ->set(6, 'SP1000000')
		  ->set(6, 'RL-55');


$interface->addCommand('CF70000000')
		  ->addCommand('SP1000000')
		  ->addCommand('RL-55')
		  ->setAll(6);
```

**NOTE** both ways will prepend the *++addr* command

You'll find also two examples for a HP8593E spectrum analyzer (though should work with most SA) and a HP3488A switch unit. Just two wrapper around the GpibInterface implemented by PrologixEth class with *easy to remember* methods for common tasks.

```
$spectrum = new HP8593E(6, $interface);

$spectrum->span(1)
		 ->centerFrecuency(70, ['unit' => 'mhz'])
		 ->referenceLevel(-55)
		 ->set();
```

Most of the methods will accept a value and an array of options. By default, this options are:

- unit => hz
- store = true

With *store* = true, the commands will be saved and you should manually send them to the device using the *set* method.

####TODO

[] Prologix USB implementation (don't own one).
[] Laravel related files (service providers, facades, config, etc).
[] Find a better (**ANY**) socket implementation.
[] Tests.