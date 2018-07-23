<?php

namespace AppBundle\Controller\Ticket;

use AppBundle\Entity\Ticket;
use AppBundle\Entity\Nota;
use AppBundle\Entity\Usuario;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;

use AppBundle\Form\TicketType;
use AppBundle\Form\NotaType;

use Symfony\Component\HttpFoundation\JsonResponse;
use AppBundle\Service\Helpers;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class TicketController extends Controller
{
    /**
     * @Route("/ticket", name="lista_tickets")
     */
    public function indexTickets()
    {

        $tickets = $this->getDoctrine()->getRepository(Ticket::class)->TicketUsuarios($this->getUser());

        return $this->render('@App/Ticket/lista_tickets.html.twig', 
            ['tickets'=>$tickets]
        );
    }
    
    /**
     * @Route("/ticket/{id}/editar", name="editar_ticket")
     * @Method("GET")
     * @param Usuario $usuario
     */
    public function indexEditTicket(Ticket $ticket){
        return $this->render('@App/Usuario/editar_ticket.html.twig',
            ["ticket" => $ticket]
        );
    }
    /**
     * @Route("rest/ticket/{id}", name="eliminar_ticket")
     * @Method("DELETE")
     * @param Usuario $ticket
     */
    public function indexEliminarTicket(Ticket $ticket){
        $em = $this->getDoctrine()->getManager();
        $em->remove($ticket);
        $em->flush();
        return $this->redirectToRoute('lista_tickets');
    }
    //RestFul API
    /**
     * @Route("/rest/ticket", options={"expose"=true}, name="buscar_tickets")
     * @Method("GET") 
     */
    public function buscarTickets()
    {
        return null;
    }
    /**
    * @Route("/rest/ticket/{id}", options={"expose"=true}, name="buscar_ticket")
    * @Method("GET") 
    * @param Ticket $ticket
    */
    public function buscarUsuario(Ticket $ticket)
    {
        //convertir las siguientes 3 lines en un servicio p/ganar 10 puntos
        $encoders = array( new JsonEncoder() );
        $normalizers = array( new ObjectNormalizer() );
        $serializer = array( new Serializer($normalizers, $encoders) );
        $jsonContent = $serializer->serialize($ticket, 'json');
        $jsonContent = json_decode($jsonContent, true);
        return new JsonResponse($jsonContent);
    }
    
    /**
     * @Route("/rest/ticket", options={"expose"=true}, name="guardar_ticket")
     * @Method("POST")
     * return Response
     */
    public function guardarTicket(Request $request)
    {

        $usarioId           = new Usuario();
        $usuarioAsignadoId  = new Usuario();

        $data = $request->getContent();
        $data = (json_decode($data, true));

        $ticket = new Ticket();
        $ticket->setDescripcion($data['descripcion']);
        $ticket->setEstado('Pendiente');
        $ticket->setFecha(new \DateTime());
        $ticket->setFechaCompletado(null);

        $em = $this->getDoctrine()->getManager();

        $usarioId = $em->getRepository(Usuario::class)->find($this->getUser()->getId());
        $usuarioAsignadoId = $em->getRepository(Usuario::class)->find($data['usuario_asignado_id']);
        
        $ticket->setUsuario($usarioId);
        $ticket->setUsuarioAsignado($usuarioAsignadoId);
        $em->persist($ticket);
        $em->flush();
       
        return new Response('1');
    }
    /**
     * @Route("/rest/ticket/{id}", name="actualizar_tickets")
     * @Method("PUT") 
     * @param Request $request
     * @param Ticket $ticket
     * @return JsonResponse
     */
    public function actualizarTicket(Request $request, Ticket $ticket)
    {
        $data = $request->getContent();
        $data = json_decode($data, true);
        $form = $this->createForm(TicketType::class, $ticket);
        $form->submit($data);
        $em = $this->getDoctrine()->getManager();
        $em->flush();
        $jsonContent = $this->getDoctrine()->getManager();
        $jsonContent = json_decode($jsonContent, true);
        return new JsonResponse($jsonContent);
    }


    /**
     * @Route("/ticket/nuevo", name="nuevo_ticket", options={"expose"=true} )
     */
    public function nuevoTicket(Request $request)
    {
        $usuarios = $this->getDoctrine()->getRepository(Usuario::class)->allUserTecnico();
        $form     = $this->createForm(TicketType::class);
        return $this->render('@App/Ticket/nuevo_ticket.html.twig',array(
            'form'=>$form->createView(),
            'usuarios'=>$usuarios
        ));
    }

    /**
     * @Route("/ticket/ver/{id}", name="informacion_ticket", options={"expose"=true},requirements={"id"="\d+"})
     * @Method("GET")
     * @param Ticket $ticket
     * @return Response
     */
    public function indexTicketInfo(Ticket $ticket)
    {
        $formNotas=$this->createForm(NotaType::class);
        $notas=$this->getDoctrine()->getRepository(Nota::class)->allNotasTicket($ticket);
        return $this->render('@App/Ticket/informacion_ticket.html.twig',array(
            'ticket'=>$ticket,
            'notas'=>$notas,
            'formNotas'=>$formNotas->createView()
        ));
        
    }
    
}