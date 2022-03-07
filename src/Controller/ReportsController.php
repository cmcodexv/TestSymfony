<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use MongoDB\Client;
use MongoDB\BSON\Regex;


class ReportsController extends AbstractController
{
    /**
     * @Route("/reports")
     */
    public function index()

    {
        $conex = new Client('mongodb://localhost:27017');
        $collection = $conex->cnx->user;

        //agregations
        $isActive = [
            '$match' => ['status' => true]
        ];

        $list = [
            '$lookup' => [
                "from" => "statics",
                "localField" => "_id",
                "foreignField" => "userId",
                "as" => "statics"
            ]
        ];

        $un =  [
            '$unwind' => '$statics'

        ];

        $group =  [
            '$group' => [
                '_id' => '$accountId',
                'name' => [
                    '$first' => '$name'
                ],
                'lastname' => [
                    '$first' => '$lastname'
                ],
                'clicks' => [
                    '$sum' => '$statics.clicks'
                ],
                'impressions' => [
                    '$sum' => '$statics.impressions'
                ],
                'spend' => [
                    '$sum' => '$statics.spend'
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

        $sort = [
            '$sort' => [
                '_id' => 1
            ]
        ];

        //data
        $data = $collection->aggregate([$isActive, $list, $un, $group, $add, $sort])->toArray();
        $arrayData = json_decode(json_encode($data));

        return $this->render('reports/index.html.twig', [
            'controller_name' => 'ReportsController',
            'data' => $arrayData
        ]);
    }
}
