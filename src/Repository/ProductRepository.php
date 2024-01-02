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
<<<<<<< HEAD
        $qb = $this->createQueryBuilder('p')
            ->join('p.category', 'c')
            ->where('c.id = :categoryId')
=======
        $qb = $this->createQueryBuilder('p') // "p" est un alias pour "Product"
            ->join('p.category', 'c') // Join sur Category avec l'alias "c"
            ->where('c.id = :categoryId') // Filtrer sur la catégorie spécifiée
>>>>>>> 0d379d5785358cfcd0e5aa84098231bed34f8040
            ->setParameter('categoryId', $categoryId);

        $results = $qb->getQuery()->getResult();

<<<<<<< HEAD
=======
        // Ensuite, pour chaque produit, nous devons aller chercher l'image qui correspond à notre logique de sélection.
>>>>>>> 0d379d5785358cfcd0e5aa84098231bed34f8040
        $pictureRepository = $this->getEntityManager()->getRepository(Picture::class);

        foreach ($results as $product) {
            $productId = $product->getId();
<<<<<<< HEAD
=======
            // Ceci est une sous-requête où nous récupérons l'image basée sur une certaine logique. 
>>>>>>> 0d379d5785358cfcd0e5aa84098231bed34f8040
            $picture = $pictureRepository->createQueryBuilder('p')
                ->where('p.product = :productId')
                ->setParameter('productId', $productId)
                ->orderBy('p.id', 'ASC')
                ->setMaxResults(1)
                ->getQuery()
                ->getOneOrNullResult();

<<<<<<< HEAD
=======
            // Ajoutez l'URL de l'image (ou null si aucune image) au produit.
>>>>>>> 0d379d5785358cfcd0e5aa84098231bed34f8040
            if ($picture) {
                $result['picture_url'] = $picture ? $picture->getFileName() : null;
                $result['picture_slug'] = $picture ? $picture->getSlug() : null;
            }
        }

        return $results;
    }
}
