# ObjectPath [![Build Status](https://travis-ci.org/peridot-php/object-path.svg?branch=master)](https://travis-ci.org/peridot-php/object-path)

Allows traversal of objects and arrays with a simple string syntax. Extracted from
Peridot's matcher library: [Leo](https://github.com/peridot-php/leo).

## Usage

```php
use Peridot\ObjectPath\ObjectPath;

// Works with nested arrays, objects, or a mixture of both.
$data = [
  'name' => 'Brian',
  'hobbies' => [
    'reading',
    'programming',
    'lion taming'
  ],
  'address' => new stdClass()
  [
    'street' => '1234 Lane',
    'zip' => '12345'
  ]
];

$data['address']->street = '1234 Lane';
$data['address']->zip = 12345;

// Wraps the value with ObjectPath
$path = new ObjectPath($data);

// Get the value directly
$reading = $path->{'hobbies[0]'};
$zip = $path->{'address[zip]'};

// Sets the value
$path->{'address->street'} = '12345 Street';

// Removes the property
unset($path->{'hobbies[2]'});



// backward compatible with peridot-php/object-path
$reading = $path->get('hobbies[0]');
$zip = $path->get('address[zip]');

// the result of get() is an ObjectPathValue instance
$value = $reading->getPropertyValue();
```

## Tests

```
$ composer install
$ vendor/bin/peridot specs/
```
