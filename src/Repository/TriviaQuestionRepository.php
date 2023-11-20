<?php

namespace App\Repository;

use App\Entity\TriviaQuestion;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TriviaQuestion>
 *
 * @method TriviaQuestion|null find($id, $lockMode = null, $lockVersion = null)
 * @method TriviaQuestion|null findOneBy(array $criteria, array $orderBy = null)
 * @method TriviaQuestion[]    findAll()
 * @method TriviaQuestion[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TriviaQuestionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TriviaQuestion::class);
    }

    /**
     * @return TriviaQuestion[]
     */
    public function get(): array
    {
        return $this->findAll();
    }

    /**
     * @param array $attributes
     * @return void
     */
    public function store(array $attributes): void
    {
        $entityManager = $this->getEntityManager();
        $entity = new TriviaQuestion();
        $entity->setPlayerName($attributes['player']);
        $entity->setQuestionText($attributes['text']);
        $entity->setType($attributes['type']);
        $entity->setOptions(json_encode($attributes['options']));

        $entityManager->persist($entity);
        $entityManager->flush();
    }
//    /**
//     * @return TriviaQuestion[] Returns an array of TriviaQuestion objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('t.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?TriviaQuestion
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
