<?php

namespace App\Repository;

use App\Entity\Usuario;
use App\Utils\Filters\HelperFilter;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NoResultException;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * @method Usuario|null find($id, $lockMode = null, $lockVersion = null)
 * @method Usuario|null findOneBy(array $criteria, array $orderBy = null)
 * @method Usuario[]    findAll()
 * @method Usuario[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UsuarioRepository extends ServiceEntityRepository implements UserLoaderInterface, UserProviderInterface
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Usuario::class);
    }

    /**
     * Busca los registros para la grilla
     *
     * @param array $filters
     * @param array $operators
     * @param array|null $order_by
     * @param int|null $limit
     * @param int|null $offset
     *
     * @return array
     */
    public function findByGrid($filters, $operators, $order_by, $limit, $offset)
    {
        // armo los filtros
        $filterArray = [];
        $paramsArray = [];
        $joinWithArray = [];

        foreach ($filters as $campo => $valor) {
            switch ($campo) {
                case 'id';
                    HelperFilter::makeNumeric('u', $campo, $valor, $operators, $filterArray, $paramsArray);
                    break;
                case 'nombre';
                    HelperFilter::makeString('u', $campo, $valor, $operators, $filterArray, $paramsArray);
                    break;
                case 'email';
                    HelperFilter::makeString('u', $campo, $valor, $operators, $filterArray, $paramsArray);
                    break;
                case 'rol.id';
                    $joinWithArray[] = 'JOIN u.rol r ';
                    HelperFilter::makeId('r', 'id', $valor, $operators, $filterArray, $paramsArray);
                    break;
                case 'celular';
                    HelperFilter::makeString('u', $campo, $valor, $operators, $filterArray, $paramsArray);
                    break;
                case 'activo';
                    HelperFilter::makeBoolean('u', $campo, $valor, $filterArray, $paramsArray);
                    break;
                case 'fechaUltimoAcceso';
                    HelperFilter::makeDate('u', $campo, $valor, $operators, $filterArray, $paramsArray);
                    break;
            }
        }

        // armo el criterio de orden
        $orderByArray = [];
        foreach ($order_by as $campo => $direccion) {
            switch ($campo) {
                case 'id';
                    $orderByArray[] = 'u.id ' . $direccion;
                    break;
                case 'nombre';
                    $orderByArray[] = 'u.nombre ' . $direccion;
                    break;
                case 'email';
                    $orderByArray[] = 'u.email ' . $direccion;
                    break;
                case 'rol.id';
                    if (!in_array('JOIN u.rol r ', $joinWithArray)) {
                        $joinWithArray[] = 'JOIN u.rol r ';
                    }
                    $orderByArray[] = 'r.id ' . $direccion;
                    break;
                case 'celular';
                    $orderByArray[] = 'u.celular ' . $direccion;
                    break;
                case 'activo';
                    $orderByArray[] = 'u.activo ' . $direccion;
                    break;
                case 'fechaUltimoAcceso';
                    $orderByArray[] = 'u.fechaUltimoAcceso ' . $direccion;
                    break;
            }
        }

        $where = '';
        if (!empty($filterArray)) {
            $where = 'WHERE ' . implode(' AND ', $filterArray);
        }

        $orderByStr = '';
        if (!empty($orderByArray)) {
            $orderByStr = ' ORDER BY ' . implode(' , ', $orderByArray);
        }

        $joinWithStr = '';
        if (!empty($joinWithArray)) {
            $joinWithStr = '' . implode(' ', $joinWithArray);
        }

        $query = $this->getEntityManager()
            ->createQuery(
                "SELECT u FROM App:Usuario u $joinWithStr $where $orderByStr"
            )
            ->setParameters($paramsArray)
            ->setMaxResults($limit)
            ->setFirstResult($offset);

        return $query->getResult();
    }

    /**
     * Cuenta la cantidad de registros según el filtro
     *
     * @param $filters
     * @param $operators
     *
     * @return integer
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function countByGrid($filters, $operators)
    {
        // armo los filtros
        $filterArray = [];
        $paramsArray = [];
        $joinWithArray = [];

        foreach ($filters as $campo => $valor) {
            switch ($campo) {
                case 'id';
                    HelperFilter::makeNumeric('u', $campo, $valor, $operators, $filterArray, $paramsArray);
                    break;
                case 'nombre';
                    HelperFilter::makeString('u', $campo, $valor, $operators, $filterArray, $paramsArray);
                    break;
                case 'email';
                    HelperFilter::makeString('u', $campo, $valor, $operators, $filterArray, $paramsArray);
                    break;
                case 'rol.id';
                    $joinWithArray[] = 'JOIN u.rol r ';
                    HelperFilter::makeId('r', 'id', $valor, $operators, $filterArray, $paramsArray);
                    break;
                case 'celular';
                    HelperFilter::makeString('u', $campo, $valor, $operators, $filterArray, $paramsArray);
                    break;
                case 'activo';
                    HelperFilter::makeBoolean('u', $campo, $valor, $filterArray, $paramsArray);
                    break;
                case 'fechaUltimoAcceso';
                    HelperFilter::makeDate('u', $campo, $valor, $operators, $filterArray, $paramsArray);
                    break;
            }
        }

        $where = '';
        if (!empty($filterArray)) {
            $where = 'WHERE ' . implode(' AND ', $filterArray);
        }

        $joinWithStr = '';
        if (!empty($joinWithArray)) {
            $joinWithStr = '' . implode(' ', $joinWithArray);
        }

        $query = $this->getEntityManager()
            ->createQuery(
                "SELECT COUNT(u.id) FROM App:Usuario u $joinWithStr $where "
            )
            ->setParameters($paramsArray);

        return $query->getSingleScalarResult();
    }

    /**
     * Loads the user for the given username.
     *
     * This method must return null if the user is not found.
     *
     * @param string $email The username
     *
     * @return UserInterface|null
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function loadUserByUsername($email)
    {
        $q = $this->createQueryBuilder('u')
            ->where('u.email = :email')
            ->andWhere('u.activo = :activo')
            ->andWhere('u.fechaBorrado IS NULL')
            ->setParameter('email', $email)
            ->setParameter('activo', true)
            ->getQuery();

        try {
            $user = $q->getSingleResult();
        } catch (NoResultException $e) {
            $message = sprintf('Unable to find an active admin AcmeUserBundle:User object identified by "%s".', $email);
            throw new UsernameNotFoundException($message, 0, $e);
        }

        return $user;
    }

    /**
     * Refreshes the user.
     *
     * It is up to the implementation to decide if the user data should be
     * totally reloaded (e.g. from the database), or if the UserInterface
     * object can just be merged into some internal array of users / identity
     * map.
     *
     * @return UserInterface
     *
     * @throws UnsupportedUserException  if the user is not supported
     * @throws UsernameNotFoundException if the user is not found
     */
    public function refreshUser(UserInterface $user)
    {
        // TODO: Implement refreshUser() method.
    }

    /**
     * Whether this provider supports the given user class.
     *
     * @param string $class
     *
     * @return bool
     */
    public function supportsClass($class)
    {
        // TODO: Implement supportsClass() method.
    }

    /**
     * Encuentra un usuario mediante su email
     *
     * @param $email
     *
     * @return mixed
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findOneByEmail($email)
    {
        $query = $this->getEntityManager()
            ->createQuery(
                "SELECT u FROM App:Usuario u WHERE u.email = :email "
            );
        $query->setParameters(['email' => $email]);

        return $query->getOneOrNullResult();
    }
}
