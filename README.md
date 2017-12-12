CultuurNet\Auth is a PHP library implementing

* the consumer-side of the authentication flow of CultuurNet's UiTiD, which is based on [OAuth 1.0a Core][oauth_core]
* a solid base for consumers of various OAuth-protected resources provided by CultuurNet

Prerequisites
=============

You'll need an OAuth consumer key & secret pair. You can find a regulary refreshed pair for testing purposes in the
[UiTiD manual][uitid_docs]. These credentials are valid for the base URL http://acc.uitid.be/uitid/rest.

You will need to register for an UiTiD account as well. Append /auth/register' to the base URL (for example
http://acc.uitid.be/uitid/rest/auth/register), visit the resulting URL with your web browser and fill out the
registration form.

Installation
=============

You can install the CultuurNet\Auth PHP library in different ways:

* Standalone. Clone or download from github and use [Composer][composer]. Run ``composer install`` from
  the root of the clone to download the necessary dependencies. Standalone usage is probably only useful for testing
  purposes.
* Inside your project: require the cultuurnet/auth package (it is
  [registered on Packagist][packagist]) in your project's composer.json file and run ``composer update``.

```json
{
    "require": {
        "cultuurnet/auth": "~1.1"
    }
}
```


Command line tool
===================

The included command line tool is intended for testing and debugging.

Location
--------

When using CultuurNet\Auth standalone, as a download or clone from github, the command line tool is available at
bin/cultuurnet-auth.

When included inside your own project by using Composer, the command line tool is available at
vendor/bin/cultuurnet-auth. Further examples in this documentation will use that location.

Listing available commands and getting help
-------------------------------------------

Use the ``list`` command to get a list of available commands:

    $ ./vendor/cultuurnet/auth/bin/cultuurnet-auth list

Use the ``help`` command to get help for a specific command, for example for the ``authenticate`` command:

    $ ./vendor/cultuurnet/auth/bin/cultuurnet-auth help authenticate

User preferences
----------------

The command line tool makes use of user defaults which you can define in an INI file 'defaults' inside a '.cultuurnet'
directory located in your HOME directory: ~/.cultuurnet/defaults. The defaults coming from the INI file can be
overridden per request by several options of the command line tool.

The directives that can be used in the INI file are:

* consumer-key: the OAuth consumer key
* consumer-secret: the OAuth consumer secret
* base-url: an array of base urls, one per distinct API. The base url for the CultuurNet OAuth 1.0a Core defined
  endpoints for example can be defined in base-url[auth]

An example of how the contents of the file could look like:

```ini
consumer-key=76163fc774cb42246d9de37cadeece8a
consumer-secret=fff975c5a8c7ba19ce92969c1879b211
base-url[auth]=http://acc.uitid.be/uitid/rest
```

The authenticate command
------------------------

Use the ``authenticate`` command to login with your UiTiD and authorize your service consumer to access the different
CultuurNet webservices on behalf of you.

To login with your UiTiD, you need to specify your username and password. The command line tool

* retrieves an OAuth request token,
* posts your username and password to the UiTiD service provider,
* authorizes the consumer there as well,
* intercepts the returned OAuth verifier,
* and finally exchanges the temporary credentials for access token credentials

```
$ ./vendor/bin/cultuurnet-auth \
> --consumer-key=76163fc774cb42246d9de37cadeece8a \
> --consumer-secret=fff975c5a8c7ba19ce92969c1879b211 \
> --username=foo \
> --password=bar \
authenticate
```

For added security you can input username and password interactively instead of using command line options.

```
$ ./vendor/bin/cultuurnet-auth \
> --consumer-key=76163fc774cb42246d9de37cadeece8a \
> --consumer-secret=fff975c5a8c7ba19ce92969c1879b211 \
> authenticate
User name: foo
Password:
```

You can retrieve and/or store commonly necessary configuration data & credentials for authorized communication with the
different CultuurNet web services in a so-called 'session' file, by specifying the desired location with the
``--session`` option.

You can continue to use the session file in other commands by specifying the ``--session`` option again, so you don't
have to specify all command line options over and over again.

Please ensure the session file is located at a safe place and not accessible by other users, as it will allow others
to make requests on your behalf. The following items are maintained in a session file:

* base URLs of the various APIs
* consumer key & consumer secret
* access token & token secret retrieved by the ``authenticate`` command



[oauth_core]: http://oauth.net/core/1.0a/
[uitid_docs]: http://tools.uitdatabank.be/docs/uitid
[composer]: http://getcomposer.org
[packagist]: https://packagist.org/packages/cultuurnet/auth
