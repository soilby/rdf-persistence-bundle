<?php

namespace Soil\RdfPersistenceBundle\Service;
use Doctrine\Common\Annotations\AnnotationReader;
use EasyRdf\Sparql\Client;

/**
 * Created by PhpStorm.
 * User: fliak
 * Date: 30.9.15
 * Time: 7.49
 */

class PersistenceService {

    /**
     * @var Client
     */
    protected $endpoint;

    public function __construct($endpoint)  {
        $this->endpoint = $endpoint;
    }

    public function persist($entity)  {

        $reflection = new \ReflectionClass($entity);

        $props = $reflection->getProperties();

        $annotationReader = new AnnotationReader();

//        $vocab = $annotationReader->getClassAnnotation($reflection, 'Soil\DiscoverBundle\Annotation\Vocab');
//        if ($vocab) {
//            $vocab = $vocab->value;
//        }
//        else    {
//            $vocab = 'tal';
//        }

        $sparql = '';

        $originURI = $entity->getOrigin();
        if (!$originURI)    {
            throw new \Exception("Cannot persist entity, because origin URI is not defined");
        }

        foreach ($props as $prop) {
            $matchAnnotation = $annotationReader->getPropertyAnnotation($prop, 'Soil\DiscoverBundle\Annotation\Match');

            if ($matchAnnotation)    {
                $match = $matchAnnotation->value;

                $value = $prop->getValue($entity);

                $sparql .= "<$originURI> $match $value";

            }
        }

        if ($sparql)    {
            echo $sparql;
//            return $this->endpoint->insert($sparql);
        }
        else    {
            return null;
        }
    }



} 