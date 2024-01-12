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

    /**
     * The function finds the top rated products by theme, grouping them by theme and selecting the top
     * 4 products for each theme based on their average note.
     * 
     * @return an array of top-rated products grouped by theme. Each theme has a maximum of 4 products,
     * and each product includes the product object and its average note.
     */
    public function findTopRatedProductsByTheme()
    {
        $qb = $this->createQueryBuilder('product');

        $qb->select('product, AVG(commentary.note) as avgNote, category, theme.id as themeId')
            ->join('product.commentaries', 'commentary')
            ->join('product.category', 'category')
            ->join('category.theme', 'theme')
            ->groupBy('theme.id, product.id')
            ->orderBy('theme.id', 'ASC')
            ->addOrderBy('avgNote', 'DESC');

        $query = $qb->getQuery();
        $results = $query->getResult();

        $topProducts = [];
        foreach ($results as $result) {
            $themeId = $result['themeId'];

            if (!isset($topProducts[$themeId])) {
                $topProducts[$themeId] = [];
            }

            if (count($topProducts[$themeId]) < 6) {
                $topProducts[$themeId][] = [
                    'product' => $result[0],
                    'avgNote' => $result['avgNote']
                ];
            }
        }

        return $topProducts;
    }

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
