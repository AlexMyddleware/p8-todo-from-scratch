<?php

namespace App\Repository;

use App\Entity\VideoLink;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<VideoLink>
 *
 * @method VideoLink|null find($id, $lockMode = null, $lockVersion = null)
 * @method VideoLink|null findOneBy(array $criteria, array $orderBy = null)
 * @method VideoLink[]    findAll()
 * @method VideoLink[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VideoLinkRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, VideoLink::class);
    }

    public function save(VideoLink $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(VideoLink $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
