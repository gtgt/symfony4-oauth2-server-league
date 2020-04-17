<?php
namespace App\Presentation\Api\Rest\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class DebugController
 * @package App\Presentation\Api\Rest\Controller
 */
final class DebugController extends AbstractController
{
    /**
     * @Route("debug", name="api_debug", methods={"GET"})
     * @param Request $request
     * @return Response
     */
    public function debugAction(Request $request): Response
    {
        /** @noinspection AdditionOperationOnArraysInspection */
        return new JsonResponse($request->request->all() + $request->query->all(), Response::HTTP_OK);
    }
}