<?php

namespace App\Controller;

use App\Dto\RequestVisitCountry;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class IndexController
 */
class IndexController extends AbstractController
{
    const REDIS_TRACK_KEY = "track";

    /**
     * @Route("/api/v1/track", methods={"GET","POST"})
     * @param Request $request
     * @param \Redis $redis
     * @param SerializerInterface $serializer
     * @param ValidatorInterface $validation
     * @param LoggerInterface $logger
     * @return JsonResponse
     */
    public function visitCountry(
        Request $request,
        \Redis $redis,
        SerializerInterface $serializer,
        ValidatorInterface $validation,
        LoggerInterface $logger
    ): JsonResponse {
        try {
            /** @var $result RequestVisitCountry */
            $result = $serializer->deserialize($request->getContent(), RequestVisitCountry::class, "json");
        } catch (ExceptionInterface $e) {
            return $this->json(["status" => "error", "error" => "json decode:".$e->getMessage()], 400);
        }
        $errors = $validation->validate($result);
        if ($errors->count() > 0) {
            return $this->json(["status" => "error", "error" => $errors], 400);
        }
        try {
            $counter = $redis->hIncrBy(self::REDIS_TRACK_KEY, $result->country, 1);
        } catch (\Exception $e) {
            $logger->error($e->getMessage(), ["country" => $result->country, "func" => "redis->hIncrBy"]);

            return $this->json(["status" => "error", "error" => "redis increment:".$e->getMessage()], 500);
        }

        return $this->json(["status" => "ok", "count" => $counter]);
    }

    /**
     * @Route("/api/v1/track/stat", methods={"GET"})
     * @param \Redis $redis
     * @return JsonResponse
     */
    public function statistics(\Redis $redis, LoggerInterface $logger): JsonResponse
    {
        try {
            $trackStats = $redis->hGetAll(self::REDIS_TRACK_KEY);
        } catch (\Exception $e) {
            $logger->error($e->getMessage(), ["func" => "redis->hGetAll"]);

            return $this->json(["status" => "error", "error" => $e->getMessage()]);
        }
        if (is_null($trackStats)) {
            return $this->json(["status" => "ok", "result" => null]);
        }

        return $this->json(["status" => "ok", "result" => $trackStats]);
    }
}