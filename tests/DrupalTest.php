<?php
require_once(__DIR__ . '/TestBase.php');

class DrupalTest extends TestBase
{

    public function testGetProperties()
    {
        $hostname = "http://drupal.d8";
        $resource = \HalClient\Resource::request("$hostname/node/1", 'admin', 'admin');
        $pageDataTmp = $resource->getProperties();
        $pageData = array();
        $pageData['body'] = $pageDataTmp['body'];
        $pageData['title'] = $pageDataTmp['title'];
        $pageData['_links']['type'] = array(
            'href' => "$hostname/rest/type/node/article"
        );
        echo json_encode($pageData);
        $postResponse = $resource->post($pageData, "$hostname/entity/node");
        var_dump($postResponse);
        //var_dump($resource->getLinks());
    }

}
