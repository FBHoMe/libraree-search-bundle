<?php
/**
 * Created by PhpStorm.
 * User: felix
 * Date: 18.09.2017
 * Time: 15:22
 */

namespace Home\LibrareeSearchBundle\Resources\contao\elements;

use Contao\StringUtil;
use Home\CustomizeeBundle\Resources\contao\dca\BasePinDca;
use Home\LibrareeSearchBundle\Resources\contao\models\LibSearchModel;
use Home\PearlsBundle\Resources\contao\Helper as Helper;
use Home\TaxonomeeBundle\Resources\contao\models\TaxonomeeModel;

class SearchResultElement extends \Contao\ContentElement
{
    /**
     * @var string
     */
    protected $strTemplate = 'cte_search_result';

    /**
     * @return string
     */
    public function generate()
    {
        // #-- get file path from url
        $file = \Contao\Input::get('file', true);
        // #-- get file object from path
        $objFile = \FilesModel::findByPath($file);

        // Send the file to the browser and do not send a 404 header (see #4632)
        if ($file != '' && $file == $objFile->path)
        {
            \Contao\Controller::sendFileToBrowser($file);
        }

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
        $this->Template->wildcard   = "### Suchergebnis ###";
    }

    /**
     * generate frontend for module
     */
    private function generateFrontend()
    {
        $GLOBALS['TL_CSS'][] = '/bundles/homelibraree/chosen_v1.8.2/chosen.css';
        // $GLOBALS['TL_CSS'][] = '/bundles/homelibraree/magicsuggest/magicsuggest.css';

        #-- get auto_item
        if ($_GET['search']) {
            $pins = self::getPins(self::getSearchStringFromUrl(), $this->lib_nav_table);
            $this->Template->link = explode('_', $this->lib_nav_table)[1];
            $this->Template->pins = $pins;
            $this->Template->taxonomies = BasePinDca::getTaxonomieFromTable($this->lib_nav_table);

            #-- add search request to log
            LibSearchModel::saveSearchArr(self::getSearchStringFromUrl(), $pins);
        }

    }

    public static function getSearchStringFromUrl() {
        return explode(' ', self::sanitizeFileName($_GET['search']));
    }

    public static function getSearchStringFromString($string) {
        $params = self::getArrayFromUrlString($string);
        return $params['search'];
    }

    private static function getArrayFromUrlString($string)
    {
        parse_str($string, $array);
        return $array;
    }


    public static function getPins($searchArr, $table)
    {
        $return = array();
        $result = array();
        $replace = array();
        $where = false;
        $database = \Database::getInstance();
        #-- array of fields for WHERE clause
        $fieldsArr = array(
            'pin.title',
            'pin.author',
            'pin.keywords',
            'pin.text',
            't.name'
        );
        $publicStatus = ' (pin.publicStatus = "Geheim" OR pin.publicStatus = "Intern" OR pin.publicStatus = "Öffentlich" OR pin.publicStatus = "") ';
        $conjunction = $_GET['conjunction'];

        #-- adding LIKE for every field and search string combination
        foreach ($searchArr as $key => $searchString){
            $tempWhere = array();
            foreach ($fieldsArr as $field){
                $tempWhere[] = $field . ' LIKE ? ';
                $replace[] = '%' . $searchString . '%';
            }
            $tempWhere = implode(' OR ',$tempWhere);

            if($key === 0){
                $where = '(' . $tempWhere . ')';
            }else{
                if($conjunction && $conjunction === 'OR'){
                    $where .= ' OR (' . $tempWhere . ')';
                }else{
                    $where .= ' AND (' . $tempWhere . ')';
                }
            }
        }

        #-- get the member groups of the user
        $user = \Contao\FrontendUser::getInstance();
        #-- get only the pins with users publicStatus (Mitgliedergruppe 4 = Geheim, Mitgliedergruppe 5 = Intern)
        if(!$user->isMemberOf(4)){
            $publicStatus = ' pin.publicStatus != "Geheim"';
            if(!$user->isMemberOf(5)){
                $publicStatus = ' (pin.publicStatus != "Geheim" AND pin.publicStatus != "Intern")';
            }
        }else if(!$user->isMemberOf(5)){
            $publicStatus = ' pin.publicStatus  != "Intern"';
        }

        if($where){
            $sql = '
                SELECT 
                  pin.title, pin.tstamp, pin.id, pin.alias, pin.published, pin.text, pin.teaser, pin.pictures, pin.files, 
                       pin.picture, pin.datum, pin.keywords, pin.keywordsSelectPin
                FROM ' . $table . '_pin AS pin
                LEFT JOIN (' . $table . '_tax_link AS tl , tl_taxonomee AS t) ON pin.id = tl.pin_id AND t.id = tl.taxonomie_id
                WHERE pin.published = 1
                AND (' . $where . ')
                AND ' . $publicStatus . '
                GROUP BY pin.id
                ORDER BY pin.id DESC
            ';

            #print_r($sql);
            #var_dump($replace);
            #exit;

            $result = $database->prepare($sql)->execute($replace);
            #var_dump($result);
            #exit;

            if($result){
                $result = $result->fetchAllAssoc();

                if(is_array($result) && count($result) > 0){
                    $result = Helper\DataHelper::convertValue($result);

                    foreach ($result as $row){
                        if($row['picture']){
                            $row['picture'] = self::getPictureFromUuid($row['picture']);
                        }
                        if($row['pictures'] && is_array($row['pictures']) && count($row['pictures']) > 0){
                            foreach ($row['pictures'] as $key => $picture){
                                $row['pictures'][$key] = self::getPictureFromUuid($picture);
                            }
                        }
                    }
                }
            }
            $return['count'] = count($result);
        }

        $return['pins'] = $result;

        return $return;
    }

    private static function getPictureFromUuid($uuid)
    {
        $file = \FilesModel::findByUuid($uuid);
        if($file instanceof \Contao\FilesModel){

            # --- get picture in backend by size attribute
            $container = \System::getContainer();
            $rootDir = $container->getParameter('kernel.project_dir');

            if(file_exists($rootDir . '/' . $file->path)){
                $picture = $container
                    ->get('contao.image.picture_factory')
                    ->create( $rootDir . '/' . $file->path, 5);

                $picture = array
                (
                    'img' => $picture->getImg($rootDir),
                    'sources' => $picture->getSources($rootDir)
                );

                return $picture['img']['src'];

            }
        }
        return '';
    }

    /**
     * Sanitize a file name
     *
     * @param string $strName The file name
     *
     * @return string The sanitized file name
     */
    public static function sanitizeFileName($strName)
    {
        // Remove invisible control characters and unused code points
        $strName = preg_replace('/[\pC]/u', '', $strName);

        // Remove special characters
        $strName = str_replace(array('\\', '/', ':', '*', '?', '"', '<', '>', '|', '&', '$', '§', '%', '!', '(', ')',
            '{', '}', '^', '°', '#'), '', $strName);

        return $strName;
    }
}