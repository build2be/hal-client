<?php

namespace HalClient;


class Resource
{
    private $data = array();
    private $links = array();
    private $cache = array();
    private $curies = array();
    private $fromEmbed = false;

    /**
     * @return boolean
     */
    public function isFromEmbed()
    {
        return $this->fromEmbed;
    }

    public function getProperties(){
        $result = $this->data;
        if(isset($result['_links'])){
            unset($result['_links']);
        }
        if(isset($result['_embedded'])){
            unset($result['_embedded']);
        }
        return $result;
    }

    public function hasLink($name){
        $name = explode('/', $name, 2);
        if(count($name) == 1){
            return isset($this->links[$name[0]]);
        }else{
            return isset($this->links[$name[0]][$name[1]]);
        }
    }

    static function fromJsonResponse($response, $fromEmbed = false)
    {
        $resource = new Resource();
        $resource->data = $response;
        $resource->fromEmbed = $fromEmbed;

        if (isset($response['_links'])) {
            foreach ($response['_links'] as $linkId => $linkData) {
                $resource->links[$linkId] = $linkData;
                if (isset($linkData[0])) {
                    foreach ($linkData as $link) {
                        if (isset($link['name'])) {
                            $resource->links[$linkId][$link['name']] = $link;
                        }
                    }
                }
            }
        }

        if (isset($response['_embedded'])) {
            foreach ($response['_embedded'] as $embed) {
                if (isset($embed[0])) {
                    foreach ($embed as $data) {
                        $resource->parseEmbed($data);
                    }
                } else {
                    $resource->parseEmbed($embed);
                }
            }
        }

        return $resource;
    }

    private function parseEmbed($embed)
    {
        if (!isset($embed['_links']['self']['href'])) {
            throw new RfcException('Embedded resource has no _links/self/href attribute');
        }
        if (isset($embed['_links']['self']['templated']) && $embed['_links']['self']['templated'] == true) {
            throw new RfcException('_links/self/href cannot be a template');
        }
        $key = $embed['_links']['self']['href'];
        $parsed = Resource::fromJsonResponse($embed, true);
        $this->cache[$key] = $parsed;
    }
}