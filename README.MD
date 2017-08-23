# Quickies

A web framework for PHP, using Mysql and Apache inspired by Django

## How to install Quickies?

### 1. Install Composer

For further information please read [https://getcomposer.org/doc/00-intro.md](https://getcomposer.org/doc/00-intro.md).

### 2. Add Quickies to your composer.json:

	```json
    {
        "require": {
            "iekadou/quickies": ">=0.1.3",
            "tinymce/tinymce": ">= 4"
        },
        "config": {
            "bin-dir": "./"
        }
    }
	```

### 3. Install Packages

To install the required packages just run the following command:

    $ composer install


## Example

We provide a simple example project at [https://github.com/iekadou/quickies-example](https://github.com/iekadou/quickies-example).

All important features are used there.

### Additional Information

Please note that Quickies is still in development and not stable in any way.

## Contributing

Feel free to contribute to Quickies. Help us bring this project to a stable version and far, far beyond.

## Security Vulnerabilities

If you discover a security vulnerability within Quickies, please send an e-mail to Jonas Braun (jonas@iekadou.com). All security vulnerabilities will be promptly addressed.

## License

Quickies is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT).
