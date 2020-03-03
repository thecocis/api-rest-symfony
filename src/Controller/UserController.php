<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

use App\Entity\User;
use App\Entity\Event;


class UserController extends AbstractController
{
    //Método para sacar el json serializado
    private function resjson($data){
        // Serializar datos con servicio de serializer
        $json = $this->get('serializer')->serialize($data, 'json');
        // Response con httpfoundation
        $response = new Response();
        // Asignar contenido a la respuesta
        $response->setContent($json);
        // Indicar formato de respuesta
        $response->headers->set('Content-Type', 'application/json');
        // Devolver la respuesta
        return $response;
    }


    public function index()
    {

        
        $user_repo = $this->getDoctrine()->getRepository(User::class);
        $event_repo = $this->getDoctrine()->getRepository(Event::class);

        $users = $user_repo->findAll();

        $user = $user_repo->find(1);

        $events = $event_repo->findAll();

        $data = [
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/UserController.php',
        ];

        /*
        foreach($users as $user){
            echo "<h1>{$user->getName()} {$user->getSurname()}</h1>";
        }
        
        die();
        */
        return $this->resjson($events);
    }
}
