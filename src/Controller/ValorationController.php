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
use App\Entity\Valoration;
use App\Services\JwtAuth;

class ValorationController extends AbstractController
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
        $valoration_repo = $this->getDoctrine()->getRepository(Valoration::class);
        $valorations = $valoration_repo->findAll();

        return $this->json([
            'message' => 'Welcome to your new controller! PD. Deberias cambiar este mensaje, vago',
            'path' => 'src/Controller/ValorationController.php',
            'valorations' => $valorations
            
        ]);
    }

    public function valorate(Request $request, JwtAuth $jwt_auth, $id = null){
        $data = [
            'status' => 'error',
            'code' => 400,
            'message' => 'La valoración no ha podido crearse'
        ];

        // Recoger el token
        $token = $request->headers->get('Authorization', null);

        // Comprobar si es correcto
        $authCheck = $jwt_auth->checkToken($token);

        $data = [
            'status' => 'error',
            'code' => 400,
            'token' => $authCheck,
            'message' => 'Algo ha ido mal al principio de la ejecución'
        ];

        if($authCheck){
            // Recoger datos por post
            $json = $request->get('json', null);
            $params = json_decode($json);

            // Recoger el objeto del usuario identificado
            $identity = $jwt_auth->checkToken($token, true);

            // Comprobar y validar datos
            if(!empty($json)){
     
                $from_id = ($identity->sub != null) ? $identity->sub : null;
                $user_id = (!empty($params->user_id)) ? $params->user_id : null;
                $value = (!empty($params->value)) ? $params->value : null;

                if ($user_id == null){
                    $user_id = (!empty($params->user->id)) ? $params->user->id : null;
                }

                $data = [
                    'status' => 'error',
                    'code' => 400,
                    'token' => $authCheck,
                    'message' => 'CHECKPOINT1',
                    'from_id' => $from_id,
                    'user_id' => $user_id,
                    'value' => $value,
                    
                ];
                if(!empty($from_id) && !empty($value) && !empty($user_id)){
                    // Guardar la nueva valoration en la BBDD
                    $em = $this->getDoctrine()->getManager();
                    $from = $this->getDoctrine()->getRepository(User::class)->findOneBy([
                        'id' => $identity->sub 
                    ]);
                    $user = $this->getDoctrine()->getRepository(User::class)->findOneBy([
                        'id' => $user_id
                    ]);

                    //Numero total de valoraciones realizadas al usuario
                    $query_total = $em->createQuery("SELECT COUNT(v.value) FROM App\Entity\Valoration v WHERE v.user = {$user_id}");
                    $total = $query_total->getSingleScalarResult();
                    //Sumatorio de valoraciones realizadas al usuario
                    $query_sum = $em->createQuery("SELECT SUM(v.value) FROM App\Entity\Valoration v WHERE v.user = {$user_id}");
                    $sum = $query_sum->getSingleScalarResult();

                    if ($total == 0){
                        $total = 1;
                    }
                    $averageValorations = ($sum/$total);

                    $data = [
                        'status' => 'error',
                        'code' => 400,
                        'token' => $authCheck,
                        'message' => 'CHECKPOINT2',
                        'user' => $user,
                        'from' => $from,
                        'media' => $averageValorations,
                        'total' => $total
                    ];

                    if ($id == null){     // NUEVA VAL
                        // Crear y guardar objeto
                        $valoration = new Valoration();
                        $valoration->setUser($user);
                        $valoration->setFrom($from);
                        $valoration->setValue($value);     
                        
                        $user->setValoration($averageValorations);
                        $user->setNumValoration($total);

                        // Guardar en la BBDD
                        $em->persist($valoration);
                        $em->persist($user);

                        $em->flush();

                        $data = [
                            'status' => 'success',
                            'code' => 200,
                            'message' => 'La valoración se ha creado correctamente',
                            'valoration' => $valoration,
                            'user' => $user,
                            'media' => $averageValorations,
                            'total' => $total,
                            'sum' => $sum
                        ];
                    }else{            //VALORATION EXISTENTE, por lo tanto  la modificamos

                        $valoration = $this->getDoctrine()->getRepository(Valoration::class)->findOneBy([
                            'id' => $id,
                            'from' => $identity->sub
                        ]);

                        $data = [
                            'status' => 'error',
                            'code' => 400,
                            'token' => $authCheck,
                            'valoration' => $valoration,
                            'id_valoration' => $id,
                            'message' => 'Valoracion existente, pero no modificada',
                            'valoration' => $valoration,
                            'user' => $user,
                            'from' => $from
                        ];

                        if ($valoration && is_object($valoration)){
                            $valoration->setValue($value);
                            $user->setValoration($averageValorations);
                            $user->setNumValoration($total);

                            $em->persist($valoration);
                            $em->persist($user);
                            $em->flush();

                            $data = [
                                'status' => 'success',
                                'code' => 200,
                                'message' => 'La valoración se ha actualizado correctamente',
                                'valoration' => $valoration,
                                'user' => $user,
                                'media' => $averageValorations,
                                'total' => $total,
                                'sum' => $sum
                            ];
                        }
                    }
                }
            }
        }

        // Devolver una respuesta
        return $this->resjson($data);
    }

    //Retorna las valorations del usuario al que se le hacen
    //El usuario "from" es el autor de los comentarios
    public function listValorations(Request $request, JwtAuth $jwt_auth, $id = null){
        // Recoger la cabecera de autenticación
        $token = $request->headers->get('Authorization');

        // Comprobar el token
        $authCheck = $jwt_auth->checkToken($token);

        if ($id == null){
            $data = array(
                'status' => 'error',
                'code' => 404,
                'message' => 'El id del usuario es null',
                'id' => $id,
                'authcheck' => $authCheck
            );
        } elseif($authCheck){
            // Conseguir la identidad del usuario
            $identity = $jwt_auth->checkToken($token, true);

            $em = $this->getDoctrine()->getManager();

            // Hacer una consulta 
            $dql = "SELECT v FROM App\Entity\Valoration v WHERE v.user = {$id} ORDER BY v.id DESC";
            $query = $em->createQuery($dql);
            $valorationsFULL = $query->getResult();
            //Solo valoraciones
            $dql_values = $em->createQuery("SELECT v.value FROM App\Entity\Valoration v WHERE v.user = {$id} ORDER BY v.id DESC");
            $valorationsONLY = $dql_values->getResult();
            //Total valoraciones
            $query_total = $em->createQuery("SELECT COUNT(v.value) FROM App\Entity\Valoration v WHERE v.user = {$id}");
            $total = $query_total->getSingleScalarResult();
            //Sumatorio valoraciones
            $query_sum = $em->createQuery("SELECT SUM(v.value) FROM App\Entity\Valoration v WHERE v.user = {$id}");
            $sum = $query_sum->getSingleScalarResult();

            if ($total == 0){
                $total = 1;
            }
            $averageValorations = ceil($sum/$total);

            // Preparar array de datos para devolver
            $data = array(
                'status' => 'success',
                'code' => 200,
                'num_valorations' => $total,
                'average_valorations' => ceil($sum/$total), 
                'valorations' => $valorationsFULL,
                'user_demander' => $identity->sub,
                'user_to' => $id
            );
        }else{
            // Si falla devolver esto:
            $data = array(
                'status' => 'error',
                'code' => 404,
                'message' => 'No se pueden listar las valoraciones en este momento',
                'authcheck' => $authCheck
            );
        }
        return $this->resjson($data);
    }



}