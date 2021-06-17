<?php

namespace App\Serializer;

use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\HttpFoundation\UrlHelper;
use App\Entity\Book;

class BookNormalizer implements ContextAwareNormalizerInterface
{
    private $normalizer;
    private $urlHelper;


    public function __construct(
        ObjectNormalizer $normalizer,
        UrlHelper $urlHelper
    )
    {
        $this->normalizer=$normalizer;
        $this->urlHelper=$urlHelper;
    }

    public function normalize($book, $format = null, array $context = [])
    {
        $data = $this->normalizer->normalize($book, $format, $context);
        if(!empty($book->getImage()))
        {
            $data['image'] = $this->urlHelper->getAbsoluteUrl('/storage/default/' . $book->getImage());
        }
        return $data;
    }

    public function supportsNormalization($data, string $format = null, array $context = [])
    {
        return $data instanceof Book;
    }
}