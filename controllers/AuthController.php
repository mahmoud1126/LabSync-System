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
            
            $role = $user['role'] ?? ''; 

            switch ($role) {
                case 'lab_manager':
                    $this->redirect('/incidents');
                    break;
                case 'pi':
                    $this->redirect('/pi/dashboard');
                    break;
                case 'researcher':
                    $this->redirect('/researcher/dashboard');
                    break;
                default:
                    $this->redirect('/dashboard'); 
                    break;
            }
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