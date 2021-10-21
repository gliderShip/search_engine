<?php

namespace App\Controller;

use App\Service\Fiskalizimi;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class IndexController extends BaseController
{

    /**
     * @Route("/", name="index")
     */
    public function index(): Response
    {
        return new Response("Hello IntelyCare 😉");
    }

}
