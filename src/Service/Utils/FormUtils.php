<?php


namespace App\Service\Utils;


use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Request;

class FormUtils
{
    static public function getErrorMessages(FormInterface $form): array
    {
        $errors = [];

        foreach ($form->getErrors(true) as $key => $error) {
            $errors[$error->getOrigin()->getName()] = $error->getMessage();
        }

        return $errors;
    }

    /**
     * @param Request $request
     * @return mixed
     * @throws BadRequestException
     */
    static public function getJson(Request $request)
    {
        $data = json_decode($request->getContent(), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new BadRequestException('Invalid json');
        }

        return $data;
    }
}