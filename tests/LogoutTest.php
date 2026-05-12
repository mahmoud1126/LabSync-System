<?php
use PHPUnit\Framework\TestCase;


require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../controllers/AuthController.php';

class TestableAuthController extends AuthController
{
    public $redirectedTo = null;

    public function redirect($path)
    {
        $this->redirectedTo = $path;
    }
}

class LogoutTest extends TestCase
{
    private $controller;

    protected function setUp(): void
    {
        $_SESSION = [];
        $this->controller = new TestableAuthController();
    }

    public function test_logout_clears_session_user()
    {
        $_SESSION['user'] = [
            'userID'   => 3,
            'userName' => 'researcher_mahmoud',
            'userType' => 'researcher',
        ];

        @$this->controller->logout();
        $this->assertArrayNotHasKey('user', $_SESSION);
    }

    public function test_logout_redirects_to_login()
    {
        $_SESSION['user'] = ['userID' => 3];
        @$this->controller->logout();
        $this->assertEquals('/login', $this->controller->redirectedTo);
    }

    public function test_logout_works_even_with_no_active_session()
    {
        @$this->controller->logout();
        $this->assertEquals('/login', $this->controller->redirectedTo);
        $this->assertArrayNotHasKey('user', $_SESSION);
    }

    public function test_logout_clears_all_session_keys()
    {
        $_SESSION['user']  = ['userID' => 3];
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'hi'];
        @$this->controller->logout();
        $this->assertEmpty($_SESSION);
    }
}
