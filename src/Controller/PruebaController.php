<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use MongoDB\Client;

class PruebaController
{
    /**
     * @Route("/")
     */

    public function homepage()
    {


        $cnx = new Client('mongodb://localhost:27017');
        $col = $cnx->storedb->users;
        $resp = $col->findOne(['username' => 'Jose']);
        var_dump(($resp));
        return new Response("de");
       }
}
