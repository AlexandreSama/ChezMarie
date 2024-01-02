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

    public function findProductsByCategory($categoryId)
    {
        $qb = $this->createQueryBuilder('p')
            ->join('p.category', 'c')
            ->where('c.id = :categoryId')
            ->setParameter('categoryId', $categoryId);

        $results = $qb->getQuery()->getResult();

        // Ensuite, pour chaque produit, nous devons aller chercher l'image qui correspond à notre logique de sélection.
        $pictureRepository = $this->getEntityManager()->getRepository(Picture::class);

        foreach ($results as $product) {
            $productId = $product->getId();
            // Ceci est une sous-requête où nous récupérons l'image basée sur une certaine logique. 
            $picture = $pictureRepository->createQueryBuilder('p')
                ->where('p.product = :productId')
                ->setParameter('productId', $productId)
                ->orderBy('p.id', 'ASC')
                ->setMaxResults(1)
                ->getQuery()
                ->getOneOrNullResult();

            // Ajoutez l'URL de l'image (ou null si aucune image) au produit.
            if ($picture) {
                $result['picture_url'] = $picture ? $picture->getFileName() : null;
                $result['picture_slug'] = $picture ? $picture->getSlug() : null;
            }
        }

        return $results;
    }
}
