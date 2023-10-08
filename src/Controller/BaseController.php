<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Loader\AnnotationLoader;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

abstract class BaseController extends AbstractController
{
    /** @var EntityManagerInterface */
    public EntityManagerInterface $em;
    /** @var Serializer */
    public Serializer $serializer;
    /** @var string */
    public string $env;
    /** @var string[] Дефолтные не прод окружения Симфони */
    public array $devEnvs = ['dev', 'test'];

    /**
     * @param KernelInterface $kernel
     * @param EntityManagerInterface $em
     */
    public function __construct(KernelInterface $kernel, EntityManagerInterface $em)
    {
        $encoders = [new JsonEncoder()];
        $classMetadataFactory = new ClassMetadataFactory(new AnnotationLoader());
        $defaultContext = [
            AbstractObjectNormalizer::ENABLE_MAX_DEPTH => true,
            AbstractNormalizer::CIRCULAR_REFERENCE_LIMIT => 2,
        ];
        $normalizers = [
            new DateTimeNormalizer([DateTimeNormalizer::FORMAT_KEY => 'Y-m-d H:i']),
            new ObjectNormalizer(
                classMetadataFactory: $classMetadataFactory,
                propertyTypeExtractor: new ReflectionExtractor(),
                defaultContext: $defaultContext
            ),
        ];
        $this->serializer = new Serializer($normalizers, $encoders);
        $this->env = $kernel->getEnvironment();
        $this->em = $em;
    }
}
