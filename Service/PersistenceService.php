<?php

namespace Soil\RdfPersistenceBundle\Service;
use Doctrine\Common\Annotations\AnnotationReader;
use EasyRdf\Sparql\Client;
use Psr\Log\LoggerAwareTrait;

/**
 * Created by PhpStorm.
 * User: fliak
 * Date: 30.9.15
 * Time: 7.49
 */

class PersistenceService {
    use LoggerAwareTrait;

    /**
     * @var Client
     */
    protected $endpoint;

    public function __construct($endpoint)  {
        $this->endpoint = $endpoint;
    }

    public function persist($entity)  {

        $reflection = new \ReflectionClass($entity);

        $originURI = $entity->getOrigin();
        if (!$originURI)    {
            throw new \Exception("Cannot persist entity, because origin URI is not defined");
        }

        $sparql = '';

        $annotationReader = new AnnotationReader();

        $iri = $annotationReader->getClassAnnotation($reflection, 'Soil\DiscoverBundle\Annotation\Iri');
        if ($iri) {
            $iri = $iri->value;

            $sparql .= "<$originURI> rdf:type $iri . " . PHP_EOL;
        }

        $props = $reflection->getProperties();

        foreach ($props as $prop) {
            $matchAnnotation = $annotationReader->getPropertyAnnotation($prop, 'Soil\DiscoverBundle\Annotation\Iri');

            if ($matchAnnotation && $matchAnnotation->persist)    {
                $match = $matchAnnotation->value;
                $prop->setAccessible(true);
                $value = $prop->getValue($entity);

                $sparql .= "<$originURI> $match <$value> . " . PHP_EOL;
            }
        }

        if ($sparql)    {
            $this->logger->addInfo('Persisting: ');
            $this->logger->addInfo($sparql);

            $num = $this->endpoint->insert($sparql);

            $this->logger->addInfo('Return: ' . print_r($num, true));
            return $num;
        }
        else    {
            return null;
        }
    }



} 