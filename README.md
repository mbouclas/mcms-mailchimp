### MCMS Mailchimp  
This package provides an easy way to integrate MailChimp with Laravel 5. Behind the scenes v3 for the MailChimp API is used. Here are some examples of what you can do with the package:

```php
Newsletter::subscribe('rincewind@discworld.com');

Newsletter::unsubscribe('the.luggage@discworld.com');

//Merge variables can be passed as the second argument
Newsletter::subscribe('sam.vines@discworld.com', ['firstName'=>'Sam', 'lastName'=>'Vines']);

//Subscribe someone to a specific list by using the third argument:
Newsletter::subscribe('nanny.ogg@discworld.com', ['firstName'=>'Nanny', 'lastName'=>'Ogg'], 'Name of your list');

//Get some member info, returns an array described in the official docs
Newsletter::getMember('lord.vetinari@discworld.com');

//Returns a boolean
Newsletter::hasMember('greebo@discworld.com');

//If you want to do something else, you can get an instance of the underlying API:
Newsletter::getApi();
```

## Installation

You can install this package via Composer using:

```bash
composer require mbouclas/mcms-mailchimp
```

You must also install this service provider.

```php
// config/app.php
'providers' => [
    ...
    Mcms\Mailchimp\McmsMailchimpServiceProvider::class,
    ...
];
```


To publish the config file to `app/config/mcmcMailchimp.php` run:

```bash
php artisan vendor:publish --provider="Mcms\Mailchimp\McmsMailchimpServiceProvider"
```

This will publish a file `mcmcMailchimp.php` in your config directory with the following contents: 
```php
return [

        /*
         * The api key of a MailChimp account. You can find yours here:
         * https://us10.admin.mailchimp.com/account/api-key-popup/
         */
        'apiKey' => env('MAILCHIMP_APIKEY'),

        /*
         * When not specifying a listname in the various methods,
         *  this list name will be used.
         */
        'defaultListName' => 'subscribers',

        /*
         * Here you can define properties of the lists you want to
         * send campaigns.
         */
        'lists' => [

            /*
             * This key is used to identify this list. It can be used
             * in the various methods provided by this package.
             *
             * You can set it to any string you want and you can add
             * as many lists as you want.
             */
            'subscribers' => [

                /*
                 * A mail chimp list id. Check the mailchimp docs if you don't know
                 * how to get this value:
                 * http://kb.mailchimp.com/lists/managing-subscribers/find-your-list-id
                 */
                 'id' => env('MAILCHIMP_LIST_ID'),
            ],
        ],
];
```

## Usage

After you've installed the package and filled in the values in the config-file working with this package will be a breeze. All the following examples use the facade. Don't forget to import it at the top of your file.

```php
use Newsletter;
```

### Subscribing and unsubscribing

Subscribing an email address can be done like this:

```php
use Newsletter;

Newsletter::subscribe('rincewind@discworld.com');
```

Let's unsubscribe someone:

```php
Newsletter::unsubscribe('the.luggage@discworld.com');
```

You can pass some merge variables as the second argument:
```php
Newsletter::subscribe('rincewind@discworld.com', ['firstName'=>'Rince', 'lastName'=>'Wind']);
```
Please note the at the time of this writing the default merge variables in MailChimp are named `FNAME` and `LNAME`. In our examples we use `firstName` and `lastName` for extra readability.

You can subscribe someone to a specific list by using the third argument:
```php
Newsletter::subscribe('rincewind@discworld.com', ['firstName'=>'Rince', 'lastName'=>'Wind'], 'subscribers');
```
That third argument is the name of a list you configured in the config file.

You can also unsubscribe someone from a specific list:
```php
Newsletter::unsubscribe('rincewind@discworld.com', 'subscribers');
```

### Deleting subscribers

NOTE: Deleting is not the same as unsubscribing. Deleting loses all history (add/opt-in/edits) as well as removing them from the list. Unlike an unsubscribe, they can still be added back again later. This is for list-maintenance only. 
If a subscriber is "opting out", you should `unsubscribe` instead!

To delete a subscriber's record from the list permanently:
```php
Newsletter::delete('rincewind@discworld.com');
```

### Getting subscriber info

You can get information on a subscriber by using the `getMember` function:
```php
Newsletter::getMember('lord.vetinari@discworld.com');
```

This will return an array with information on the subscriber. If there's no one subscribed with that
e-mail address the function will return `false`

There's also a convenience method to check if someone is already subscribed:

```php
Newsletter::hasMember('nanny.ogg@discworld.com'); //returns a bool
```

### Creating a campaign

This is how you create a campaign:
```php
/**
 * @param string $fromName
 * @param string $replyTo
 * @param string $subject
 * @param string $html
 * @param string $listName
 * @param array  $options
 * @param array  $contentOptions
 *
 * @return array|bool
 *
 * @throws \Mcms\Mailchimp\Exceptions\InvalidMailchimpList
 */
public function createCampaign($fromName, $replyTo, $subject, $html = '', $listName = '', $options = [], $contentOptions = [])
```

Note the campaign will only be created, no mails will be sent out.

### Handling errors

If something went wrong you can get the last error with:
```php
Newsletter::getLastError();
```

If you just want to make sure if the last action succeeded you can use:
```php
Newsletter::lastActionSucceeded(); //returns a bool
```

```php
$api = Newsletter::getApi();
```

## Testing

Run the tests with:
```bash
vendor/bin/phpunit
```

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
