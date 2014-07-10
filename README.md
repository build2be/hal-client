PHP HAL Client
==============

Usage
-----

```php
<?php
use HalClient\Resource;

$orders = Resource::request('http://example.com/orders');
$orders->getParameters();  # Return the non-hal json data in the resource
echo $orders->getUrl('find', array('id' => 2));  # http://example.com/orders?id=2
echo $orders->getLink('self')->getDocumentationUrl() # http://example.com/docs/orders
```