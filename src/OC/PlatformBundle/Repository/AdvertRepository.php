<?php

namespace OC\PlatformBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * AdvertRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class AdvertRepository extends \Doctrine\ORM\EntityRepository
{
    /*public function myFindAll()
    {
        // Méthode 1 : en passant par l'EntityManager
        $queryBuilder = $this->_em->createQueryBuilder()
            ->select('a')
            ->from($this->_entityName, 'a')
        ;
        // Dans un repository, $this->_entityName est le namespace de l'entité gérée
        // Ici, il vaut donc OC\PlatformBundle\Entity\Advert

        // Méthode 2 : en passant par le raccourci (je recommande)
        $queryBuilder = $this->createQueryBuilder('a');

        // On n'ajoute pas de critère ou tri particulier, la construction
        // de notre requête est finie

        // On récupère la Query à partir du QueryBuilder
        $query = $queryBuilder->getQuery();

        // On récupère les résultats à partir de la Query
        $results = $query->getResult();

        // On retourne ces résultats
        return $results;
    }*/

    public function myFindAll()
    {
        return $this
            ->createQueryBuilder('a')
            ->getQuery()
            ->getResult()
            ;
    }

    public function myFindOne($id)
    {
        $qb = $this->createQueryBuilder('a');

        $qb
            ->where('a.id = :id')
            ->setParameter('id', $id)
        ;

        return $qb
            ->getQuery()
            ->getResult()
            ;
    }

    public function findByAuthorAndDate($author, $year)
    {
        $qb = $this->createQueryBuilder('a');

        $qb->where('a.author = :author')
            ->setParameter('author', $author)
            ->andWhere('a.date < :year')
            ->setParameter('year', $year)
            ->orderBy('a.date', 'DESC')
        ;

        return $qb
            ->getQuery()
            ->getResult()
            ;
    }

    public function whereCurrentYear(QueryBuilder $qb)
    {
        $qb
            ->andWhere('a.date BETWEEN :start AND :end')
            ->setParameter('start', new \Datetime(date('Y').'-01-01'))  // Date entre le 1er janvier de cette année
            ->setParameter('end',   new \Datetime(date('Y').'-12-31'))  // Et le 31 décembre de cette année
        ;
    }

    //http://localhost/Symfony/web/app_dev.php/platform/search/alexandre
    public function myFind($nom)
    {
        $qb = $this->createQueryBuilder('a');

        // On peut ajouter ce qu'on veut avant
        $qb
            ->where('a.author = :author')
            ->setParameter('author', $nom)
        ;

        // On applique notre condition sur le QueryBuilder
        //$this->whereCurrentYear($qb);

        // On peut ajouter ce qu'on veut après
        $qb->orderBy('a.date', 'DESC');

        return $qb
            ->getQuery()
            ->getResult()
            ;
    }

    public function myFindAllDQL()
    {
        $query = $this->_em->createQuery('SELECT a FROM OCPlatformBundle:Advert a');
        $results = $query->getResult();

        return $results;
    }
    //test de la requete en console : php bin/console doctrine:query:dql "SELECT a FROM OCPlatformBundle:Advert a"
    //test de la requete en console : php bin/console doctrine:query:dql "SELECT a, u FROM OCPlatformBundle:Advert a JOIN a.user u WHERE u.age = 25"
    //test de la requete en console : php bin/console doctrine:query:dql "SELECT a.title FROM OCPlatformBundle:Advert a WHERE a.id IN(1, 3, 5)"


    public function getAdvertWithApplications()
    {
        $qb = $this
            ->createQueryBuilder('a')
            ->leftJoin('a.applications', 'app')
            ->addSelect('app')
        ;

        return $qb
            ->getQuery()
            ->getResult()
            ;
    }
    // la relation entreAdvert etApplication est une Many-To-One avecApplication du côté Many, le côté propriétaire donc.
    // Cela veut dire que pour pouvoir faire la jointure dans ce sens, la relation est bidirectionnelle, afin d'ajouter
    // un attribut applications dans l'entité inverseAdvert. C'est ce que nous avons fait à la fin du chapitre précédent.

    // sinon partir d'application (si pas bidirectionelle)

    //personnaliser condition de jointure
    //$qb->innerJoin('a.applications', 'app', 'WITH', 'YEAR(app.date) > 2013')


    //récupérer toutes les annonces qui correspondent à une liste de catégories
    public function getAdvertWithCategories(array $categoryNames)
    {

        $qb = $this
            ->createQueryBuilder('a')
            ->innerJoin('a.categories', 'cat')
            ->addSelect('cat')
        ;

        // Puis on filtre sur le nom des catégories à l'aide d'un IN
        $qb->where(
            $qb->expr()->in('cat.name', $categoryNames)
        );

        return $qb
            ->getQuery()
            ->getResult()
            ;

    }

    //récupérer les X dernières candidatures avec leur annonce associée
    public function getApplicationsWithAdvert($limit){

        $qb = $this
            ->createQueryBuilder('a')
            ->innerJoin('a.applications','app')
            ->addSelect('app')
        ;

        // Puis on ne retourne que $limit résultats
        $qb->setMaxResults($limit);

        return $qb
            ->getQuery()
            ->getResult()
            ;

    }

    // récupérer les entités triées par date
    public function getAllAdverts($page, $nbPerPage)
    {
        $query = $this->createQueryBuilder('a')
            ->orderBy('a.date', 'DESC')
            ->getQuery()

        ;

        $query
            // On définit l'annonce à partir de laquelle commencer la liste
            ->setFirstResult(($page-1) * $nbPerPage)
            // Ainsi que le nombre d'annonce à afficher sur une page
            ->setMaxResults($nbPerPage)
        ;


        return $query->getResult();
    }

    public function getAdverts()
    {
        $query = $this->createQueryBuilder('a')

            // Jointure sur l'attribut image
                ->innerJoin('a.image','img')
                ->addSelect('img')
            // Jointure sur l'attribut categories
                ->innerJoin('a.categories','cat')
                ->addSelect('cat')
                 ->orderBy('a.date', 'DESC')
                 ->getQuery()

        ;



        //return $query->getResult();

        // Enfin, on retourne l'objet Paginator correspondant à la requête construite
        // (n'oubliez pas le use correspondant en début de fichier)
        return new Paginator($query, true);
    }
}
