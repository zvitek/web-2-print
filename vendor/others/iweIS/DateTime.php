<?php

class DateTimeFormat {

    /**
     * From format 12. 01. 2014 00:23:44 -> 2014-01-12 00:23:44
     * @param $dateTime
     * @return null|string
     */
    public static function toDatabase($dateTime)
    {
        $dividedTime = explode(' ', str_replace('.', '', $dateTime));
        $time =  (count($dividedTime) == 4) ? end($dividedTime) : '00:00:00';
        $date = $dividedTime[2] . '-' . $dividedTime[1] . '-' . $dividedTime[0];

        if(in_array(count($dividedTime), array(3,4)))
            return $date . ' ' . $time;
        else
            return NULL;
    }

    public static function toView($dateTime)
    {
        $dividedTime = explode(' ', $dateTime);
        $dividedDate = explode('-', $dividedTime[0]);

        $date = $dividedDate[2] . '. ' . $dividedDate[1] . '. ' . $dividedDate[0] . ' ' . $dividedTime[1];

        return $date;
    }

}