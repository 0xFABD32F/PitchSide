<?php
session_start();

require_once 'database_connexion.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Récupération des données avec sanitisation de base
    $email = trim(string: htmlspecialchars($_POST['email']));
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];
    $type = 'user';  // On attribue par défaut le type "normal user" lors de la création d'un compte.

    // Validation des champs
    if (empty($email) || empty($password) || empty($confirmPassword)) {
        die("Tous les champs sont obligatoires.");
    }


    if ($password !== $confirmPassword) {
        die("Les mots de passe ne correspondent pas.");
    }

    if (strlen($password) < 2) {
        die("Le mot de passe doit contenir au moins 2 caractères.");
    }

    try {
        // Vérifier si l'email existe déjà
        $stmt = $pdo->prepare("SELECT * FROM comptes WHERE login = :email");
        $stmt->execute(['email' => $email]);
        if ($stmt->fetch()) {
            die("Cet email est déjà utilisé.");
        }

        // Hasher le mot de passe
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        // Insérer l'utilisateur
        $stmt = $pdo->prepare("INSERT INTO comptes (login, password, type_compte) VALUES ( :email, :password, :type)");
        $stmt->execute([
            'email' => $email,
            'password' => $hashedPassword,
            'type' => $type // On ajoute le type ici
        ]);
        $_SESSION['user_id'] = $pdo->lastInsertId();
        header("Location: acceuil.php");
        exit();
    } catch (PDOException $e) {
        die("Erreur lors de l'inscription : " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-[#1b191b] text-white flex justify-center items-center h-screen">
    <div class="bg-[#282828] p-8 rounded-lg shadow-lg w-96 ">
        <h2 class="text-center text-2xl mb-6">Créer un compte</h2>
        <form method="POST" action="">
            <input type="text" name="email" placeholder="Login" required class="w-full p-2 mb-4 rounded bg-gray-700 text-white">
            <input type="password" name="password" placeholder="Mot de passe" required class="w-full p-2 mb-4 rounded bg-gray-700 text-white">
            <input type="password" name="confirm_password" placeholder="Confirmer le mot de passe" required class="w-full p-2 mb-4 rounded bg-gray-700 text-white">
            <div class="flex items-center mb-4">
                <input type="checkbox" id="terms" required class="mr-2">
                <label for="terms" class="text-sm">J'accepte les <a href="#" class="text-[#F4A261] hover:underline">conditions générales</a></label>
            </div>
            <button type="submit" class="w-full p-2 rounded bg-[#F4A261]  text-white text-lg">S'inscrire</button>
        </form>
        <p class="text-center text-sm mt-4">Déjà un compte ? <a href="login.php" class="text-[#F4A261] hover:underline">Se connecter</a></p>
        
        <p class="text-center text-sm mt-4"> <a href="acceuil.php" class="text-[#F4A261]  hover:underline">back home</a></p>

    </div>
</body>
</html>
