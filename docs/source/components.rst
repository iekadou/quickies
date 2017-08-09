.. _components:

===========
Components
===========

Quickies is a PHP MVC framework with lot's of nice-to-have features.
It is a good idea to use if you want to have a fast, easy to code web application in PHP.

Config
======

``inc/include.php``:

This file is the main entry point of your project.

.. code-block:: php
    :linenos:

    <?php
    define('PATH', $_SERVER['DOCUMENT_ROOT'].'/');
    require_once(PATH."/vendor/iekadou/quickies/lib/Quickies/utils/include.php");

Typically this file has exactly this content. PATH should be defined to the root directory and the include.php should be
included as the first command.

``config/secrets.php``:

.. important::

    This file should not be in your repository. It should be delivered to your web server in a different way.

As the filename already says this is the one you want to store your Secrets inside. Required is your db connection
information, like shown below. Of course it may contain every other secret you need in your app.

.. code-block:: php
    :linenos:

    <?php
    $secrets['db_host'] = "secret_db_host";
    $secrets['db_name'] = "secrect_dbname";
    $secrets['db_user'] = "secrect_username";
    $secrets['db_pass'] = "secrect_pass";


``config/webapp.php``:

This file is the main configuration file of your project.

.. code-block:: php
    :linenos:

    <?php
    define("USERCLASS", "Iekadou\\Quickies\\User");
    define("SITE_NAME", 'Quickies.io');
    define("DOMAIN", 'www.quickies.io');
    define("NO_REPLY_EMAIL", "noreply@quickies.io");
    define("LARE_PREFIX", "quickies");
    define("DB_DEBUG", false);
    define("TEMPLATE_CACHING", trie);
    define("DISPLAY_DEBUG_INFORMATION", false);
    define("DISPLAY_CURRENT_TIME", false);
    define("UPLOADDIR", "uploads/");
    define("DEFAULT_LANGUAGE", "de");

USERCLASS defines the classname of the User object.
SITE_NAME, DOMAIN should match your application's data.
NO_REPLY_EMAIL is used as sender e-mail address for e-mails to your users.

LARE_PREFIX is the prefix used for php-lare.

LARE is a lightweight Ajax replacement engine, which will reduce traffic and CPU load of your site when used correctly.

For more information we recommend you to read https://github.com/lare-team/lare.js .

DB_DEBUG should always be false in production mode. It displays every database query directly via print at the position
it is called. This is very helpful to debug or improve performance.

As Quickies uses Twig as template engine it is a good idea to use TEMPLATE_CACHING to cache your templates.
For more information read https://twig.symfony.com/doc/2.x/api.html and https://twig.symfony.com/ .

DISPLAY_DEBUG_INFORMATION and DISPLAY_CURRENT_TIME are variables to enable/disable the variables
*display_debug_information* and *display_current_time* in Globals. The global space for variables in Quickies.

Instantiator
============

To instantiate an Object you should use Instantiator function ``_i($classname)``.

``$classname`` is usually in the *_cn* attribute of the Class, e.g:

.. code-block:: php
    :linenos:

    <?php
    _i(Article::_cn)

URLs
====

To not mess up the URLs in your project you should use the *UrlsPy*. The name derives from the urls.py of the Django
Framework for Pyhton, where it is inspired from.

The idea is to have one place to define your URL endpoints.
In Quickies it is the *.htaccess*.

``.htaccess``:

