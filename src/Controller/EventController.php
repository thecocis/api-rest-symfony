<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Date;

use Knp\Component\Pager\PaginatorInterface;

use App\Entity\User;
use App\Entity\Event;
use App\Services\JwtAuth;

class EventController extends AbstractController
{   
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
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/EventController.php',
        ]);
    }

    public function create(Request $request, JwtAuth $jwt_auth){
        $data = [
            'status' => 'error',
            'code' => 400,
            'message' => 'El evento no ha podido crearse'
        ];

        // Recoger el token
        $token = $request->headers->get('Authorization', null);

        // Comprobar si es correcto
        $authCheck = $jwt_auth->checkToken($token);


        $data = [
            'status' => 'error',
            'code' => 400,
            'token' => $authCheck
        ];

        if($authCheck){
            // Recoger datos por post
            $json = $request->get('json', null);
            $params = json_decode($json);

            // Recoger el objeto del usuario identificado
            $identity = $jwt_auth->checkToken($token, true);


            // Comprobar y validar datos
            if(!empty($json)){

                $user_id = ($identity->sub != null) ? $identity->sub : null;
                $title = (!empty($params->title)) ? $params->title : null;
                $description = (!empty($params->description)) ? $params->description : null;
                $url = (!empty($params->url)) ? $params->url : null;
                $price = (!empty($params->price)) ? $params->price : null;
                $date = (!empty($params->date)) ? $params->date : null;

                if(!empty($user_id) && !empty($title)){
                    // Guardar el nuevo evento en la BBDD
                    $em = $this->getDoctrine()->getManager();
                    $user = $this->getDoctrine()->getRepository(User::class)->findOneBy([
                        'id' => $user_id
                    ]);

                    // Crear y guardar objeto
                    $event = new Event();
                    $event->setUser($user);
                    $event->setTitle($title);
                    $event->setDescription($description);
                    $event->setUrl($url);
                    $event->setStatus(0);
                    $event->setPrice($price);
                   

                    $createdAt = new \Datetime('now');
                    $event->setCreatedAt($createdAt);
                    
                    $realDate = new \Datetime($date);
                    $event->setDate($realDate);

                    // Guardar en la BBDD
                    $em->persist($event);
                    $em->flush();

                    $data = [
                        'status' => 'success',
                        'code' => 200,
                        'message' => 'El event se ha creado correctamente',
                        'event' => $event
                    ];
                }
            }
        }

        // Devolver una respuesta
        return $this->resjson($data);
    }

    public function eventos(Request $request, JwtAuth $jwt_auth, PaginatorInterface $paginator){
        // Recoger la cabecera de autenticación
        $token = $request->headers->get('Authorization');

        // Comprobar el token
        $authCheck = $jwt_auth->checkToken($token);

        if($authCheck){
            // Conseguir la identidad del usuario
            $identity = $jwt_auth->checkToken($token, true);

            $em = $this->getDoctrine()->getManager();

            // Hacer una consulta para paginar
            $dql = "SELECT e FROM App\Entity\Event e WHERE e.user = {$identity->sub} ORDER BY e.id DESC";
            $query = $em->createQuery($dql);

            // Recoger el parametro page de la url
            $page = $request->query->getInt('page', 1);
            $items_per_page = 5;
            
            // Invocar paginación
            $pagination = $paginator->paginate($query, $page, $items_per_page);
            $total = $pagination->getTotalItemCount();

            // Preparar array de datos para devolver
            $data = array(
                'status' => 'success',
                'code' => 200,
                'total_items_count' => $total,
                'page_actual' => $page,
                'itemps_per_page' => $items_per_page,
                'total_page' => ceil($total / $items_per_page),
                'videos' => $pagination,
                'user' => $identity->sub
            );
        }else{
            // Si falla devolver esto:
            $data = array(
                'status' => 'error',
                'code' => 404,
                'message' => 'No se pueden listar los eventos en este momento'
            );

        }






        return $this->resjson($data);
    }
}
