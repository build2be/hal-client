PHP HAL Client
==============

Installation
------------

In composer.json:

```json
{
    "repositories": [
        {
            "url": "git@github.com:build2be/hal-client.git",
            "type": "git"
        }
    ],
    "require": {
        "build2be/hal-client": "*"
    }
}

```

Usage
-----

Example file on /orders:

```json
{
    "_links": {
        "self": { "href": "/orders" },
        "next": { "href": "/orders?page=2" },
        "find": { "href": "/orders{?id}", "templated": true }
    },
    "currentlyProcessing": 14,
    "shippedToday": 20
}
```

```php
<?php
use HalClient\Resource;

$orders = Resource::request('http://example.com/orders');
$orders->getParameters();  # Return the non-hal json data in the resource
echo $orders->getUrl('find', array('id' => 2));  # http://example.com/orders?id=2
echo $orders->getLink('self')->getDocumentationUrl() # http://example.com/docs/orders
```