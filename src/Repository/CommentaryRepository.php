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

    /**
     * The function `findAverageRatingsByTheme` retrieves the average ratings of products belonging to
     * a specific theme, along with their corresponding picture URLs and slugs.
     * 
     * @param themeId The themeId parameter is the ID of the theme for which you want to find the
     * average ratings.
     * @param limit The "limit" parameter is an optional parameter that specifies the maximum number of
     * results to be returned. By default, it is set to 6, but you can change it to any positive
     * integer value to limit the number of results returned by the query.
     * 
     * @return an array of results. Each result contains the following information:
     * - 'productId': The ID of the product.
     * - 'avg_rating': The average rating of the product.
     * - 'picture_url': The URL of the product's picture.
     * - 'picture_slug': The slug of the product's picture.
     */
    public function findAverageRatingsByTheme($themeId, $limit = 6)
    {
        $qb = $this->createQueryBuilder('c')
            ->select('product.id as productId, AVG(c.note) as avg_rating')
            ->join('c.product', 'product')
            ->join('product.category', 'category')
            ->where('category.theme = :themeId')
            ->andWhere('product.is_active = 1')
            ->setParameter('themeId', $themeId)
            ->groupBy('product.id')
            ->orderBy('avg_rating', 'DESC')
            ->setMaxResults($limit);

        $results = $qb->getQuery()->getResult();

        $pictureRepository = $this->getEntityManager()->getRepository(Picture::class);

        foreach ($results as &$result) {
            $productId = $result['productId'];

            $picture = $pictureRepository->createQueryBuilder('p')
                ->where('p.product = :productId')
                ->setParameter('productId', $productId)
                ->orderBy('p.id', 'ASC')
                ->setMaxResults(1)
                ->getQuery()
                ->getOneOrNullResult();

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
