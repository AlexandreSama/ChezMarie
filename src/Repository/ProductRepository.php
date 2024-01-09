<?php

namespace App\Repository;

use App\Entity\Picture;
use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Product>
 *
 * @method Product|null find($id, $lockMode = null, $lockVersion = null)
 * @method Product|null findOneBy(array $criteria, array $orderBy = null)
 * @method Product[]    findAll()
 * @method Product[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    //    /**
    //     * @return Product[] Returns an array of Product objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('p.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Product
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

   /**
    * The function `findProductsByCategory` retrieves products belonging to a specific category and
    * also fetches the first picture associated with each product.
    * 
    * @param categoryId The categoryId parameter is the ID of the category for which you want to find
    * products.
    * 
    * @return an array of products that belong to a specific category.
    */
    public function findProductsByCategory($categoryId)
    {
        $qb = $this->createQueryBuilder('p')
            ->join('p.category', 'c')
            ->where('c.id = :categoryId')
            ->setParameter('categoryId', $categoryId);

        $results = $qb->getQuery()->getResult();

        $pictureRepository = $this->getEntityManager()->getRepository(Picture::class);

        foreach ($results as $product) {
            $productId = $product->getId();

            $picture = $pictureRepository->createQueryBuilder('p')
                ->where('p.product = :productId')
                ->setParameter('productId', $productId)
                ->orderBy('p.id', 'ASC')
                ->setMaxResults(1)
                ->getQuery()
                ->getOneOrNullResult();

            if ($picture) {
                $result['picture_url'] = $picture ? $picture->getFileName() : null;
                $result['picture_slug'] = $picture ? $picture->getSlug() : null;
            }
        }

        return $results;
    }

    /**
     * The function returns a query builder object for finding all records in a database table.
     * 
     * @return a Doctrine query object.
     */
    public function findAllQuery()
    {
        return $this->createQueryBuilder('p')
            ->getQuery();
    }
}
