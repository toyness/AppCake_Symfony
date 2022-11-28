<?php

namespace App\Repository;

use App\Entity\Articles;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Articles>
 *
 * @method Articles|null find($id, $lockMode = null, $lockVersion = null)
 * @method Articles|null findOneBy(array $criteria, array $orderBy = null)
 * @method Articles[]    findAll()
 * @method Articles[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ArticlesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Articles::class);
    }

    public function add(Articles $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Articles $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

/**
     * @return Articles[] Returns an array of Articles objects
 */
    public function findArc($value): array
    {
        return $this->createQueryBuilder('articles')
            ->orderBy('articles.id', 'DESC')
            ->setMaxResults(10)
            ->setFirstResult($value)
            ->getQuery()
            ->getResult()
        ;
    }

/**
 *@return Articles[] Returns an array of Articles objects
*/
    public function findOneBySomeField($value): ?Articles
    {
        return $this->createQueryBuilder('articles')
            ->andWhere('articles.title = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function counts()
    {
        $qb = $this->createQueryBuilder('articles');
        return $qb
            ->select('count(articles.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }
}