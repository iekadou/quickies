.. _setup:

===============
Getting Started
===============

These instructions cover how to get a working copy of the source code.


How to install
==============

Composer
---------

The preferred way to use Quickies is to use Composer:

1. Install Composer

For further information please read [https://getcomposer.org/doc/00-intro.md](https://getcomposer.org/doc/00-intro.md).

2. Add Quickies to your composer.json:

.. code-block:: json

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


3. Run the migrations by opening ``/migrate/``
