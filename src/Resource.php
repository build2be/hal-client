<?php

namespace HalClient;


use Curl\Curl;
use Symfony\Component\Yaml\Exception\RuntimeException;

class Resource
{
    private $data = array();
    private $linkCollection;
    private $cache = array();
    private $fromEmbed = false;

    function __construct()
    {
        $this->linkCollection = new LinkCollection();
    }

    static function request($url, $username = null, $password = null, $headers = array())
    {
        $curl = new Curl();
        if ($username !== null && $password !== null) {
            $curl->setBasicAuthentication($username, $password);
        }
        foreach ($headers as $key => $value) {
            $curl->setHeader($key, $value);
        }
        $curl->setHeader('Accept', 'application/hal+json');
        $curl->get($url);
        if ($curl->error) {
            throw new RuntimeException($curl->error_message, $curl->error_code);
        } else {
            $data = json_decode($curl->response, true);
            return Resource::fromJsonResponse($data);
        }

    }

    public function uncached()
    {
        $url = $this->getUrl('self');
        return Resource::request($url);
    }

    /**
     * @return boolean
     */
    public function isFromEmbed()
    {
        return $this->fromEmbed;
    }

    public function getProperties()
    {
        $result = $this->data;
        if (isset($result['_links'])) {
            unset($result['_links']);
        }
        if (isset($result['_embedded'])) {
            unset($result['_embedded']);
        }
        return $result;
    }

    public function hasLink($rel)
    {
        return $this->linkCollection->hasLink($rel);
    }

    public function getUrl($linkName, $parameters = array())
    {
        if (!$this->linkCollection->hasLink($linkName)) {
            throw new \InvalidArgumentException('Link "' . $linkName . '" does not exist.');
        }
        /**
         * @var $link Link
         */
        $link = $this->linkCollection->getLink($linkName);
        $href = $link->getHref();
        if ($link->isTemplate()) {
            return Resource::parseUrlTemplate($href, $parameters);
        } else {
            return $href;
        }
    }

    public function getLink($linkName)
    {
        if (!$this->linkCollection->hasLink($linkName)) {
            throw new \InvalidArgumentException('Link "' . $linkName . '" does not exist.');
        }
        return $this->linkCollection->getLink($linkName);
    }

    static function parseUrlTemplate($template, $parameters = array())
    {
        //$regex_fieldnames = '/\\{\\??([a-zA-Z0-9]+),?(?:([a-zA-Z0-9]+,)*([a-zA-Z0-9]+)?)*\\}/';
        //preg_match($regex_fieldnames, $template, $fieldnames);
        //var_dump($fieldnames);
        //$fieldnames = array_unique($fieldnames);
        //$given_fieldnames = array_keys($parameters);
        //$missing = array_diff($fieldnames, $given_fieldnames);
        //if (count($missing > 0)) {
        //    throw new \InvalidArgumentException('Missing arguments: ' . implode(', ', $missing));
        //}

        foreach ($parameters as $search => $replace) {
            $template = str_replace('{' . $search . '}', $replace, $template);
        }
        $regex_urlparameter_fields = '/(\\{\\?[a-zA-Z0-9,]+\\})/';
        $template = preg_replace_callback(
          $regex_urlparameter_fields,
          function ($matches) use ($parameters) {
              $matches = $matches[0];
              $matches = substr(substr($matches, 2), 0, -1);
              $fields = explode(',', $matches);
              $results = array();
              foreach ($fields as $field) {
                  $results[$field] = $parameters[$field];
              }
              $querystr = http_build_query($results);
              return '?' . $querystr;
          }, $template);

        return $template;
    }

    static function fromJsonResponse($response, $fromEmbed = false)
    {
        $resource = new Resource();
        $resource->data = $response;
        $resource->fromEmbed = $fromEmbed;

        if (isset($response['_links'])) {

            if (isset($response['_links']['curies'])) {
                foreach ($response['_links']['curies'] as $curie) {
                    $curie = Link::parse('curies', $curie);
                    $resource->linkCollection->addCurie($curie);
                }
            }

            foreach ($response['_links'] as $linkId => $linkData) {
                if (isset($linkData[0])) {
                    foreach ($linkData as $link) {
                        $link = Link::parse($linkId, $link);
                        $resource->linkCollection->addMultiLink($link);
                    }
                } else {
                    $link = Link::parse($linkId, $linkData);
                    $resource->linkCollection->addLink($link);
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