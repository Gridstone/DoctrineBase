<?php
declare(strict_types=1);

namespace adityasetiono\DoctrineBase\Entity\Repository;

class BaseEntityRepository extends \Doctrine\ORM\EntityRepository
{
    public function delete(string $id): void
    {
        $this->_em->createQueryBuilder()
            ->update($this->_entityName, 'e')
            ->set('e.deleted', time()*1000)
            ->where('e.id = :id')
            ->setParameter(':id', $id)
            ->getQuery()
            ->execute();
    }
}