.. code-block:: apache
    :linenos:

    RewriteEngine On

    RewriteCond %{HTTP:X-Forwarded-Proto} !https
    RewriteCond %{HTTPS} off
    RewriteRule ^ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]

    Header add Access-Control-Allow-Origin "*"
    Header add Access-Control-Allow-Headers "origin, x-requested-with, content-type"
    Header add Access-Control-Allow-Methods "PUT, GET, POST, DELETE, OPTIONS"
    Header add Access-Control-Allow-Credentials "true"

    # prevent direct access to any file except static folder
    RewriteCond %{REQUEST_FILENAME} -f
    RewriteCond %{REQUEST_URI} !^/static/.*$
    RewriteCond %{ENV:REDIRECT_STATUS} ^$
    RewriteRule (.*) / [R=404,L,NC]

    # deny direct access to views
    RewriteCond %{REQUEST_URI} ^/views/.*$
    RewriteCond %{ENV:REDIRECT_STATUS} ^$
    RewriteRule (.*) / [R=404,L,NC]

    # for URLS_PY append ###namespace### in line
    #URLS_PY START#
    RewriteRule ^$ views/index.php [L] ###home###
    RewriteRule ^activate/([^/\.]+)/?$ views/activate.php?activation_key=$1 [L] ###activate####
    RewriteRule ^activate/$ views/activate.php [L] ###activate####

    RewriteRule ^forgot-password/$ views/forgot_password.php [L] ###forgot_password###
    RewriteRule ^logout/$ views/logout.php [L] ###logout###
    RewriteRule ^login/$ views/login.php [L] ###login###
    RewriteRule ^register/$ views/register.php [L] ###register###

    # backend urls
    RewriteRule ^account/$ views/account/index.php [L] ###account###
    RewriteRule ^account/activate/$ views/account/activate.php [L] ###account:activate###
    RewriteRule ^account/profile/$ views/account/profile.php [L] ###account:profile###
    RewriteRule ^account/users/$ views/account/users.php [L] ###account:users###
    RewriteRule ^account/user/([^/\.]+)/?$ views/account/user.php?id=$1 [L] ###account:user###

    # api urls
    RewriteRule ^api/account/activate/$ vendor/iekadou/quickies/lib/Quickies/api_views/activate.php [L] ###api:account:activate###
    RewriteRule ^api/forgot-password/$ vendor/iekadou/quickies/lib/Quickies/api_views/forgot_password.php [L] ###api:forgot_password###
    RewriteRule ^api/login/$ vendor/iekadou/quickies/lib/Quickies/api_views/login.php [L] ###api:login###
    RewriteRule ^api/profile/$ vendor/iekadou/quickies/lib/Quickies/api_views/profile.php [L] ###api:profile###
    RewriteRule ^api/register/$ vendor/iekadou/quickies/lib/Quickies/api_views/register.php [L] ###api:register###
    RewriteRule ^api/user/$ vendor/iekadou/quickies/lib/Quickies/api_views/user.php [L] ###api:user###
    RewriteRule ^api/user/([^/\.]+)/?$ vendor/iekadou/quickies/lib/Quickies/api_views/user.php?id=$1 [L] ###api:user###

    # articles
    RewriteRule ^api/article/$ api_views/article.php [L] ###api:article###
    RewriteRule ^api/article/([^/\.]+)/?$ api_views/article.php?id=$1 [L] ###api:article###
    RewriteRule ^articles/$ views/articles.php [L] ###articles####
    RewriteRule ^article/([^/\.]+)/?$ views/article.php?slug=$1 [L] ###article###
    RewriteRule ^account/articles/$ views/account/articles.php [L] ###account:articles###
    RewriteRule ^account/article/([^/\.]+)/?$ views/account/article.php?id=$1 [L] ###account:article###
    #URLS_PY END#

    # static/js/tinymce should link to vendor tinymce.
    RewriteRule ^static/js/tinymce/(.*)$ vendor/tinymce/tinymce/$1 [L]

    # deny direct access to api
    RewriteCond %{REQUEST_URI} ^/api_views/*$
    RewriteCond %{ENV:REDIRECT_STATUS} ^$
    RewriteRule (.*) / [R=404,L,NC]

    # deny direct access to vendor
    RewriteCond %{REQUEST_URI} ^/vendor/.*$
    RewriteCond %{ENV:REDIRECT_STATUS} ^$
    RewriteRule (.*) / [R=404,L,NC]

    # restrictions
    RewriteRule ^classes/.*$ - [R=404,L,NC]
    RewriteRule ^config/.*$ - [R=404,L,NC]
    RewriteRule ^inc/.*$ - [R=404,L,NC]
    RewriteRule ^templates/.*$ - [R=404,L,NC]
    RewriteRule ^migrations/.*$ - [R=404,L,NC]

    # appending trailing slash if no file found
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{ENV:REDIRECT_STATUS} ^$
    RewriteRule ^(.*[^/])$ /$1/ [L,R]

    ErrorDocument 404 /views/_errors/404.php


As you can see it looks a lot like a normal *.htaccess* file except for the comments. And they are the ones who do the
magic.

Let's look at line *25* ``RewriteRule ^$ views/index.php [L] ###home###``.

It defines that calling ``/`` will open the *views/index.php* as usually.
Additionally it flags this endpoint with the name *home*.


The same thing is done in  line *33* ``RewriteRule ^article/([^/\.]+)/?$ views/article.php?id=$1 [L] ###article###``.
Except that it has to be called with a paramenter, called in the View available as id.


Views
=====

Let's start with a very basic example.

``views/index.php``:

.. code-block:: php
    :linenos:

    <?php
    namespace Iekadou\Example;
    require_once("../inc/include.php");
    use Iekadou\Quickies\Translation;
    use Iekadou\Quickies\View;

    $View = new View('index', Translation::translate('Home'), "index.html");
    $View->render();

In a View you will define the business logic of your application. The file is called the standard PHP-way.
So in every View file you should define your namespace and require the ``include.php``, followed by the import of the
components you want to use. Typically you need the View and for a multi-language site the Translation component.

As you can see in line 7 a View is a PHP object which is instantiated with an id, a verbose name and a template which is
used to render. In this example ``Home`` is translated by the Translation component.

So the example above simply renders the ``index.html``, a Twig template.


``views/article.php``:

.. code-block:: php
    :linenos:

    <?php
    namespace Iekadou\Example;
    require_once("../inc/include.php");
    use Iekadou\Quickies\Utils;
    use Iekadou\Quickies\View;


    $slug = (isset($_GET['slug']) ? htmlspecialchars($_GET['slug']) : false);

    $Article = _i(Article::_cn)->get_by(array(array("slug", "=", $slug), array("public", "=", 1)));
    if (!$Article) {
        Utils::raise404();
        die();
    }
    $View = new View($Article->slug, $Article->name, 'article.html');
    $View->set_template_var('article', $Article);
    $View->render();


This view renders an article specified by a slug using the ``article.html`` template.


Models
======

A Model in Quickes represents an entity in a database. You simply create a Class extending the BaseModel, defining the
database table name and which fields it consists of.

``classes/Article.php``

.. code-block:: php
    :linenos:

    <?php
    namespace Iekadou\Example;

    use Iekadou\Quickies\BaseModel;
    use Iekadou\Quickies\TimestampField;
    use Iekadou\Quickies\VarcharField;
    use Iekadou\Quickies\TextField;

    class Article extends BaseModel
    {
        const _cn = "Iekadou\\Example\\Article";

        protected $table = 'article';
        protected $fields = array(
            'created_at' => array('type' => TimestampField::_cn, 'auto_create' => true),
            'title' => array('type' => VarcharField::_cn, 'max_length' => 255),
            'content' => array('type' => TextField::_cn)
        );
        public $form_fields = array('title', 'content');
    }

The Model *Article* above consists of a timestamp *created_at*, a Varchar *title* with a max length of 255 and a TextField
*content*.


Here is an example how to create an instance

.. code-block:: php
    :linenos:

    <?php
    $article = _i(Article::_cn);
    $article->title = "Title of an Article";
    $article->content = "This is the content of this article.\nWow so amazing!";
    $article->create();

To get an instance out of the database simply use the ``get`` or ``get_by`` methods like this:

.. code-block:: php
    :linenos:

    <?php
    $article = _i(Article::_cn)->get($id);
    $another_article = _i(Article::_cn)->get_by(array(array('title', '=', "Title of an Article")));

The ``get`` method accepts only one parameter: A value of the field *id*.
It returns one object or false if no object was found matching the id.

``get_by`` accepts 3 parameters:

+-------------+-------------------------------------------------------------------------------------------+
| $conditions | An array, which defines which conditions have to be met by the Query to return an object. |
|             |                                                                                           |
|             | E.g.:                                                                                     |
|             |                                                                                           |
|             | ``array(array('title', 'LIKE', 'First%'), array('created_at', '<', time()))``             |
+-------------+-------------------------------------------------------------------------------------------+
| $sortings   | An array, which defines how the queryset should be sorted.                                |
|             |                                                                                           |
|             | E.g.:                                                                                     |
|             |                                                                                           |
|             | ``array(array('title', 'ASC'), array('created_at', 'DESC'))``                             |
+-------------+-------------------------------------------------------------------------------------------+
| $limit      | A string which defines in which way the queryset should be limited.                       |
+-------------+-------------------------------------------------------------------------------------------+

To retrieve multiple objects you should use ``filter_by`` with the same parameters like ``get_by``.

.. code-block:: php
    :linenos:

    <?php
    $articles = _i(Article::_cn)->filter_by(array(array('title', 'LIKE', "Title%")));

To *update* an object change it's attributes and use the ``save`` method to save it in the database like this:

.. code-block:: php
    :linenos:

    <?php
    $article->title = "Updated title";
    $article->save();

To *delete* an object use the ``delete`` method, like in the two examples below:

.. code-block:: php
    :linenos:

    <?php
    $article->delete();

    _i(Article::_cn)->delete($id);


You can also *delete* multiple objects using ``delete_by`` using the conditions parameter as in ``get_by``:

.. code-block:: php
    :linenos:

    <?php
    $article->delete_by(array(array('title', 'LIKE', '%LOREM%')));

To *count* how many items would be in a queryset use ``count_by`` using the conditions parameter as in ``get_by``:

.. code-block:: php
    :linenos:

    <?php
    $article->count_by(array(array('title', 'LIKE', '%LOREM%')));


Migrations
==========

As Models are created or changed, the database should change the same way.

Those changes are saved in *Migrations*.
After you defined a Model and include the Class in include.php you simply run the makemigrations endpoint to generate
your migrations. After the generation you can apply the migrations on the databse using the migrate endpoint.

In the default ``.htaccess`` the endpoints are defined like this:

.. code-block:: apache
    :linenos:

    RewriteRule ^migrate/$ vendor/iekadou/quickies/lib/Quickies/views/migrate.php [L] ###migrate###
    RewriteRule ^makemigrations/$ vendor/iekadou/quickies/lib/Quickies/views/makemigrations.php [L] ###makemigrations###

So calling ``/makemigrations/`` and ``/migrate/`` will do the work.
