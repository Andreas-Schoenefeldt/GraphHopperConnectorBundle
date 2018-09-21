# GraphHopperConnectorBundle
Allows to use the GraphHopper geocoding api at https://graphhopper.com/api/1/docs/geocoding/

you'll need an API key to use this service. It is just an easy wrapper to actually have access in the symfony context to this.

The Bundle converts the resut of the graphhopper API to [geojson](http://geojson.org/) in order to allow it to work seamlessly with other apis. 

## Installation

### Step 1: Download the Bundle


Open a command console, enter your project directory and execute the
following command to download the latest stable version of this bundle:

```console
$ composer require schoenef/graph-hopper-connector-bundle:~1.0
```

This command requires you to have Composer installed globally, as explained
in the [installation chapter](https://getcomposer.org/doc/00-intro.md)
of the Composer documentation.

### Step 2: Enable the Bundle

Then, enable the bundle by adding it to the list of registered bundles
in the `app/AppKernel.php` file of your project:

```php
<?php
// app/AppKernel.php

// ...
class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            // ...

            new Schoenef\GraphHopperConnectorBundle\GraphHopperConnectorBundle(), // geo coding service wrapper
        );

        // ...
    }

    // ...
}
```

### Step 3: Configure the Bundle

Add the following configuration to your ```app/config/config.yml```:
```yml
graph_hopper_connector:
  timeout: 20
  api_key: "%your-key%"
  lang: de
  country: DE
  provider: "gisgraphy"
  autocomplete: true
```

### Usage

To use the connector, you can use the following inside of symfony controllers:

```php
$connector = $this->get('graph_hopper.connector');
$results = $connector->searchLocation('berlin');
```

