<?php
require_once(__DIR__ . '/TestBase.php');

class DrupalTest extends TestBase
{

    public function testGetProperties()
    {
        $resource = \HalClient\Resource::request('http://d8.dev/node/1', 'admin', 'admin');
        $pageDataTmp = $resource->getProperties();
        $pageData = array();
        $pageData['body'] = $pageDataTmp['body'];
        $pageData['title'] = $pageDataTmp['title'];
        $pageData['_links']['type'] = array(
            'href' => 'http://d8.dev/rest/type/node/article'
        );
        echo json_encode($pageData);
        $postResponse = $resource->post($pageData, 'http://d8.dev/entity/node');
        var_dump($postResponse);
        //var_dump($resource->getLinks());
    }

}