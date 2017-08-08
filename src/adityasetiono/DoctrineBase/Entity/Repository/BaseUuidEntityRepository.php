<?php
declare(strict_types=1);

namespace adityasetiono\DoctrineBase\Entity\Repository;

class BaseUuidEntityRepository extends \Doctrine\ORM\EntityRepository
{
    public function delete(string $uuid)
    {
        return $this->_em->createQueryBuilder()
            ->update($this->_entityName, 'e')
            ->set('e.deleted', time()*1000)
            ->where('e.uuid = :uuid')
            ->setParameter(':uuid', $uuid)
            ->getQuery()
            ->execute();
    }
}
