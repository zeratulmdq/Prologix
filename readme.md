# Prologix GPIB ETH/USB classes

A few PHP classes to talk to GPIB capable devices through the Prologix GPIB interface. Currently, just an HP8593E spectrum analyzer (though should work with most SA) and a HP3488A switch unit are implemented. A work in progress.

#Usage

```
$interface = new PrologixEth('192.168.254.243');

(new HP8593E(18, $interface))->span(1, 'mhz')
	                         ->centerFrecuency(3940, 'mhz')
	                         ->referenceLevel(-55)
	                         ->set();

(new HP3488A(1, $interface))->open([300])
		                    ->close([302, 502])
		                    ->set();
```

##TODO

* Prologix USB implementation (don't own one).
* Laravel related files (service providers, facades, config, etc).
* Find a better socket implementation.
* Tests.