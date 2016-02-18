<?php

namespace Miguelpelota;

class InstagramHarvest
{
    /**
     * @var string
     */
    private $access_token;

    /**
     * @var string
     */
    private $url;

    /**
     * @var string
     */
    private $hashtag;

    /**
     * @var string
     */
    private $min_tag_id;

    /**
     * @var array
     */
    private $images = array();

    /**
     * @var string
     */
    private $min_tag_id_new;

    /**
     * @param string $hashtag
     * @param string $access_token
     */
    public function __construct($hashtag, $access_token, $min_tag_id)
    {
        $this->hashtag = $hashtag;
        $this->access_token = $access_token;
        $this->min_tag_id = $min_tag_id;

        $this->url = 'https://api.instagram.com/v1/tags/'.$hashtag.
            '/media/recent?access_token='.$access_token;
    }

    /**
     * @return array
     */
    public function fetchImages()
    {
        $url = $this->url;

        $i = 0;

        while ($url) {
            $result = $this->_callApi($url);

            foreach ($result->data as $image) {
                $image_id = explode('_', $image->id);
                $image_id = $image_id[0];

                if ($this->min_tag_id && (integer)$image->id > (integer)$this->min_tag_id) {
                    $this->images[] = $image;
                } elseif (!$this->min_tag_id) {
                    $this->images[] = $image;
                }
            }

            $min_tag_id_new = $result->pagination->min_tag_id;

            if ($i == 0) {
                $this->min_tag_id_new = $min_tag_id_new;
            }

            if ($this->min_tag_id && $min_tag_id_new <= $this->min_tag_id) {
                break;
            }

            if (!isset($result->pagination->next_url)) {
                break;
            }

            $url = $result->pagination->next_url;

            // if ($i == 0) {
            //     break;
            // }

            ++$i;
        }

        return $this->images;
    }

    /**
     * @return string
     */
    public function getNewMinTagId()
    {
        return $this->min_tag_id_new;
    }


    /**
     * @param string $url
     */
    private function _callApi($url)
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);

        $result = curl_exec($ch);

        curl_close($ch);

        $result = json_decode($result);

        return $result;
    }
}
