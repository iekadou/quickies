.. _setup:

How to install Quickies?
========================

1. Install Composer
-------------------

For further information please read [https://getcomposer.org/doc/00-intro.md](https://getcomposer.org/doc/00-intro.md).

2. Add Quickies to your composer.json
-------------------------------------

.. code-block:: json

    {
        "require": {
            "iekadou/quickies": ">=0.1.2",
            "tinymce/tinymce": ">= 4"
        },
        "config": {
            "bin-dir": "./"
        }
    }

3. Install Packages
-------------------

To install the required packages just run the following command:

.. code-block:: bash

    $ composer install

Example
-------

We provide a simple example project at [https://github.com/iekadou/quickies-example](https://github.com/iekadou/quickies-example).

All important features are used there.
