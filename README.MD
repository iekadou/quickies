# Quickies

A web framework for PHP based on django

## How to install Quickies?

1. Install Composer

For further information please read [https://getcomposer.org/doc/00-intro.md](https://getcomposer.org/doc/00-intro.md).

2. Add Quickies to your composer.json:

	```json
    {
        "repositories": [
            {
                "type": "vcs",
                "url":  "git@github.com:iekadou/quickies.git"
            }
        ],
        "require": {
            "iekadou/quickies": ">=0.1.1",
            "tinymce/tinymce": ">= 4"
        },
        "config": {
            "bin-dir": "./"
        }
    }
	```

3. composer install

4. php quicky makemigrations

5. php quicky migrate
