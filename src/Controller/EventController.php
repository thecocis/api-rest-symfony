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
        $event_repo = $this->getDoctrine()->getRepository(Event::class);
        $events = $event_repo->findAll();

        return $this->json([
            'message' => 'Welcome to your new controller! PD. Deberias cambiar este mensaje, vago',
            'path' => 'src/Controller/EventController.php',
            'events' => $events
            
        ]);
    }

    public function create(Request $request, JwtAuth $jwt_auth, $id = null){
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
                $latitude = (!empty($params->latitude)) ? $params->latitude : null;
                $longitude = (!empty($params->longitude)) ? $params->longitude : null;

                if(!empty($user_id) && !empty($title)){
                    // Guardar el nuevo evento en la BBDD
                    $em = $this->getDoctrine()->getManager();
                    $user = $this->getDoctrine()->getRepository(User::class)->findOneBy([
                        'id' => $user_id
                    ]);

                    if ($id == null){     // NUEVO EVENTO
                        // Crear y guardar objeto
                        $event = new Event();
                        $event->setUser($user);
                        $event->setTitle($title);
                        $event->setDescription($description);
                        $event->setUrl($url);
                        $event->setStatus(0);
                        $event->setPrice($price);
                        $event->setLatitude($latitude);
                        $event->setLongitude($longitude);
                    

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
                            'message' => 'El evento se ha creado correctamente',
                            'event' => $event
                        ];
                    }else{            //EVENTO EXISTENTE, por lo tanto  lo modificamos

                        $event = $this->getDoctrine()->getRepository(Event::class)->findOneBy([
                            'id' => $id
                            //'user' => $identity->sub
                        ]);

                        if ($event && is_object($event)){
                            $event->setTitle($title);
                            $event->setDescription($description);
                            $event->setUrl($url);
                            $event->setStatus(0);
                            $event->setPrice($price);
                            $event->setLatitude($latitude);
                            $event->setLongitude($longitude);
                            
                            $realDate = new \Datetime($date);
                            $event->setDate($realDate);

                            $em->persist($event);
                            $em->flush();

                            $data = [
                                'status' => 'success',
                                'code' => 200,
                                'message' => 'El evento se ha actualizado correctamente',
                                'event' => $event
                            ];
                        }
                    }
                }
            }
        }

        // Devolver una respuesta
        return $this->resjson($data);
    }

    //Retorna los eventos del usuario
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
                'eventos' => $pagination,
                'user' => $identity->sub
            );
        }else{
            // Si falla devolver esto:
            $data = array(
                'status' => 'error',
                'code' => 404,
                'message' => 'No se pueden listar los eventos en este momento',
                'authcheck' => $authCheck
            );
        }
        return $this->resjson($data);
    }

    //Retorna TODOS los eventos
    public function allEventos(Request $request, JwtAuth $jwt_auth, PaginatorInterface $paginator){
        // Recoger la cabecera de autenticación
        $token = $request->headers->get('Authorization');

        // Comprobar el token
        $authCheck = $jwt_auth->checkToken($token);

        if($authCheck){
            // Conseguir la identidad del usuario
            $identity = $jwt_auth->checkToken($token, true);

            $em = $this->getDoctrine()->getManager();

            // Hacer una consulta para paginar
            $dql = "SELECT e FROM App\Entity\Event e ORDER BY e.id DESC";
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
                'eventos' => $pagination,
                'user que lo solicita' => $identity->sub
            );
        }else{
            // Si falla devolver esto:
            $data = array(
                'status' => 'error',
                'code' => 404,
                'message' => 'No se pueden listar los eventos en este momento',
                'authcheck' => $authCheck
            );
        }
        return $this->resjson($data);
    }

    //Retorna 1 evento
    public function event(Request $request, JwtAuth $jwt_auth, $id = null){
        // Salida por defecto
        $data = [
            'status' => 'error',
            'code' => 404,
            'message' => 'Evento no encontrado',
        ];

        // Sacar el token y comprobar si es correcto
        $token = $request->headers->get('Authorization');
        $authCheck = $jwt_auth->checkToken($token);

        if ($authCheck){

            // Sacar la identidad del usuario
            $identity = $jwt_auth->checkToken($token, true);

            // Sacar el objeto del evento en base al id
            $event = $this->getDoctrine()->getRepository(Event::class)->findOneBy([
                'id' => $id
            ]);

            // Comprobar si el evento existe y es propiedad del usuario identificado
            if ($event && is_object($event)){        
                $data = [
                    'status' => 'success',
                    'code' => 200,
                    'event' => $event
                ];
            }
        }
        return $this->resjson($data);
    }

    //Elimina 1 evento
    public function remove(Request $request, JwtAuth $jwt_auth, $id = null){
        $token = $request->headers->get('Authorization');
        $authCheck = $jwt_auth->checkToken($token);

        // Salida por defecto
        $data = [
            'status' => 'error',
            'code' => 404,
            'message' => 'Evento no encontrado o sesion inválida',
            'authcheck' => $authCheck
        ];

        if ($authCheck){
            $identity = $jwt_auth->checkToken($token, true);
            $doctrine = $this->getDoctrine();
            $em = $doctrine->getManager();
            $event = $doctrine->getRepository(Event::class)->findOneBy([
                'id'=>$id
            ]);

            if ($event && is_object($event) && $identity->sub == $event->getUser()->getId()){
                $em->remove($event);
                $em->flush();

                $data = [
                    'status' => 'success',
                    'code' => 200,
                    'event' => $event
                ];
            }
        }

        return $this->resjson($data);
    }

    public function search(Request $request, JwtAuth $jwt_auth, $search = null){
        $token = $request->headers->get('Authorization');
        $authCheck = $jwt_auth->checkToken($token);

        // Salida por defecto
        $data = [
            'status' => 'error',
            'code' => 404,
            'message' => 'Evento no encontrado o sesion inválida',
            'authcheck' => $authCheck
        ];

        if ($authCheck){
            $identity = $jwt_auth->checkToken($token, true);
            $em = $this->getDoctrine()->getManager();

            //Filtro
            $filter = $request->get('filter', null);
            if (empty($filter)) {
                $filter = null;
            }elseif ($filter == 1) {
                $filter = 'disponible';
            }elseif ($filter == 2) {
                $filter = 'lleno';
            }else {
                $filter = 'acabado';
            }

            //Orden
            $order = $request->get('order', null);
            if (empty($order) || $order == 2) {
                $order = 'DESC';
            }else {
                $order = 'ASC';
            }

            //Búsqueda
            if($search != null){
                $dql = "SELECT e FROM App\Entity\Event e "
                        ."WHERE e.user = {$identity->sub} AND "
                        ."(e.title LIKE :search OR e.description LIKE :search)";
            }else {
                $dql = "SELECT e FROM App\Entity\Event e "
                        ."WHERE e.user = {$identity->sub}";
            }

            //Set filter
            if ($filter != null) {
                $dql .= "AND e.status = :filter"; 
            }

            //Set order
            $dql .= " ORDER BY e.id $order";

            //Create query
            $query = $em->createQuery($dql);

            //Set parameter filter
            if ($filter != null) {
                $query->setParameter('filter', "$filter");
            }

            //Set parameter search
            if (!empty($search)) {
                $query->setParameter('search',"%$search%");
            }

            $events = $query->getResult();

            $data = [
                'status' => 'success',
                'code' => 200,
                'message' => 'Resultado de la búsqueda',
                'events' => $events
            ];
        }

        return $this->resjson($data);
    }

}
