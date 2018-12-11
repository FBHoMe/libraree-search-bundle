<?php
/**
 * Created by PhpStorm.
 * User: felix
 * Date: 19.09.2017
 * Time: 09:52
 */

namespace Home\LibrareeSearchBundle\Resources\contao\models;

class LibSearchModel extends \Contao\Model
{
    /**
     * Table name
     * @var string
     */
    protected static $strTable = 'tl_lib_search';

    public static function saveSearchArr($searchArr, $pins)
    {
        if(is_array($searchArr) && count($searchArr) > 0){
            $searchStr = implode(' ', $searchArr);
            $results = 0;
            if(is_array($pins) && count($pins) > 0){
                $recipies = $pins['recipeCount'] ? $pins['recipeCount'] : 0;
                $stories = $pins['storyCount'] ? $pins['storyCount'] : 0;
                $results = $recipies + $stories;
            }

            $alias = \StringUtil::decodeEntities($searchStr);
            $alias = \StringUtil::restoreBasicEntities($alias);
            $alias = \StringUtil::standardize($alias);

            #-- create new search entry
            $searchEntry = new LibSearchModel();
            $searchEntry->__set('name', $searchStr);
            $searchEntry->__set('alias', $alias);
            $searchEntry->__set('count', $results);
            $searchEntry->__set('tstamp', time());
            $searchEntry->__set('ip', self::getUserIP());
            $searchEntry->save();

        }
    }

    public static function getUserIP()
    {
        // Get real visitor IP behind CloudFlare network
        if (isset($_SERVER["HTTP_CF_CONNECTING_IP"])) {
            $_SERVER['REMOTE_ADDR'] = $_SERVER["HTTP_CF_CONNECTING_IP"];
            $_SERVER['HTTP_CLIENT_IP'] = $_SERVER["HTTP_CF_CONNECTING_IP"];
        }
        $client  = @$_SERVER['HTTP_CLIENT_IP'];
        $forward = @$_SERVER['HTTP_X_FORWARDED_FOR'];
        $remote  = $_SERVER['REMOTE_ADDR'];

        if(filter_var($client, FILTER_VALIDATE_IP))
        {
            $ip = $client;
        }
        elseif(filter_var($forward, FILTER_VALIDATE_IP))
        {
            $ip = $forward;
        }
        else
        {
            $ip = $remote;
        }

        return $ip;
    }
}