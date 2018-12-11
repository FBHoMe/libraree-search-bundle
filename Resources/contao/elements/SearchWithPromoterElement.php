<?php
/**
 * Created by PhpStorm.
 * User: felix
 * Date: 18.09.2017
 * Time: 15:22
 */

namespace Home\LibrareeSearchBundle\Resources\contao\elements;

use Home\PearlsBundle\Resources\contao\Helper as Helper;

class SearchWithPromoterElement extends \ContentElement
{
    /**
     * @var string
     */
    protected $strTemplate = 'cte_search_promoter';

    /**
     * @return string
     */
    public function generate()
    {
        return parent::generate();
    }

    /**
     * generate module
     */
    protected function compile()
    {
        if (TL_MODE == 'BE') {
            $this->generateBackend();
        } else {
            $this->generateFrontend();
        }
    }

    /**
     * generate backend for module
     */
    private function generateBackend()
    {
        $this->strTemplate          = 'be_wildcard';
        $this->Template             = new \BackendTemplate($this->strTemplate);
        $this->Template->title      = $this->headline;
        $this->Template->wildcard   = "### Suche ###";
    }

    /**
     * generate frontend for module
     */
    private function generateFrontend()
    {
        #-- get auto_item
        if (!isset($_GET['item']) && \Config::get('useAutoItem') && isset($_GET['auto_item'])) {
            \Input::setGet('item', \Input::get('auto_item'));
        }

    }


}