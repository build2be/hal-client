<?php
/**
 * Created by PhpStorm.
 * User: martijn
 * Date: 10-7-14
 * Time: 13:30
 */

namespace HalClient;


class LinkCollection {
    private $links = array();
    private $curies = array();

    public function addCurie(Link $curie){
        $this->curies[$curie->getName()] = $curie;
    }

    public function addLink(Link $link){
        $this->links[$link->getRel()] = $link;
    }

    public function addMultiLink(Link $link){
            $this->links[$link->getRel()][] = $link;
            $this->links[$link->getRel()][$link->getName()] = $link;
    }

    public function getLink($rel){
        if(strpos($rel, '/') !== false){
            $part = explode('/', $rel, 2);
            if (isset($this->links[$part[0]][$part[1]])) {
                return $this->links[$part[0]][$part[1]];
            }
        }else {
            if (isset($this->links[$rel])) {
                return $this->links[$rel];
            }
        }
        throw new \InvalidArgumentException('Link "' . $rel . ' not found."');
    }

    public function hasLink($rel){
        $rel = explode('/', $rel, 2);
        if (count($rel) == 1) {
            return isset($this->links[$rel[0]]);
        } else {
            if(is_array($this->links[$rel[0]])){
                return isset($this->links[$rel[0]][$rel[1]]);
            }else{
                throw new \InvalidArgumentException('Invalid index "' . $rel[1] . '" in "' . $rel[0] . '".');
            }
        }
    }
} 