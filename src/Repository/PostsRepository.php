<?php

namespace App\Repository;

use App\Entity\Posts;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Posts>
 */
class PostsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Posts::class);
    }

    //    /**
    //     * @return Posts[] Returns an array of Posts objects
    //     */
    public function findLatestPosts(int $limit = 6): array
    {
        return $this->createQueryBuilder('p')
            ->where('p.is_published = :isPublished')
            ->setParameter('isPublished', true)
            ->orderBy('p.created_at_post', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
    public function findPostsByCategory(int $categoryId, int $limit = 10): array
    {
        return $this->createQueryBuilder('p')
            ->where('p.category = :categoryId')
            ->andWhere('p.is_published = :isPublished')
            ->setParameter('categoryId', $categoryId)
            ->setParameter('isPublished', true)
            ->orderBy('p.created_at_post', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
