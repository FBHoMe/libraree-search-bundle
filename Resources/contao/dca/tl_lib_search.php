<?php
/**
 * Created by PhpStorm.
 * User: felix
 * Date: 27.09.2017
 * Time: 16:33
 */

namespace Home\LibrareeSearchBundle\Resources\contao\dca;
use Home\PearlsBundle\Resources\contao\Helper\Dca as Helper;

$moduleName = 'tl_lib_search';

$tl_lib_search = new Helper\DcaHelper($moduleName);

try{
$tl_lib_search
    #-- Config ---------------------------------------------------------------------------------------------------------
    ->addConfig('liste')
    #-- List -----------------------------------------------------------------------------------------------------------
    ->addList('base')
    ->setListSettings('label','label_callback', array(
        'Home\LibrareeSearchBundle\Resources\contao\dca\tl_lib_search','getListLayout'
    ))
    #-- Sorting --------------------------------------------------------------------------------------------------------
    ->addSorting('liste')
    #-- Fields default -------------------------------------------------------------------------------------------------
    ->addField('id', 'id')
    ->addField('tstamp', 'tstamp')
    ->addField('name', 'name')
    ->addField('published', 'published')
    ->addField('alias','alias')
    #-- Fields ---------------------------------------------------------------------------------------------------------
    ->addField('integer', 'count', array(
        'search' => true,
        'sorting' => true,
        'filter' => true,
    ))
    ->addField('text', 'ip')
    #-- Palette --------------------------------------------------------------------------------------------------------
    ->addPaletteGroup('default', array(
        'name',
        'count',
        'tstamp',
        'ip'
    ))
    #-- Operations -----------------------------------------------------------------------------------------------------
    #->addOperation('edit', 'edit', array(),'_first')
    ->addOperation('delete','delete',array(
        'attributes' => 'onclick="if(!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\'))return false;"'
    ))
    ->addOperation('show')
;
}catch(\Exception $e){
    var_dump($e);
}

class tl_lib_search extends \Backend
{
    public function getListLayout($row, $label)
    {
        return 'Gesucht: <strong>' . $row['name'] . '</strong><br>'.
            'Anzahl Ergebnisse: ' . $row['count'] . '<br>'.
            'Zuletzt gesucht: ' . date('d.m.Y H:i:s', $row['tstamp'])
            ;
    }
}
