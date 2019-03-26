# JSON Objects

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Build Status][ico-travis]][link-travis]
[![Coverage Status][ico-scrutinizer]][link-scrutinizer]
[![Quality Score][ico-code-quality]][link-code-quality]
[![Total Downloads][ico-downloads]][link-downloads]

This package extracts JSON objects from large JSON sources like files, endpoints and streams while saving memory. It parses heavy JSONs by using [JsonStreamingParser][link-jsonstreamingparser] and provides an easy API to declare what objects to extract and process.

## Install

Via Composer

``` bash
$ composer require cerbero/json-objects
```

## Usage

Simply pass the JSON source (files, endpoints, streams) and optionally the key where objects are contained to create a
new instance of `JsonObjects`. You can also call the factory method `from()`:

``` php
$source = 'https://jsonplaceholder.typicode.com/users';

// Create a new instance specifying the JSON source to extract objects from
new JsonObjects($source);
// or
JsonObjects::from($source);

// Create a new instance specifying the JSON source and the key to extract objects from
new JsonObjects($source, 'address.geo');
// or
JsonObjects::from($source, 'address.geo');
```

When providing a key to extract objects from, you can use the dot notation to indicate nested sections of a JSON. For
example `nested.*.key` extracts all the objects in the property `key` of every object contained in `nested`.

Finally you can decide whether to extract and process objects one by one or in chunks. The memory will be allocated to
read these objects only instead of the whole JSON document:

``` php
// Extract and process one object at a time from the given JSON source
JsonObjects::from($source)->each(function (array $object) {
    // Process one object
});

// Extract and process a chunk of objects at a time from the given JSON source
JsonObjects::from($source)->chunk(100, function (array $objects) {
    // Process 100 objects
});
```

## Change log

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Testing

``` bash
$ composer test
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) and [CODE_OF_CONDUCT](CODE_OF_CONDUCT.md) for details.

## Security

If you discover any security related issues, please email andrea.marco.sartori@gmail.com instead of using the issue tracker.

## Credits

- [Andrea Marco Sartori][link-author]
- [JsonStreamingParser][link-jsonstreamingparser]

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/cerbero/json-objects.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/cerbero90/json-objects/master.svg?style=flat-square
[ico-scrutinizer]: https://img.shields.io/scrutinizer/coverage/g/cerbero90/json-objects.svg?style=flat-square
[ico-code-quality]: https://img.shields.io/scrutinizer/g/cerbero90/json-objects.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/cerbero/json-objects.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/cerbero/json-objects
[link-travis]: https://travis-ci.org/cerbero90/json-objects
[link-scrutinizer]: https://scrutinizer-ci.com/g/cerbero90/json-objects/code-structure
[link-code-quality]: https://scrutinizer-ci.com/g/cerbero90/json-objects
[link-downloads]: https://packagist.org/packages/cerbero/json-objects
[link-author]: https://github.com/cerbero90
[link-jsonstreamingparser]: https://github.com/salsify/jsonstreamingparser
