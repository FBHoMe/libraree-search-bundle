<?php

namespace Home\LibrareeSearchBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Home\LibrareeSearchBundle\Resources\contao\elements\SearchResultElement as SearchResult;

class SearchController extends Controller
{
    /**
     * @param $searchStr
     * @param $type
     * @param $filter
     *
     * @Route("/search/{searchStr}/{type}/{filter}", name="lib_search")
     *
     * @return JsonResponse
     */
    public function searchAction($searchStr, $type, $filter)
    {
        $this->container->get('contao.framework')->initialize();

        $searchArr = explode(' ', $searchStr);
        $filterObj = json_decode($filter);

        $result = SearchResult::getPins($searchArr, $filterObj, $type);

        return new JsonResponse($result);
    }
}
