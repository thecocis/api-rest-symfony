<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

use Knp\Component\Pager\PaginatorInterface;

use App\Entity\User;
use App\Entity\Event;
use App\Entity\Participant;
use App\Services\JwtAuth;

class ParticipantController extends AbstractController
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
        $participant_repo = $this->getDoctrine()->getRepository(Participant::class);
        $participants = $participant_repo->findAll();

        return $this->json([
            'message' => 'Welcome to your new controller! PD. Deberias cambiar este mensaje, vago',
            'path' => 'src/Controller/CommentController.php',
            'participants' => $participants
            
        ]);
    }

    public function newParticipant(Request $request, JwtAuth $jwt_auth){
        $data = [
            'status' => 'error',
            'code' => 400,
            'message' => 'La participación no ha podido crearse'
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

                $event_id = (!empty($params->event_id)) ? $params->event_id : null;
                $user_id = (!empty($params->user_id)) ? $params->user_id : null;

                $verification = $this->getDoctrine()->getRepository(Participant::class)->findOneBy([
                    'user' => $user_id,
                    'event_id' => $event_id
                ]);

                if ($verification && is_object($verification)){
                    $data = [
                        'status' => 'accepted',
                        'code' => 200,
                        'message' => 'El solicitante ya participa en este evento!',
                        'verification' => $verification
                    ];

                }elseif (!empty($event_id) && !empty($user_id)){
                    // Guardar el nuevo participant en la BBDD
                    $em = $this->getDoctrine()->getManager();
                    $user = $this->getDoctrine()->getRepository(User::class)->findOneBy([
                        'id' => $user_id
                    ]);

                    $event = $this->getDoctrine()->getRepository(Event::class)->findOneBy([
                        'id' => $event_id
                    ]);
                    
                    if ($event->getActualCapacity()>0) {
                        // NUEVO participant
                        // Crear y guardar objeto
                        $participant = new Participant();
                        $participant->setUser($user);
                        $participant->setEvent($event_id);            

                        // Guardar en la BBDD
                        $em->persist($participant);
                        $em->flush();

                        //Consulta para saber las participaciones totales de un evento
                        $dql_0 = "SELECT COUNT(p.id) FROM App\Entity\Participant p WHERE p.event_id = {$event_id}";
                        $query_0 = $em->createQuery($dql_0);
                        $eventParticipations = $query_0->getSingleScalarResult();

                        $maxParticipations = $event->getMaxCapacity();
                        $actualCapacity = $maxParticipations - $eventParticipations;
                        $event->setActualCapacity($actualCapacity);

                        $em->persist($event);
                        $em->flush();

                        $data = [
                            'status' => 'success',
                            'code' => 200,
                            'message' => 'El participant se ha creado correctamente',
                            'participant' => $participant
                        ];
                    }else{
                        $data = [
                            'status' => 'unaccepted',
                            'code' => 200,
                            'message' => 'No hay plazas para el evento',
                            'verification' => $verification,
                            'authcheck' => $authCheck
                        ];
                    }

                }else{            //ERROR

                    $data = [
                        'status' => 'error',
                        'code' => 400,
                        'message' => 'Error desconocido, algo ha ido mal',
                        'verification' => $verification,
                        'authcheck' => $authCheck
                    ];

                }
            }
        
        }
        // Devolver una respuesta
        return $this->resjson($data);

    }

    //Lista los participantes
    //ID pertenece a un evento
    public function listParticipants(Request $request, JwtAuth $jwt_auth, PaginatorInterface $paginator, $id = null){
        // Recoger la cabecera de autenticación
        $token = $request->headers->get('Authorization');

        // Comprobar el token
        $authCheck = $jwt_auth->checkToken($token);

        if ($id == null){
            $data = array(
                'status' => 'error',
                'code' => 404,
                'message' => 'El id del evento es null',
                'id' => $id,
                'authcheck' => $authCheck
            );
        } elseif($authCheck){
            // Conseguir la identidad del usuario
            $identity = $jwt_auth->checkToken($token, true);

            $em = $this->getDoctrine()->getManager();

            // Hacer una consulta para paginar
            $dql = "SELECT p FROM App\Entity\Participant p WHERE p.event_id = {$id} ORDER BY p.id DESC";
            $query = $em->createQuery($dql);

            // Recoger el parametro page de la url
            $page = $request->query->getInt('page', 1);
            $items_per_page = 100;
            
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
                'participants' => $pagination,
                'user_demander' => $identity->sub,
                'event' => $id
            );
        }else{
            // Si falla devolver esto:
            $data = array(
                'status' => 'error',
                'code' => 404,
                'message' => 'No se pueden listar los participantes en este momento',
                'authcheck' => $authCheck
            );
        }
        return $this->resjson($data);
    }

    //Elimina 1 participant  
    //ID pertenecec a un participant
    public function removeParticipant(Request $request, JwtAuth $jwt_auth, $id = null){

        $token = $request->headers->get('Authorization');
        $authCheck = $jwt_auth->checkToken($token);

        // Salida por defecto
        $data = [
            'status' => 'error',
            'code' => 404,
            'message' => 'user no encontrado o sesion inválida',
            'authcheck' => $authCheck
        ];

        if ($authCheck){
            $identity = $jwt_auth->checkToken($token, true);
            $doctrine = $this->getDoctrine();
            $em = $doctrine->getManager();
            $participant = $doctrine->getRepository(Participant::class)->findOneBy([
                'id'=>$id
            ]);

            $event = $doctrine->getRepository(Event::class)->findOneBy([
                'id'=>$participant->getEvent()
            ]);

            if ($participant && is_object($participant) && ($identity->sub == $participant->getUser()->getId() || $identity->sub == $event->getUser()->getId())){
                $em->remove($participant);
                $em->flush();

                //Consulta para saber las participaciones totales de un evento
                $dql_0 = "SELECT COUNT(p.id) FROM App\Entity\Participant p WHERE p.event_id = {$event->getId()}";
                $query_0 = $em->createQuery($dql_0);
                $eventParticipations = $query_0->getSingleScalarResult();

                $maxParticipations = $event->getMaxCapacity();
                $actualCapacity = $maxParticipations - $eventParticipations;
                $event->setActualCapacity($actualCapacity);

                $em->persist($event);
                $em->flush();

                $data = [
                    'status' => 'success',
                    'code' => 200,
                    'participant' => $participant
                ];
            }
        }

        return $this->resjson($data);
    }


}