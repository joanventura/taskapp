<?php
namespace AppBundle\Repository;
use AppBundle\Entity\Ticket;
/**
 * NotaRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class NotaRepository extends \Doctrine\ORM\EntityRepository
{
    public function allNotasTicket(Ticket $ticket)
    {
        return $this->getEntityManager()->createQuery(
            ' SELECT n FROM AppBundle:Nota n
            WHERE n.ticket=:ticket
            ORDER BY n.fecha desc'
        )->setParameter(':ticket', $ticket)->getResult();
    }
}