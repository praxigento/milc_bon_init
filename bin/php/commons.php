<?php
/**
 * Common functions for this folder.
 */

/**
 * Find entities by attribute value.
 *
 * @param \Psr\Container\ContainerInterface $container
 * @param string $class
 * @param string[] $bind
 * @return array
 */
function common_get_by_attr($container, $class, $bind)
{
    /** @var \Doctrine\ORM\EntityManagerInterface $em */
    $em = $container->get(\Doctrine\ORM\EntityManagerInterface::class);
    /** @var \Doctrine\ORM\QueryBuilder $qb */
    $qb = $em->createQueryBuilder();
    $as = 'main';
    $qb->select($as);
    $qb->from($class, $as);
    /* add WHERE clauses */
    $params = [];
    foreach ($bind as $name => $value) {
        $qb->andWhere("$as.$name=:$name");
        $params[$name] = $value;
    }
    $qb->setParameters($params);
    $query = $qb->getQuery();
    $result = $query->getArrayResult();
    return $result;
}