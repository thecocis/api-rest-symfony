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
use App\Entity\Comment;
use App\Services\JwtAuth;

class CommentController extends AbstractController
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
        $comment_repo = $this->getDoctrine()->getRepository(Comment::class);
        $comments = $comment_repo->findAll();

        return $this->json([
            'message' => 'Welcome to your new controller! PD. Deberias cambiar este mensaje, vago',
            'path' => 'src/Controller/CommentController.php',
            'comments' => $comments
            
        ]);
    }

    public function create(Request $request, JwtAuth $jwt_auth, $id = null){
        $data = [
            'status' => 'error',
            'code' => 400,
            'message' => 'El comentario no ha podido crearse'
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

                $from_id = ($identity->sub != null) ? $identity->sub : null;
                $user_id = (!empty($params->user_id)) ? $params->user_id : null;
                $body = (!empty($params->body)) ? $params->body : null;


                if(!empty($from_id) && !empty($body) && !empty($user_id)){
                    // Guardar el nuevo comment en la BBDD
                    $em = $this->getDoctrine()->getManager();
                    $from = $this->getDoctrine()->getRepository(User::class)->findOneBy([
                        'id' => $from_id 
                    ]);
                    $user = $this->getDoctrine()->getRepository(User::class)->findOneBy([
                        'id' => $user_id
                    ]);

                    if ($id == null){     // NUEVO COMMENT
                        // Crear y guardar objeto
                        $comment = new Comment();
                        $comment->setUser($user);
                        $comment->setFrom($from);
                        $comment->setBody($body);

                        $createdAt = new \Datetime('now');
                        $comment->setCreatedAt($createdAt);                

                        // Guardar en la BBDD
                        $em->persist($comment);
                        $em->flush();

                        $data = [
                            'status' => 'success',
                            'code' => 200,
                            'message' => 'El comment se ha creado correctamente',
                            'comment' => $comment
                        ];
                    }else{            //COMMENT EXISTENTE, por lo tanto  lo modificamos

                        $comment = $this->getDoctrine()->getRepository(Comment::class)->findOneBy([
                            'id' => $id,
                            'from' => $identity->sub
                        ]);

                        if ($comment && is_object($comment)){
                            $comment->setBody($body);

                            $em->persist($comment);
                            $em->flush();

                            $data = [
                                'status' => 'success',
                                'code' => 200,
                                'message' => 'El comment se ha actualizado correctamente',
                                'comment' => $comment
                            ];
                        }
                    }
                }
            }
        }

        // Devolver una respuesta
        return $this->resjson($data);
    }

    //Retorna los comments del usuario al que se le hacen
    //El usuario "from" es el autor de los comentarios
    public function listComments(Request $request, JwtAuth $jwt_auth, PaginatorInterface $paginator, $id = null){
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

            // Hacer una consulta para paginar
            $dql = "SELECT c FROM App\Entity\Comment c WHERE c.user = {$id} ORDER BY c.id DESC";
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
                'comments' => $pagination,
                'user' => $identity->sub
            );
        }else{
            // Si falla devolver esto:
            $data = array(
                'status' => 'error',
                'code' => 404,
                'message' => 'No se pueden listar los comentarios en este momento',
                'authcheck' => $authCheck
            );
        }
        return $this->resjson($data);
    }

    //Elimina 1 comment
    public function remove(Request $request, JwtAuth $jwt_auth, $id = null){

        $token = $request->headers->get('Authorization');
        $authCheck = $jwt_auth->checkToken($token);

        // Salida por defecto
        $data = [
            'status' => 'error',
            'code' => 404,
            'message' => 'Comment no encontrado o sesion inválida',
            'authcheck' => $authCheck
        ];

        if ($authCheck){
            $identity = $jwt_auth->checkToken($token, true);
            $doctrine = $this->getDoctrine();
            $em = $doctrine->getManager();
            $comment = $doctrine->getRepository(Comment::class)->findOneBy([
                'id'=>$id
            ]);

            if ($comment && is_object($comment) && $identity->sub == $comment->getFrom()->getId()){
                $em->remove($comment);
                $em->flush();

                $data = [
                    'status' => 'success',
                    'code' => 200,
                    'comment' => $comment
                ];
            }
        }

        return $this->resjson($data);
    }


}