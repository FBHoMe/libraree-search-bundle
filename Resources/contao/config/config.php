<?php
/**
 * Created by PhpStorm.
 * User: felix
 * Date: 19.01.2018
 * Time: 13:15
 */

#-- add backend modules ------------------------------------------------------------------------------------------------
array_insert($GLOBALS['BE_MOD']['content'], 3 ,[
    'lib_search' => [
        'tables' => ['tl_lib_search'],
        'table' => ['TableWizard', 'importTable'],
        'list' => ['ListWizard', 'importList'],
    ],
    'lib_search_overview' => [
        'callback' => 'Home\LibrareeSearchBundle\Resources\contao\modules\SearchBeModule',
    ]
]);


#-- add content elements -----------------------------------------------------------------------------------------------
array_insert($GLOBALS['TL_CTE'], 2, array
(
    'libraree' => array
    (
        'search'                => 'Home\LibrareeSearchBundle\Resources\contao\elements\SearchElement',
        'searchWithPromoter'    => 'Home\LibrareeSearchBundle\Resources\contao\elements\SearchWithPromoterElement',
        'searchResult'          => 'Home\LibrareeSearchBundle\Resources\contao\elements\SearchResultElement',
    )
));

#-- add models ---------------------------------------------------------------------------------------------------------
$GLOBALS['TL_MODELS']['tl_lib_search'] = 'Home\LibrareeSearchBundle\Resources\contao\models\LibSearchModel';
