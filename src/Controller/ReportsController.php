<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use MongoDB\Client;


class ReportsController extends AbstractController
{
    /**
     * @Route("/reports")
     */
    public function index()

    {
        $conex = new Client('mongodb://localhost:27017');
        $collection = $conex->cnx->user;
        $data = $collection->find()->toArray();
        $arrayData = json_decode(json_encode($data));

        // var_dump($array[3]);

        return $this->render('reports/index.html.twig', [
            'controller_name' => 'ReportsController',
            'data' => $arrayData

        ]);
    }
}
