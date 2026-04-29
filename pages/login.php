<?php

require_once __DIR__ . '/../config/Database.php';

$error_message = '';
$success_message = '';

$pdo = Database::getInstance()->getConnection();

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';


    if (empty($username) || empty($password)) {
        $error_message = "Please enter both username and password.";
    } else {

        $stmt = $pdo->prepare('SELECT * FROM Users WHERE userName = ?');
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['userPassword'])) {
            $_SESSION['userID'] = $user['userID'];
            $_SESSION['userName'] = $user['userName'];
            $_SESSION['userType'] = $user['userType'];

            switch ($user['userType']) {
                case 'faculty_pi':
                    echo "hi faculty_pi\n";
                    break;

                case 'lab_manager':
                    echo "lab_manager";
                    break;

                case 'researcher':
                    echo "hi researcher";
                    break;

                case 'guest_researcher':
                    echo "hi guest";
                    break;


                default:
                    $error_message = "Account configuration error. Please contact IT.";
                    session_destroy();
                    break;
            }

        } else {
            $error_message = "Invalid email/username or password.";
        }
    }
}

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Page</title>
    <link href="https://cdn.jsdelivr.net/npm/bootswatch@5.3.3/dist/cosmo/bootstrap.min.css" rel="stylesheet"></head>

<body class="bg-light">

    <div class="container d-flex justify-content-center align-items-center vh-100">

        <div class="card p-4 shadow" style="width: 100%; max-width: 400px;">
            <h2 class="text-center mb-4">Welcome Back</h2>

            <?php if (!empty($error_message)): ?>
                <div class="alert alert-danger" role="alert">
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($success_message)): ?>
                <div class="alert alert-success" role="alert">
                    <?php echo $success_message; ?>
                </div>
            <?php endif; ?>

            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
                <div class="mb-3">
                    <label for="username" class="form-label">Email or Username</label>
                    <input type="text" id="username" name="username" class="form-control" placeholder="Enter your email" >
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" id="password" name="password" class="form-control" placeholder="Enter your password" >
                </div>

                <button type="submit" class="btn btn-primary w-100">Log In</button>
            </form>


        </div>

    </div>

</body>
</html>
