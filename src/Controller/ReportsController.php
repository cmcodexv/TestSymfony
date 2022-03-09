<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use MongoDB\Client;
use App\Form\SearchType;



class ReportsController extends AbstractController
{
    /**
     * @Route("/reports",name="reports")
     */

    public function index(Request $request,  ParameterBagInterface $params)
    {   
        //get url
        $db_url = $params->get('db_url');
        $conex = new Client($db_url);

        try {

            $collection = $conex->demo_db->accounts;

            //stages of aggregation
            $isActive = [
                '$match' => ['status' => 'ACTIVE']
            ];

            $list = [
                '$lookup' => [
                    "from" => "metrics",
                    "localField" => "accountId",
                    "foreignField" => "accountId",
                    "as" => "metrics"
                ]
            ];

            $un =  [
                '$unwind' => '$metrics'
            ];

            $group =  [
                '$group' => [
                    '_id' => '$accountId',
                    'name' => [
                        '$first' => '$accountName'
                    ],
                    'clicks' => [
                        '$sum' => '$metrics.clicks'
                    ],
                    'impressions' => [
                        '$sum' => '$metrics.impressions'
                    ],
                    'spend' => [
                        '$sum' => '$metrics.spend'
                    ]

                ]
            ];

            $add = [
                '$addFields' => [
                    'costPerClick' => [
                        '$divide' => ['$spend', '$clicks']
                    ]
                ]
            ];

            //form search
            $form = $this->createForm(SearchType::class);
            $form->handleRequest($request);

            //submit form
            if ($form->isSubmitted()) {

                /** @var ClickableInterface $buttonSearch  */
                $buttonSearch = $form->get("searchBtn");

                //button search
                if ($buttonSearch->isClicked()) {

                    $filterValue = $form->get('search')->getData();

                    $filterInput = [
                        '$match' => ['_id' => ''  . $filterValue . '']
                    ];

                    //data filtered
                    $data = $collection->aggregate([$isActive, $list, $un, $group, $filterInput, $add])->toArray();
                    $arrayData = json_decode(json_encode($data));

                    // reset form
                    $form = $this->createForm(SearchType::class);

                    return $this->render('reports/index.html.twig', [
                        'controller_name' => 'ReportsController',
                        'data' => $arrayData,
                        'form' => $form->createView()
                    ]);
                } else {

                    // all data
                    $data = $collection->aggregate([$isActive, $list, $un, $group, $add])->toArray();
                    $arrayData = json_decode(json_encode($data));

                    // reset form
                    $form = $this->createForm(SearchType::class);

                    return $this->render('reports/index.html.twig', [
                        'controller_name' => 'ReportsController',
                        'data' => $arrayData,
                        'form' => $form->createView()
                    ]);
                }
            } else {

                //all data
                $data = $collection->aggregate([$isActive, $list, $un, $group, $add])->toArray();
                $arrayData = json_decode(json_encode($data));

                return $this->render('reports/index.html.twig', [
                    'controller_name' => 'ReportsController',
                    'data' => $arrayData,
                    'form' => $form->createView()
                ]);
            }
        } catch (\Exception $e) {
            echo nl2br("Unable to connect to Database at the moment! \n Error:'.$e.");
            exit();
        }
    }
}
