<?php

/**
 * Created by PhpStorm.
 * User: felix
 * Date: 28.09.2017
 * Time: 09:00
 */

namespace Home\LibrareeSearchBundle\Resources\contao\modules;

use Home\LibrareeSearchBundle\Resources\contao\models\LibSearchModel;


/**
 * Backend module "search".
 *
 * @property array $editable
 *
 */
class SearchBeModule extends \BackendModule
{

	/**
	 * Template
	 * @var string
	 */
	protected $strTemplate = 'mod_be_search';


	/**
	 * Generate the module
	 */
	protected function compile()
	{
        $GLOBALS['TL_JAVASCRIPT'][] = '/bundles/homelibraree/jquery-3.2.1.js|static';
	    $model = LibSearchModel::findAll();

	    if($model instanceof \Contao\Model\Collection){
	        $searched = $model->fetchAll();
            $listAll = array();
            $listMonth = array();

	        if(is_array($searched) && count($searched) > 0){
	            foreach ($searched as $key => $row){
	                if($row['alias'] !== ''){
                        #-- create list month
                        $month = date('Ym', $row['tstamp']);
                        if(array_key_exists($month, $listMonth)){
                            if(array_key_exists($row['alias'], $listMonth[$month]['items'])){
                                $listMonth[$month]['items'][$row['alias']] = array(
                                    'count' => $listMonth[$month]['items'][$row['alias']] ['count'] + 1,
                                    'searched' => $row,
                                    'lastDate' => $row['tstamp'] > $listMonth[$month]['items'][$row['alias']] ['lastDate'] ? $row['tstamp'] : $listMonth[$month]['items'][$row['alias']] ['lastDate']
                                );
                            }else{
                                $listMonth[$month]['items'][$row['alias']] = array(
                                    'count' => 1,
                                    'searched' => $row,
                                    'lastDate' => $row['tstamp']
                                );
                            }
                        }else{
                            $listMonth[$month] = array(
                                'month' => date('m.Y', $row['tstamp']),
                                'items' => array(
                                    $row['alias'] => array(
                                        'count' => 1,
                                        'searched' => $row,
                                        'lastDate' => $row['tstamp']
                                    )
                                )
                            );
                        }

                        #-- create list all
                        if(array_key_exists($row['alias'], $listAll)){
                            $listAll[$row['alias']] = array(
                                'count' => $listAll[$row['alias']]['count'] + 1,
                                'searched' => $row,
                                'lastDate' => $row['tstamp'] > $listAll[$row['alias']]['lastDate'] ? $row['tstamp'] : $listAll[$row['alias']]['lastDate']
                            );
                        }else{
                            $listAll[$row['alias']] = array(
                                'count' => 1,
                                'searched' => $row,
                                'lastDate' => $row['tstamp']
                            );
                        }
                    }
                }

                #-- sort list month
                krsort($listMonth);
                if(count($listMonth) > 0){
                    foreach ($listMonth as $key => $month){
                        $temp = $month;
                        usort($temp['items'], function( $a, $b ){
                            return $b['count'] - $a['count'];
                        });
                        $listMonth[$key] = $temp;
                    }
                }

                #-- sort list all
                usort($listAll, function( $a, $b ){
                    return $b['count'] - $a['count'];
                });
            }

            $this->Template->listMonth = $listMonth;
	        $this->Template->listAll = $listAll;
        }

	}
}
