<?php

/**
 * Author: Michal Schweichler
 * email: michal.schweichler[at]gmail.com
 * www: http://michalschweichler.co.uk
 *
 * Date: 13/06/14
 * Time: 20:30
 */
class SeoAnalyser
{
    private $url; // string
    private $httpResponse; // obj->code | obj->header | obj->body
    private $dom; // DOMDocument
    private $xpath; // DOMXpath
    private $title; // string
    private $description; // string
    private $keywords; // string
    private $robots; // string
    private $charset; // string
    private $links; // array('external' => array(), 'internal' => array())

    function __construct($url)
    {
        $this->url = $this->validateUrl($url);
        $this->httpResponse = $this->getServerResponse($this->getUrl());
        $this->dom = $this->htmlToDom($this->getHtml());
        $this->xpath = $this->domToXpath($this->getDom());
    }

    public function getUrl()
    {
        if (!empty($this->url)) {
            return $this->url;
        } else {
            return null;
        }
    }

    public function getHtml()
    {
        if (!empty($this->httpResponse->body)) {
            return $this->httpResponse->body;
        } else {
            return null;
        }
    }

    public function getHttpResponse()
    {
        if (!empty($this->httpResponse)) {
            return $this->httpResponse;
        } else {
            return array('code' => null, 'header' => null, 'body' => null);
        }
    }

    public function getDom()
    {
        if (!empty($this->dom)) {
            return $this->dom;
        } else {
            $this->dom = new DOMDocument();
            return $this->dom;
        }
    }

    public function getXpath()
    {
        if (!empty($this->xpath)) {
            return $this->xpath;
        } else {
            $this->xpath = new DOMXPath($this->getDom());
            return $this->xpath;
        }
    }

    public function getAllLinks()
    {
        return array_merge($this->getExternalLinks(), $this->getInternalLinks());
    }

    public function getTitle()
    {
        if (!empty($this->title)) {
            return $this->title;
        } else {
            $title = $this->getXpath()->query('//title');
            if ($title->length > 0) {
                $this->title = ($value = $title->item(0)) ? $value->nodeValue : null;
                return $this->title;
            } else {
                $this->title = null;
                return $this->title;
            }
        }
    }

    public function getDescription()
    {
        if (!empty($this->description)) {
            return $this->description;
        } else {
            $description = $this->getXpath()->query('//meta[@name="description"]');
            if ($description->length > 0) {
                $this->description = ($value = $description->item(0)->attributes->getNamedItem(
                    'content'
                )) ? $value->nodeValue : null;
                return $this->description;
            } else {
                $this->description = null;
                return $this->description;
            }
        }
    }

    public function getKeywords()
    {
        if (!empty($this->keywords)) {
            return $this->keywords;
        } else {
            $keywords = $this->getXpath()->query('//meta[@name="keywords"]');
            if ($keywords->length > 0) {
                $this->keywords = ($value = $keywords->item(0)->attributes->getNamedItem(
                    'content'
                )) ? $value->nodeValue : null;
                return $this->keywords;
            }
            $this->keywords = null;
            return $this->keywords;
        }
    }

    public function getRobots()
    {
        if (!empty($this->robots)) {
            return $this->robots;
        } else {
            $robots = $this->getXpath()->query('//meta[@name="robots"]');
            if ($robots->length > 0) {
                $this->robots = ($value = $robots->item(0)->attributes->getNamedItem(
                    'content'
                )) ? $value->nodeValue : null;
                return $this->robots;
            }
            $this->robots = null;
            return $this->robots;
        }
    }

