<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Constraints\Email;

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
        return $this->resjson($data);
    }
    public function create(Request $request){
        // Recocger los datos por post
        $json = $request->get('json', null);

        // Descodificar el json
        $params = json_decode($json);

        //Respuesta por defecto
        $data = [
            'status' => 'error',
            'code' => 200,
            'message' => 'El usuario no se ha creado.'
        ];

        //Comprobar y validar datos
        if($json != null) {

            $name = (!empty($params->name)) ? $params->name : null;
            $surname = (!empty($params->surname)) ? $params->surname : null;
            $email = (!empty($params->email)) ? $params->email : null;
            $password = (!empty($params->password)) ? $params->password : null;
            $entity = (!empty($params->entity)) ? $params->entity : null;
            $charge = (!empty($params->charge)) ? $params->charge : null;
            $avatar = (!empty($params->avatar)) ? $params->avatar : null;
            $biography = (!empty($params->biography)) ? $params->biography : null;
            $valoration = (!empty($params->valoration)) ? $params->valoration : null;
            $prefix = (!empty($params->prefix)) ? $params->prefix : null;
            $telephone = (!empty($params->telephone)) ? $params->telephone : null;
            $num_valoration = (!empty($params->num_valoration)) ? $params->num_valoration : null;

            $validator = Validation::createValidator();
            $validate_email = $validator->validate($email, [
                new Email()
            ]);

            if(!empty($email) && count($validate_email) == 0 && !empty($password) && !empty($name) && !empty($surname) && !empty($prefix) && !empty($telephone)){
                // Si la validación es correcta, crear el objeto del usuario

                $user = new User();
                $user->setName($name);
                $user->setSurname($surname);
                $user->setEmail($email);
                $user->setEntity($entity);
                $user->setCharge($charge);
                $user->setAvatar($avatar);
                $user->setBiography($biography);
                $user->setCreatedAt(new \Datetime('now'));
                $user->setValoration($valoration);
                $user->setPrefix($prefix);
                $user->setTelephone($telephone);
                $user->setNumValoration('0');
                $user->setRole('ROLE_USER');
                

                // Cifrar la contraseña
                $pwd = hash('sha256', $password);
                $user->setPassword($pwd);

                $data = $user;

                // Comprobarl si el usuario existe (duplicados)
                $doctrine = $this->getDoctrine();
                $em = $doctrine->getManager();

                $user_repo = $doctrine->getRepository(User::class);
                $isset_user = $user_repo->findBy(array(
                    'email' => $email
                ));

                //Si no existe, guardarlo en la bd
                if(count($isset_user) == 0) {
                    //guardo el usuario
                    $em->persist($user);
                    $em->flush();

                    $data = [
                        'status' => 'success',
                        'code' => 200,
                        'message' => 'El usuario ha sido creado correctamente.',
                        'user' => $user
                    ];
                }else{
                    $data = [
                        'status' => 'error',
                        'code' => 400,
                        'message' => 'El usuario ya existe.'
                    ];
                }
    }
}
        // Hacer respuesta en json

        return new JsonResponse($data);
        

    }
}
