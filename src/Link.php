<?php

namespace HalClient;


class Link
{
    private $href;
    private $title;
    private $name;
    private $isTemplate = false;
    private $type;
    private $deprication;
    private $profile;
    private $lang;
    private $rel;
    private $prefix;
    private $documentationUrl;

    static function parse($rel, $object)
    {
        $link = new Link();
        $link->setHref($object['href']);

        if (strpos($rel, ':') !== false) {
            $part = explode(':', $rel, 2);
            $link->setRel($part[1]);
            $link->setPrefix($part[0]);
        } else {
            $link->setRel($rel);
            $link->setPrefix('');
        }

        if (isset($object['name'])) {
            $link->setName($object['name']);
        }
        if (isset($object['title'])) {
            $link->setTitle($object['title']);
        }
        if (isset($object['templated']) && $object['templated']) {
            $link->setIsTemplate(true);
        }
        if (isset($object['type'])) {
            $link->setType($object['type']);
        }
        if (isset($object['deprication'])) {
            $link->setDeprication($object['deprication']);
        }
        if (isset($object['profile'])) {
            $link->setProfile($object['profile']);
        }
        if (isset($object['lang'])) {
            $link->setLang($object['lang']);
        }

        return $link;
    }

    /**
     * @return string
     */
    public function getPrefix()
    {
        return $this->prefix;
    }

    /**
     * @param string $prefix
     */
    public function setPrefix($prefix)
    {
        $this->prefix = $prefix;
    }

    /**
     * @return string
     */
    public function getDocumentationUrl()
    {
        return $this->documentationUrl;
    }

    /**
     * @param string $documentationUrl
     */
    public function setDocumentationUrl($documentationUrl)
    {
        $this->documentationUrl = $documentationUrl;
    }

    /**
     * @return string
     */
    public function getRel()
    {
        return $this->rel;
    }

    /**
     * @param string $rel
     */
    public function setRel($rel)
    {
        $this->rel = $rel;
    }

    /**
     * @return string
     */
    public function getDeprication()
    {
        return $this->deprication;
    }

    /**
     * @return bool
     */
    public function isDepricated()
    {
        return !empty($this->deprication);
    }

    /**
     * @param string $deprication
     */
    public function setDeprication($deprication)
    {
        $this->deprication = $deprication;
    }

    /**
     * @return string
     */
    public function getHref()
    {
        return $this->href;
    }

    /**
     * @param string $href
     */
    public function setHref($href)
    {
        $this->href = $href;
    }

    /**
     * @return boolean
     */
    public function isTemplate()
    {
        return $this->isTemplate;
    }

    /**
     * @param boolean $isTemplate
     */
    public function setIsTemplate($isTemplate)
    {
        $this->isTemplate = $isTemplate;
    }

    /**
     * @return string
     */
    public function getLang()
    {
        return $this->lang;
    }

    /**
     * @param string $lang
     */
    public function setLang($lang)
    {
        $this->lang = $lang;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getProfile()
    {
        return $this->profile;
    }

    /**
     * @param string $profile
     */
    public function setProfile($profile)
    {
        $this->profile = $profile;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }
} 