    public function getCharset()
    {
        if (!empty($this->charset)) {
            return $this->charset;
        } else {
            $charset = $this->getXpath()->query('//meta[@charset]');
            if ($charset->length > 0) {
                $this->charset = ($value = $charset->item(0)->attributes->getNamedItem(
                    'charset'
                )) ? $value->nodeValue : null;
                return $this->charset;
            } else {
                $charset = $this->getXpath()->query(
                    '//meta[@http-equiv[contains(translate(.,"CONTENT-TYPE","content-type"), "content-type")]]'
                );
                if ($charset->length > 0) {
                    $content = ($value = $charset->item(0)->attributes->getNamedItem(
                        'content'
                    )) ? $value->nodeValue : null;
                    if (!empty($content)) {
//                        preg_match('/charset=(.*)/', $content, $match);
//                        $this->charset = $match[1];
                        if ($pos = stripos($content, 'charset=')) {
                            $this->charset = ($value = substr(
                                $content,
                                $pos + 8
                            )) ? $value : null; // ~10x faster than preg_match
                            return $this->charset;
                        }
                    }
                }
            }
        }
        //var_dump($this->getHttpResponse()->header); // TODO get charset from http response?
        $this->charset = null;
        return $this->charset;
    }

    public function getExternalLinks()
    {
        if (!empty($this->links['external'])) {
            return $this->links['external'];
        } else {
            foreach ($this->getXpath()->query('//a[@href]') as $item) {
                $href = ($value = $item->attributes->getNamedItem('href')) ? $value->nodeValue : null;
                $link = parse_url($href);
                if ($link !== false && !empty($link['host'])
                    && $link['host'] != parse_url($this->getUrl(), PHP_URL_HOST)
                    && $link['host'] != 'www.' . parse_url($this->getUrl(), PHP_URL_HOST)
                    && $link['host'] != substr(parse_url($this->getUrl(), PHP_URL_HOST), 4)
                ) {
                    $this->links['external'][] = $href;
                }
            }
            return (!empty($this->links['external']) ? $this->links['external'] : array());
        }
    }

    public function getInternalLinks()
    {
        if (!empty($this->links['internal'])) {
            return $this->links['internal'];
        } else {
            foreach ($this->getXpath()->query('//a[@href]') as $item) {
                $href = ($value = $item->attributes->getNamedItem('href')) ? $value->nodeValue : null;
                $link = parse_url($href);
                if ($link !== false) {
                    if (empty($link['host'])) {
                        if (!empty($link['path']) && $link['path'] != 'void(0);') {
                            $this->links['internal'][] = $href;
                        }
                    } else {
                        if ($link['host'] == parse_url($this->getUrl(), PHP_URL_HOST)
                            || $link['host'] == 'www.' . parse_url($this->getUrl(), PHP_URL_HOST)
                            || $link['host'] == substr(parse_url($this->getUrl(), PHP_URL_HOST), 4)
                        ) {
                            $this->links['internal'][] = $href;
                        }
                    }
                }
            }
            return (!empty($this->links['internal']) ? $this->links['internal'] : array());
        }
    }

    private function validateUrl($url)
    {
        $url = (strpos($url, '://') === false) ? 'http://' . $url : $url;

        if (filter_var($url, FILTER_VALIDATE_URL) !== false) {
            return $url;
        } else {
            return false;
        }
    }

    private function getServerResponse($url)
    {
        $httpResponse = array('code' => null, 'header' => null, 'body' => null);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 20);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CAINFO, dirname(__FILE__) . '/cacert.pem'); // for outdated curl SSL
//        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0); // not for production!
//        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); // not for production!

        if ($response = curl_exec($ch)) {
            $headerLength = curl_getinfo($ch, CURLINFO_HEADER_SIZE);

            $httpResponse['code'] = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $httpResponse['header'] = substr($response, 0, $headerLength);
            $httpResponse['body'] = substr($response, $headerLength);
        }

        curl_close($ch);
        return (object)$httpResponse;
    }

    private function htmlToDom($html)
    {
        $dom = new DOMDocument();
        if (!empty($html)) {
            $dom->loadHTML($html);
        }
        return $dom;
    }

    private function domToXpath($dom)
    {
        if (get_class($dom) == 'DOMDocument') {
            return new DOMXPath($dom);
        } else {
            return new DOMXPath(new DOMDocument());
        }
    }
} 