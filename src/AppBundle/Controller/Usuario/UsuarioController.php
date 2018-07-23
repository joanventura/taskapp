<?php

namespace AppBundle\Controller\Usuario;

use AppBundle\Entity\Usuario;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use AppBundle\Form\UsuarioType;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use AppBundle\Service\Helpers;

class UsuarioController extends Controller {
    /**
     * @Route("/usuario", options={"expose"=true}, name="lista_usuarios")
     */
    public function indexUsuario(){


        //return $this->render("@App/Usuario/lista_usuarios.html.twig");
        /*
        $em = $this->getDoctrine()->getManager();

        $usuario = new Usuario();
        $usuario->setNombre("Johansell Ventura");
        $usuario->setUsername("johansellventura");

        $em -> persist($usuario);
        $em -> flush();
        dump($usuario);
        die();*/

        $usuarios = $this->getDoctrine()->getRepository(Usuario::class)->findAll();
  
        return $this->render("@App/Usuario/lista_usuarios.html.twig",["usuarios" => $usuarios]);
    }

    /**
     * @Route("/usuario/{idUsuario}", options={"expose"=true}, name="informacion_usuario")
     */
    public function indexUsuarioInfo($idUsuario){
        return $this->render('@App/Usuario/index.html.twig',[ "idUsuario"=>$idUsuario]);
    }


    /**
     * @Route("/usuario/{id}/editar", options={"expose"=true}, name="editar_usuario")
     * @Method("GET")
     * @param Usuario $usuario
     */
    public function indexEditUsuario(Usuario $usuario){
        
        return $this->render('@App/Usuario/editar_usuario.html.twig',
            [ "usuario" => $usuario ]
        );
    }


    /**
     * @Route("rest/usuario/{id}", options={"expose"=true}, name="eliminar_usuario")
     * @Method("DELETE")
     * @param Usuario $usuario
     */
    public function indexEliminarUsuario(Usuario $usuario){

        $em = $this->getDoctrine()->getManager();

        $em->remove($usuario);
        $em->flush();

        return $this->redirectToRoute('lista_usuarios');
        //return new $this->Response("1");
    }


    //RestFul API

    /**
     * @Route("/rest/usuario", options={"expose"=true}, name="buscar_usuarios")
     * @Method("GET")
     */
    public function buscarUsuarios(){
        return null;
    }

    /**
     * @Route("/rest/usuario/{id}", options={"expose"=true}, name="buscar_usuario")
     * @Method("GET")
     * @param Usuario $usuario
     */
    public function buscarUsuario(Usuario $usuario){

        $jsonContent = $this->get('serializer')->serialize($usuario, 'json');
        $jsonContent = json_decode($jsonContent, true);

        return new JsonResponse($jsonContent);
    }

    /**
     * @Route("/rest/usuario/", options={"expose"=true}, name="guardar_usuario")
     * @Method("POST")
     */
    public function guardarUsuario(Request $request){
        $data = $request->getContent();
        $data = json_decode($data, true);

        $usuario = new Usuario();

        $form = $this->createForm(UsuarioType::class, $usuario);
        $form->submit($data);

        /*
        $usuario->setNombre($data["nombre"]);
        $usuario->setUsername($data["username"]);
        */
        if($form->isValid()){
            $em = $this->getDoctrine()->getManager();
            $em->persist($usuario);
            $em->flush();

            $jsonContent = $this->get('serializer')->serialize($usuario, 'json');
            $jsonContent = json_decode($jsonContent, true);

            return new JsonResponse($jsonContent);
        }

        return new JsonResponse(null,400);
    }

    /**
     * @Route("/rest/usuario/{id}", options={"expose"=true}, name="actualizar_usuario")
     * @Method("PUT")
     * @param Request $request
     * @param Usuario $usuario
     * @return JsonResponse
     */
    public function actualizarUsuario(Request $request,Usuario $usuario){
        $data = $request->getContent();
        $data = json_decode($data, true);

        $form = $this->createForm(UsuarioType::class, $usuario);
        $form->submit($data);

        /*$usuario->setNombre($data["nombre"]);
        $usuario->setUsername($data["username"]);*/

        $em = $this->getDoctrine()->getManager();
        $em->flush();

        $jsonContent = $this->get('serializer')->serialize($usuario, 'json');
        $jsonContent = json_decode($jsonContent, true);

        return new JsonResponse($jsonContent);
    }


        /**
     * @Route("/registro_usuario", name="registro")
     */
    public function registroAction(Request $request, UserPasswordEncoderInterface $passwordEncoder)
    {
        $usuario=new Usuario();
        $form=$this->createForm(UsuarioType::class,$usuario);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $password = $passwordEncoder->encodePassword($usuario, $usuario->getContrasena());
            $usuario->setContrasena($password);
            $em=$this->getDoctrine()->getManager();
            $em->persist($usuario);
            $em->flush();
            return $this->redirectToRoute('homepage');
        }
        return $this->render('@App\Usuario\registro.html.twig',array(
            'form'=>$form->createView()
        ));
    }

    /**
     * @Route("/logout", name="logout")
     */
    public function logoutAction(Request $request){
    }

}