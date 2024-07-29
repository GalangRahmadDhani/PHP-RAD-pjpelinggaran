<?php

use BaseController;

class GetnoboxaccountController extends BaseController
{
    public function index()
    {
        // Retrieve the token from cookies
        $token = isset($_COOKIE['token']) ? $_COOKIE['token'] : null;

        if (!$token) {
            // Handle the case where the token is not found
            $response = [
                'error' => 'Token not found',
            ];
            $this->renderJson($response, 400); // 400 Bad Request
            return;
        }

        // Initialize Nobox with the token
        $nobox = new Nobox($token);

        // Get the account list
        $tokenResponse = $nobox->getAccountList();

        // Return the response as JSON
        $this->renderJson($tokenResponse);
    }

    private function renderJson($data, $statusCode = 200)
    {
        header('Content-Type: application/json');
        http_response_code($statusCode);
        echo json_encode($data);
    }
}
