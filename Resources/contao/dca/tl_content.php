<?php
/**
 * Created by PhpStorm.
 * User: felix
 * Date: 27.09.2017
 * Time: 16:33
 */

namespace Home\LibrareeBundle\Resources\contao\dca;
use Home\PearlsBundle\Resources\contao\Helper\Dca as Helper;

$moduleName = 'tl_content';

$tl_content = new Helper\DcaHelper($moduleName);

try{
$tl_content
    #-- search --------------------------------------------------------------------------------------------------------
    ->addField('page','urlLink', array('label'=>''))
    ->addField('page','urlSearch', array('label'=>''))
    ->addField('headline', 'lib_headline')
    #->addField('textarea', 'text')
    ->addField('image', 'image')

    #-- search palette
    ->copyPalette('default', 'search')
    ->addPaletteGroup('search', array('urlSearch'), 'search')

    #-- search result palette
    ->copyPalette('default', 'searchResult')
    ->addPaletteGroup('searchResult', array('lib_nav_table'), 'searchResult')

    #-- search with promoter palette
    ->copyPalette('default', 'searchWithPromoter')
    ->addPaletteGroup('search', array('urlSearch','urlLink','lib_headline','text','image'), 'searchWithPromoter')
;
}catch(\Exception $e){
    var_dump($e);
}
