<?php
/**
 * Nobox API Controller
 * @category  Controller
 */
class NoboxController extends BaseController
{
    private $apiUrl = "https://id.nobox.ai/AccountAPI/GenerateToken";
    private $username = "galangrdmagang@gmail.com";
    private $password = "Galang@123";

    function __construct()
    {
        parent::__construct();
    }

    /**
     * Generate Nobox Token on Login
     * @return string|null
     */
    function generateTokenOnLogin()
    {
        $token = $this->callNoboxApi();
        if ($token) {
            // Store the token in session or do something with it
            set_session("nobox_token", $token);
            return $token;
        }
        return null;
    }

    /**
     * Generate Nobox Token and Return JSON Response
     * @return void
     */
    function generateTokenApi()
    {
        $token = $this->callNoboxApi();
        if ($token) {
            render_json([
                'status' => 'success',
                'token' => $token
            ]);
        } else {
            render_json([
                'status' => 'error',
                'message' => 'Failed to generate token'
            ]);
        }
    }

    /**
     * Call Nobox API to generate token
     * @return string|null
     */
    private function callNoboxApi()
    {
        $data = [
            'username' => $this->username,
            'password' => $this->password
        ];

        $ch = curl_init($this->apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json'
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode == 200) {
            $result = json_decode($response, true);
            return $result['token'] ?? null;
        }

        return null;
    }

    public function revokeTokenOnLogout() {
        // Hapus token dari session jika ada
        if (isset($_SESSION['nobox_token'])) {
            unset($_SESSION['nobox_token']);
        }
    
        // Jika Anda menggunakan metode lain untuk menyimpan token (misalnya cookie),
        // Anda bisa menambahkan kode untuk menghapusnya di sini
        // Contoh untuk cookie:
        // if (isset($_COOKIE['nobox_token'])) {
        //     setcookie('nobox_token', '', time() - 3600, '/');
        // }
    
        // Log revoke token (opsional)
        error_log("Nobox token revoked for user session: " . session_id());
    }
}