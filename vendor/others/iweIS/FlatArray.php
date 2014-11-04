<?php

class FlatArray
{
    /* @var array */
    private $meArray;

    /* @var array */
    private $flatArray;

    /* @var bool */
    private $keyValue;

    public function __construct($array = array())
    {
        $this->meArray = $array;
    }

    public function getArray($keyValue = FALSE)
    {
        $this->keyValue = $keyValue;
        $this->prepareData($this->meArray);
        return $this->flatArray;
    }

    public function prepareData($data)
    {
        foreach($data as $item)
        {
            if(array_key_exists('data', $item))
                $nData = $item['data'];
            else
                $nData = array();

            unset($item['data']);

            if($this->keyValue)
                $this->flatArray[$item['id']] = $item['name'];
            else
                $this->flatArray[$item['id']] = $item;

            if(count($nData))
                $this->prepareData($nData);
        }
    }

}
