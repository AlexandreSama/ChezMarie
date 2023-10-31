<?php

namespace App\Repository;

use App\Entity\Commentary;
use App\Entity\Picture;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Commentary>
 *
 * @method Commentary|null find($id, $lockMode = null, $lockVersion = null)
 * @method Commentary|null findOneBy(array $criteria, array $orderBy = null)
 * @method Commentary[]    findAll()
 * @method Commentary[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CommentaryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Commentary::class);
    }

    public function findAverageRatingsByTheme($themeId, $limit = 6)
    {
        // Créer le constructeur de requête
        $qb = $this->createQueryBuilder('c')
            ->select('product.id as productId, AVG(c.note) as avg_rating')
            ->join('c.product', 'product')
            ->join('product.category', 'category') // Join sur Category depuis Product
            ->where('category.theme = :themeId') // Filtre pour un thème spécifique
            ->setParameter('themeId', $themeId) // Définir la valeur du thème
            ->groupBy('product.id')
            ->orderBy('avg_rating', 'DESC')
            ->setMaxResults($limit);

        // Vous obtenez d'abord les notes moyennes pour les produits.
        $results = $qb->getQuery()->getResult();

        // Ensuite, pour chaque produit, nous devons aller chercher l'image qui correspond à notre logique de sélection.
        $pictureRepository = $this->getEntityManager()->getRepository(Picture::class);

        foreach ($results as &$result) {
            $productId = $result['productId'];
            // Ceci est une sous-requête où nous récupérons l'image basée sur une certaine logique. 
            $picture = $pictureRepository->createQueryBuilder('p')
                ->where('p.product = :productId')
                ->setParameter('productId', $productId)
                ->orderBy('p.id', 'ASC')
                ->setMaxResults(1)
                ->getQuery()
                ->getOneOrNullResult();

            // Ajoutez l'URL de l'image (ou null si aucune image) au résultat.
            $result['picture_url'] = $picture ? $picture->getFileName() : null;
            $result['picture_slug'] = $picture ? $picture->getSlug() : null;
        }

        return $results;
    }


    //    /**
    //     * @return Commentary[] Returns an array of Commentary objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('c.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Commentary
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
