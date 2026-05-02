<?php

class AuthController extends BaseController
{
    public function showLogin()
    {
        $this->view('auth/login');
    }

    public function doLogin()
    {
        $username = $this->getPost('userName');
        $password = $this->getPost('userPassword');

        require_once __DIR__ . '/../models/Researcher.php';
        $userModel = new Researcher();
        $user      = $userModel->authenticate($username, $password);

        if ($user) {
            $_SESSION['user'] = $user;
            $this->setFlash('success', 'Welcome, ' . $user['userName']);
            $this->redirect('/incidents');   // straight to incidents for testing
        } else {
            $this->setFlash('error', 'Invalid credentials.');
            $this->redirect('/login');
        }
    }

    public function logout()
    {
        session_destroy();
        $this->redirect('/login');
    }
}