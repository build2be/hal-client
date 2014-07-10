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

    private function addCurie($structure)
    {
        $this->curies[$structure['name']] = array(
          'href' => $structure['href'],
          'templated' => $structure['templated'],
        );
    }

    private function parseEmbed($embed)
    {
        if (!isset($embed['_links']['self']['href'])) {
            // RFC Validation error
        }
        if (isset($embed['_links']['self']['templated']) && $embed['_links']['self']['templated'] == true) {
            // RFC Validation error
        }
        $key = $embed['_links']['self']['href'];
        $parsed = Resource::fromJsonResponse($embed);
        $this->cache[$key] = $parsed;
    }
}