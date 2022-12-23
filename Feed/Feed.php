<?php

namespace MauticPlugin\MauticAdvancedTemplatesBundle\Feed;

use Mautic\LeadBundle\Entity\Lead;
use Mautic\LeadBundle\Model\LeadModel;

class Feed
{
    /** @var  string */
    private $feed;

    /** @var \SimpleXMLElement  */
    private $rss;

    private $json;

    public function __construct($feed)
   {
       $this->feed = $feed;

       $this->loadContent($feed);
   }

   public function loadContent($feed) {

        // Create a stream
        $opts = array(
            'http'=>array(
            'method'=>"GET",
            'header'=>"Accept-language: de\r\n" 
            )
        );
        
        $context = stream_context_create($opts);
        
        // Open the file using the HTTP headers set above
        $content = file_get_contents($feed, false, $context);
        $headers = implode("\n", $http_response_header);

        //check if the stream ist json encoded
        if (preg_match_all("/^content-type\s*:\s*(.*)$/mi", $headers, $matches)) {
            $content_type = end($matches[1]);

            if (strpos($content_type, 'json') > 0) {
                $this->json = json_decode($content);
                $this->rss = false;
                return;
            }
            
        }

        $this->json = false;
        $this->rss = simplexml_load_string($content);
   }


    /**
     * @return array
     */
    public function getItems()
    {
        if($this->json)
        {
            return $this->getJson();
        }

        return $this->getItemsXML();
    }

    /**
     * @return array
     */
    public function getJson()
    {
        return $this->json;
    }

    /**
     * @return \SimpleXMLElement
     */
    public function getItemsXML(): \SimpleXMLElement
    {
        return $this->rss->channel->item;
    }
}
