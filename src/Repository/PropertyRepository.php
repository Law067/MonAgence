<?php

namespace App\Repository;

use App\Entity\Picture;
use Doctrine\ORM\Query;
use App\Entity\Property;
use App\Entity\PropertySearch;
use Doctrine\ORM\QueryBuilder;
use Knp\Component\Pager\PaginatorInterface;
use Doctrine\Common\Persistence\ManagerRegistry;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method Property|null find($id, $lockMode = null, $lockVersion = null)
 * @method Property|null findOneBy(array $criteria, array $orderBy = null)
 * @method Property[]    findAll()
 * @method Property[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PropertyRepository extends ServiceEntityRepository
{
    /**
     * @var PaginatorInterface
     */
    private $paginator;

    public function __construct(ManagerRegistry $registry, PaginatorInterface $paginator)
    {
        parent::__construct($registry, Property::class);
        $this->paginator = $paginator;
    }

/**
 * @return PaginationInterface
 */

    public function paginateAllVisible(PropertySearch $search, int $page): PaginationInterface
    {
        $query = $this->findVisibleQuery();

        if ($search->getMaxPrice()) {
            $query = $query
                ->andWhere('p.price <= :maxprice')
                ->setParameter('maxprice', $search->getMaxPrice());
        }

        if ($search->getMinSurface()) {
            $query = $query
                ->andWhere('p.surface >= :minsurface')
                ->setParameter('minsurface', $search->getMinSurface());
        }

        if ($search->getOptions()->count() > 0) {
            $key = 0;
            foreach ($search->getOptions() as $option) {
                $key++;
                $query = $query
                    ->andWhere(":option$key MEMBER OF p.options")
                    ->setParameter("option$key", $option);
            }
        }

        $properties = $this->paginator->paginate(
            $query->getQuery(),
            $page,
            12
        );
        $this->hydratePicture($properties);

        return $properties;

    }

    public function findLatest(): array
    {
        $properties = $this->findVisibleQuery()
            ->setMaxResults(4)
            ->getQuery()
            ->getResult();
        $this->hydratePicture($properties);
        return $properties;
    }

    private function findVisibleQuery(): QueryBuilder
    {
        return $this->createQueryBuilder('p')
            ->where('p.sold = false');

    }

  public function hydratePicture($properties){
      if (method_exists($properties, 'getItems')) {
          $properties = $properties->getItems();
      }

    $pictures = $this->getEntityManager()->getRepository(Picture::class)->findForProperties($properties);
    foreach ($properties as $property) {
        /**@var $property Property */
        if ($pictures->containsKey($property->getId())) {
            $property->setPicture($pictures->get($property->getId()));
        }  
    }
  }
}
