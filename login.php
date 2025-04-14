<?php
session_start();
include('database_connexion.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM comptes WHERE login = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id_compte'];
            $_SESSION['user_email'] = $user['login'];
            $_SESSION['Type_compte'] = $user['type_compte'];                    
            $_SESSION['ID'] = $user['id_compte'];
            $_SESSION['current_page'] = $_SERVER['REQUEST_URI'];

            switch ($user['type_compte']) {
                case 'admin_global':
                    header("Location: general_admin_panel.php");
                    exit();
                case 'user':
                    header("Location: acceuil.php");
                    exit();
                case 'admin_tournoi':
                  header("Location: ajout_match.php");
                  exit();
                default:
                    die("Invalid role.");
            }
        } else {
            $error_message = "Invalid password.";
        }
    } else {
        $error_message = "No user found with that login.";
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login Page</title>
  <script src="https://cdn.tailwindcss.com"></script>
  </head>
<body class="bg-[#1b191b] text-white">
  <div class="min-h-screen flex items-center justify-center">
    <div class="bg-[#282828] p-8 rounded-lg shadow-lg w-full max-w-md">
      <h2 class="text-2xl font-bold mb-6 text-center">Login</h2>

      <?php if (isset($error_message)): ?>
        <div class="text-red-500 mb-4"><?php echo $error_message; ?></div>
      <?php endif; ?>

      <form method="POST" action="login.php">
        <div class="mb-4">
          <label for="email" class="block text-sm font-medium mb-2">login</label>
          <input type="text" name="email" id="email" class="w-full p-3 rounded bg-[#282828] border border-gray-600 focus:outline-none focus:ring-2 focus:ring-[#F4A261]" required>
        </div>
        <div class="mb-6">
          <label for="password" class="block text-sm font-medium mb-2">Password</label>
          <input type="password" name="password" id="password" class="w-full p-3 rounded bg-[#282828] border border-gray-600 focus:outline-none focus:ring-2 focus:ring-[#F4A261]" required>
        </div>
        
        <button type="submit" class="mt-6 w-full py-3 bg-[#F4A261]  rounded text-white font-bold">Login</button>
        <p class="text-center text-sm mt-4">Premiere fois ? <a href="create_account.php" class="text-[#F4A261] hover:underline">Create account</a></p>

      </form>
    </div>
  </div>
</body>
</html>
