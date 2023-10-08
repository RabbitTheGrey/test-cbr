<?php

namespace App\Controller\Api;

use App\Controller\BaseController;
use App\Exception\CbrException;
use App\Form\CbrRequestType;
use App\Service\Contract\CbrContract;
use DateTime;
use Exception;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class CbrController extends BaseController
{
    /**
     * Получение курсов валют
     *
     * @param Request $request
     * @param CbrContract $service
     * @param FormFactoryInterface $formFactory
     *
     * @return JsonResponse
     * @throws CbrException
     * @throws TransportExceptionInterface
     */
    #[Route(path: "/api/getCurrency", name: "get_currency", methods: ["GET"])]
    public function getCurrency(Request $request, CbrContract $service, FormFactoryInterface $formFactory): JsonResponse
    {
        $payload = json_decode($request->getContent(), true);
        $form = $formFactory->create(CbrRequestType::class);

        if ($form->submit($payload) && !$form->isValid()) {
            return $this->json([
                'success' => false,
                'errors' => $form->getErrors(true),
            ]);
        }

        $data = $form->getData();

        $currencies = $service->getCurrency($data['date_start'], $data['date_end']);

        return $this->json([
            'success' => true,
            'currencies' => $currencies,
        ]);
    }
}
