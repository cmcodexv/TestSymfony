<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use MongoDB\Client;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;


class ReportsController extends AbstractController
{
    /**
     * @Route("/reports",name="reports")
     */

    public function index(Request $request)
    {


        $conex = new Client('mongodb://localhost:27017');
        $collection = $conex->demo_db->accounts;


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
        $form = $this->createFormBuilder()
            ->add(
                'search',
                TextType::class,
                [
                    "attr" => [
                        "placeholder" => "Account ID"
                    ]
                ]
            )
            ->add('get', SubmitType::class, array('label' => 'GET DATA'))
            ->getForm();


        $form->handleRequest($request);

        if ($form->isSubmitted()) {

            $filterValue = $form->getData();

            // $filter = [
            //     '$match' => [
            //         'accountId' => [
            //             '$regex' => '/^' . $filterValue["search"] . '/'
            //         ]
            //     ]
            // ];
            // $isActive = [
            //     '$match' => ['status' => 'ACTIVE']
            // ];
            // $data = $collection->aggregate([$filter])->toArray();
            // $arrayData = json_decode(json_encode($data));
            // var_dump($arrayData);
            // die();

            // $isActive = [
            //     '$match' => ['status' => 'ACTIVE']
            // ];
            // $findFilter = $collection->find([$filter]);

            // return $this->redirectToRoute('reports');
            return $this->render('reports/index.html.twig', [
                'controller_name' => 'ReportsController',
                'form' => $form->createView(),
            ]);
        } else {
            var_dump("tft");
        }


        //data
        $data = $collection->aggregate([$isActive, $list, $un, $group, $add])->toArray();

        return $this->render('reports/index.html.twig', [
            'controller_name' => 'ReportsController',
            // 'data' => $arrayData,
            'form' => $form->createView(),
        ]);
    }
}
