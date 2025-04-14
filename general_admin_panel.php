<?php 
session_start();
if (!isset($_SESSION['Type_compte']) || $_SESSION['Type_compte'] !== 'admin_global') {
    header('Location: acceuil.php');
    exit();
} 
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin General</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="style1.css">
    <link rel="stylesheet" href="style.css">
    </head>

<body class="bg-[#1b191b] font-sans text-white min-h-screen p-6">

    <h1 class="text-3xl md:text-4xl font-extrabold text-center mb-6 mt-0 bg-gradient-to-r from-blue-400 to-blue-600 bg-clip-text text-transparent">
        Welcome to the Admin General Panel
    </h1>
    <div class="flex justify-around">
      <div class=" w-3/4 md-lg">
        <!-- Button Container -->
        <div class="button-container w-full ">
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6  gap-3">
                <button class="menu-btn bg-[#3C94FA] p-1 rounded-lg" data-form="createTeamForm">
                    <h2 class="font-bold text-lg text-center text-white">New Teams</h2>
                </button>
                <button class="menu-btn bg-[#3C94FA] p-0 rounded-lg" data-form="createPlayerForm">
                    <h2 class="font-bold text-lg text-center text-white">New Player</h2>
                </button>
                <button class="menu-btn bg-[#3C94FA] p-1 rounded-lg" data-form="createStadiumForm">
                    <h2 class="font-bold text-lg text-center text-white">New Stadium</h2>
                </button>
                <button class="menu-btn bg-[#3C94FA] p-1 rounded-lg" data-form="createStaffForm">
                    <h2 class="font-bold text-lg text-center text-white">New Staff</h2>
                </button>
                <button class="menu-btn bg-[#3C94FA] p-1 rounded-lg" data-form="createAdminForm">
                    <h2 class="font-bold text-lg text-center text-white">Manage Users</h2>
                </button>
                <button class="menu-btn bg-[#3C94FA] p-1 rounded-lg" data-form="createArbitreForm">
                    <h2 class="font-bold text-lg text-center text-white">Add Arbitres</h2>
                </button>
            </div>
        </div>

        <!-- Form Section -->
        <div class="form-container bg-[#282828] p-4 rounded-lg shadow w-full mt-6">  
         
           <?php
                require_once 'fetch_all_element.php';
                $teams = fetchAllRecords('equipes');
            ?>   
            
        

            <!-- Add Staff Form -->
            <form id="createStaffForm" action="general_admin_panel/process_create_staff.php" method="POST" class="hidden">
                <h3 class="text-xl text-center font-semibold mb-4">Add Staff</h3>

                <div class="flex items-center mb-4">
                    <label for="create_staff_team" class="w-1/3">Sélectionner Une Équipe :</label>
                    <select name="team_id" id="create_staff_team" class="bg-[#282828]" required>
                        <option value="">Sélectionnez une équipe</option>
                        <?php foreach ($teams as $team): ?>
                            <option value="<?= htmlspecialchars($team['id']) ?>" 
                                    data-nom="<?= htmlspecialchars($team['nom']) ?>" 
                                    data-ville="<?= htmlspecialchars($team['ville']) ?>">
                                <?= htmlspecialchars($team['nom']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div> 
                <div class="flex items-center mb-4">
                        <label for="nom" class="w-1/3">Nom :</label>
                        <input type="text" id="nom" name="nom" placeholder="Nom du staff" class="mystyle1" required>
                </div>
                <div class="flex items-center mb-4">
                        <label for="prenom" class="w-1/3">Prenom :</label>
                        <input type="text" id="prenom" name="prenom" placeholder="Prenom du staff" class="mystyle1" required>
                </div>
                <div class="flex items-center mb-4">
                       <label for="date_naissance" class="w-1/3">Date de Naissance :</label>
                       <input type="date" id="date_naissance" name="date_naissance" class="mystyle1" required>
                </div>
                <div class="flex items-center mb-4">
                        <label for="post" class="w-1/3">Post :</label>
                        <select id="post" name="post" class="bg-[#282828]" required>
                           <option value="">Sélectionnez un post</option>
                            <option value="Entraîneur principal">Entraîneur principal</option>
                            <option value="Entraîneur adjoint">Entraîneur adjoint</option>
                            <option value="Préparateur physique">Préparateur physique</option>
                            <option value="Médecin">Médecin</option>
                        </select>        
                </div>
                <div class="flex items-center mb-4">
                       <label for="date_debut" class="w-1/3">Date de Debut:</label>
                       <input type="date" id="date_debut" name="date_debut" class="mystyle1" required>
                </div>
                <div class="flex items-center mb-4">
                       <label for="date_fin" class="w-1/3">Date de Fin :</label>
                       <input type="date" id="date_fin" name="date_fin" class="mystyle1" required>
                </div>
                <button type="submit" class="green_button w-full">🚀 Soumettre</button>
            </form>
 <!-- update Staff Form -->
        <form id="updateStaffForm" action="general_admin_panel/process_manage_staff.php" method="POST" class="hidden">
                <h3 class="text-xl text-center font-semibold mb-4">Manage Staff</h3>
                <div class="flex items-center mb-4">
                <label for="update_staff_team_selection" class="w-1/3">Équipe :</label>
                    <select name="team_id" id="update_staff_team_selection" class="bg-[#282828]" required>
                        <option value="">Sélectionnez une équipe</option>
                        <?php foreach ($teams as $team): ?>
                            <option value="<?= htmlspecialchars($team['id']) ?>" 
                                    data-nom="<?= htmlspecialchars($team['nom']) ?>" 
                                    data-ville="<?= htmlspecialchars($team['ville']) ?>">
                                <?= htmlspecialchars($team['nom']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="flex items-center mb-4">
                <label for="update_staff_selection" class="w-1/3">Staff :</label>
                <select name="staff_id" id="update_staff_selection" class="mystyle1 bg-[#282828]" required>
                    <option value="">Sélectionnez un joueur</option>
                </select>
                </div>
                <div class="flex items-center mb-4">
                        <label for="update_staff_nom" class="w-1/3">Nom :</label>
                        <input type="text" id="update_staff_nom" name="nom" placeholder="Nom du staff" class="mystyle1" required>
                </div>
                <div class="flex items-center mb-4">
                        <label for="update_staff_prenom" class="w-1/3">Prenom :</label>
                        <input type="text" id="update_staff_prenom" name="prenom" placeholder="Prenom du staff" class="mystyle1" required>
                </div>
                <div class="flex items-center mb-4">
                       <label for="update_staff_naissance" class="w-1/3">Date de Naissance :</label>
                       <input type="date" id="update_staff_naissance" name="date_naissance" class="mystyle1" required>
                </div>
                
                <div class="flex items-center mb-4">
                    <label for="update_staff_poste" class="w-1/3">Post :</label>
                    <select id="update_staff_poste" name="post" class="bg-[#282828]" required>
                            <option value="">Sélectionnez un post</option>
                            <option value="Entraineur_principal">Entraîneur principal</option>
                            <option value="Entraineur_adjoint">Entraîneur adjoint</option>
                            <option value="Preparateur_physique">Préparateur physique</option>
                            <option value="Medecin">Médecin</option>
                    </select>
                </div>
                <div class="flex items-center mb-4">
                       <label for="update_staff_date_debut" class="w-1/3">Date de Debut:</label>
                       <input type="date" id="update_staff_date_debut" name="date_debut" class="mystyle1" required>
                </div>
                <div class="flex items-center mb-4">
                       <label for="update_staff_date_fin" class="w-1/3">Date de Fin :</label>
                       <input type="date" id="update_staff_date_fin" name="date_fin" class="mystyle1" required>
                </div>
                <div class="flex items-center mb-4">
                        <button type="submit" name="update_staff" class="green_button w-full ">🚀 Update</button>
                        <div class="w-5"></div>
                        <button type="submit" name="drop_staff" class="red_button w-full">🚀 Drop Staff</button>
                    </div> 
            </form>
            <!-- Add Stadium Form -->
            <form id="createStadiumForm" action="general_admin_panel/process_create_stadium.php" method="POST" class="hidden">
                     <h3 class="text-xl text-center font-semibold mb-4">Add Stadium</h3>
                    <div class="flex items-center mb-4">
                        <label for="nom" class="w-1/3">Nom :</label>
                        <input type="text" id="nom" name="nom" placeholder="Nom de  l'equipe" class="mystyle1" required>
                    </div>
                    <div class="flex items-center mb-4">
                        <label for="ville" class="w-1/3">Ville :</label>
                        <input type="text" id="ville" name="ville" placeholder="Ville de l'equipe" class="mystyle1" required>
                    </div>
                      <div class="flex items-center mb-4">
                       <label for="capacite" class="w-1/3">Capacite :</label>
                       <input type="number" id="capacite" name="capacite" placeholder="Capacite du stade" class="mystyle1" required>
                      </div>
                <button type="submit" class="green_button w-full">🚀 Soumettre</button>
            </form>
              <!-- Update Stadium Form -->
              <?php
                require_once 'fetch_all_element.php';
                $stadiums = fetchAllRecords('stades');
            ?>
            <form id="updateStadiumForm" action="general_admin_panel/process_manage_stadium.php" method="POST" class="hidden">
                     <h3 class="text-xl text-center font-semibold mb-4">Manage Stadium</h3>
                     <div class="flex items-center mb-4">
                    <label for="update_stadium_selections" class="w-1/3">Sélectionner Un Stade :</label>
                    <select name="stadium_id" id="update_stadium_selections" class="bg-[#282828]" required>
                        <option value="">Sélectionnez une stade</option>
                        <?php foreach ($stadiums as $stadium): ?>
                            <option value="<?= htmlspecialchars($stadium['id']) ?>" 
                                    data-nom="<?= htmlspecialchars($stadium['nom']) ?>" 
                                    data-ville="<?= htmlspecialchars($stadium['ville']) ?>"
                                    data-capacite="<?= htmlspecialchars($stadium['capacite']) ?>">
                                <?= htmlspecialchars($stadium['nom']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                     <div class="flex items-center mb-4">
                        <label for="update_stadium_nom" class="w-1/3">Nom :</label>
                        <input type="text" id="update_stadium_nom" name="nom" placeholder="Nom de  l'equipe" class="" required>
                    </div>
                    <div class="flex items-center mb-4">
                        <label for="update_stadium_ville" class="w-1/3">Ville :</label>
                        <input type="text" id="update_stadium_ville" name="ville" placeholder="Ville de l'equipe" class="mystyle1" required>
                    </div>
                      <div class="flex items-center mb-4">
                       <label for="update_stadium_capacite" class="w-1/3">Capacite :</label>
                       <input type="number" id="update_stadium_capacite" name="capacite" placeholder="Capacite du stade" class="mystyle1" required>
                      </div>
                      <div class="flex items-center mb-4">
                        <button type="submit" name="update_stadium" class="green_button w-full ">🚀 Update</button>
                        <div class="w-5"></div>
                        <button type="submit" name="drop_stadium" class="red_button w-full">🚀 Drop Stadium</button>
                    </div> 
            </form>
            <!-- Add Team Form -->
            <form enctype="multipart/form-data" id="createTeamForm" action="general_admin_panel/process_create_equipe.php" method="POST" class="hidden">
                     <h3 class="text-xl text-center font-semibold mb-4">Add Team</h3>
                    <div class="flex items-center mb-4">
                        <label for="nom" class="w-1/3">Nom :</label>
                        <input type="text" id="nom" name="nom" placeholder="Nom de  l'equipe" class="" required>
                    </div>
                    <div class="flex items-center mb-4">
                        <label for="ville" class="w-1/3">Ville :</label>
                        <input type="text" id="ville" name="ville" placeholder="Ville de l'equipe" class="mystyle1" required>
                    </div>
                    <div class="flex items-center mb-4">
                        <label for="photo" class="w-1/3">Photo :</label>
                        <input type="file" id="photo" name="photo" accept="image/*"  required>
                    </div>
                <button type="submit" class="green_button w-full">🚀 Soumettre</button>
            </form>
          <!-- Add Arbitre Form -->
          <form id="createArbitreForm" action="general_admin_panel/process_create_arbitre.php" method="POST" class="hidden">
                     <h3 class="text-xl text-center font-semibold mb-4">Add Arbitre</h3>
                    <div class="flex items-center mb-4">
                        <label for="nom" class="w-1/3">Nom :</label>
                        <input type="text" id="nom" name="nom" placeholder="Nom de  l'equipe" class="mystyle1" required>
                    </div>
                    <div class="flex items-center mb-4">
                        <label for="grade" class="w-1/3">Grade :</label>
                        <input type="text" id="ville" name="grade" placeholder="Grade de l'arbitre" class="mystyle1" required>
                    </div>
                <button type="submit" class="green_button w-full">🚀 Soumettre</button>
            </form>
            <?php
                require_once 'fetch_all_element.php';
                $arbitres = fetchAllRecords('arbitres');
            ?>
          <!-- Update Arbitre Form -->
          <form id="updateArbitreForm" action="general_admin_panel/process_manage_arbitre.php" method="POST" class="hidden">
                     <h3 class="text-xl text-center font-semibold mb-4">Manage Arbitre</h3>
                     <div class="flex items-center mb-4">
                    <label for="update_arbitre_selections" class="w-1/3">Arbitres:</label>
                    <select name="arbitre_id" id="update_arbitre_selections" class="bg-[#282828]" required>
                        <option value="">Sélectionnez un arbitre</option>
                        <?php foreach ($arbitres as $arbitre): ?>
                            <option value="<?= htmlspecialchars($arbitre['id']) ?>" 
                                    data-nom="<?= htmlspecialchars($arbitre['nom']) ?>" 
                                    data-grade="<?= htmlspecialchars($arbitre['grade']) ?>"
                                   >
                                <?= htmlspecialchars($arbitre['nom']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                    <div class="flex items-center mb-4">
                        <label for="update_arbitre_nom" class="w-1/3">Nom :</label>
                        <input type="text" id="update_arbitre_nom" name="nom" placeholder="Nom de  l'equipe" class="mystyle1" required>
                    </div>
                    <div class="flex items-center mb-4">
                        <label for="update_arbitre_grade" class="w-1/3">Grade :</label>
                        <input type="text" id="update_arbitre_grade" name="grade" placeholder="Grade de l'arbitre" class="mystyle1" required>
                    </div>
                    <div class="flex items-center mb-4">
                        <button type="submit" name="update_arbitre" class="green_button w-full ">🚀 Update</button>
                        <div class="w-5"></div>
                        <button type="submit" name="drop_arbitre" class="red_button w-full">🚀 Drop Arbitre</button>
                    </div> 
            </form>
             <!-- Manage user Form -->
             <?php
                require_once 'fetch_all_element.php';
                $users = fetchAllRecords('comptes');
            ?>
             <form id="createAdminForm" action="general_admin_panel/process_create_admin.php" method="POST" class="hidden">
                     <h3 class="text-xl text-center font-semibold mb-4">Change The Users Type Here.</h3>
                     <div class="flex items-center mb-4">
                    <label for="admin_type_selection" class="w-1/3">New Type :</label>
                    <select id="admin_type_selection" name="admin_type" class="bg-[#282828]" required>
                            <option value="">Sélectionnez un type</option>
                            <option value="user">Normale User</option>
                            <option value="admin_tournoi">Admin de Tournois</option>
                           <!-- <option value="admin_global">Admin Globale</option> -->
                    </select>
                </div>
                <div class="flex items-center mb-4">
                    <label for="create_admin_selections" class="w-1/3">Users:</label>
                    <select name="user_id" id="create_admin_selections" class="bg-[#282828]" required>
                        <option value="">Sélectionnez un user</option>
                        <?php foreach ($users as $user): ?>
                            <?php if ($user['type_compte'] != 'admin_global'): ?>
                                <option value="<?= htmlspecialchars($user['id_compte']) ?>" 
                                        data-login="<?= htmlspecialchars($user['login']) ?>"
                                        data-type_compte="<?= htmlspecialchars($user['type_compte']) ?>">
                                    <?= htmlspecialchars($user['login']) ?>
                                </option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="flex items-center mb-4">
                    <label for="user_curent_type" class="w-1/3">Actual type :</label>
                    <input type="text" id="user_curent_type"  name="type" readonly required>
                </div>
                <button type="submit" class="green_button w-full">🚀 Update user type</button>
            </form>

             <!-- updateteamForm -->
           
<form id="updateTeamForm" action="general_admin_panel/process_manage_equipe.php" method="POST" class="hidden">
    <h3 class="text-xl text-center font-semibold mb-4">Manage Team</h3>
    
    <!-- Sélection de l'équipe -->
    <div class="flex items-center mb-4">
        <label for="update_team_selection" class="w-1/3">Sélectionner Une Équipe :</label>
        <select name="team_id" id="update_team_selection" class="mystyle1 bg-[#282828]" required>
            <option value="">Sélectionnez une équipe</option>
            <?php foreach ($teams as $team): ?>
                <option value="<?= htmlspecialchars($team['id']) ?>"
                        data-nom="<?= htmlspecialchars($team['nom']) ?>"
                        data-ville="<?= htmlspecialchars($team['ville']) ?>"
                        data-photo="<?= htmlspecialchars($team['photo']) ?>">
                    <?= htmlspecialchars($team['nom']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <!-- Champ Nom -->
    <div class="flex items-center mb-4">
        <label for="update_team_nom" class="w-1/3">Nom :</label>
        <input type="text" id="update_team_nom" name="nom" placeholder="Nom de l'équipe" class="mystyle1" required>
    </div>

    <!-- Champ Ville -->
    <div class="flex items-center mb-4">
        <label for="update_team_ville" class="w-1/3">Ville :</label>
        <input type="text" id="update_team_ville" name="ville" placeholder="Ville de l'équipe" class="mystyle1" required>
    </div>
    <img src="" width="90" id="update_team_photo" alt="" class="mx-auto my-5 justify-center">

    <!-- Boutons d'action -->
    <div class="flex items-center mb-4">
        <button type="submit" name="update_team" class="green_button w-full">🛠💊 Update</button>
        <div class="w-5"></div>
        <button type="submit" name="drop_team" class="red_button w-full">🔥💥 Drop Team</button>
    </div>

</form>

<!-- Formulaire de création de joueur -->
<form enctype="multipart/form-data" id="createPlayerForm" action="general_admin_panel/process_create_joueur.php" method="POST" class="hidden">
    <h3 class="text-xl text-center font-semibold mb-4">Create Player</h3>

    <div class="grid grid-cols-2 gap-4">
        <!-- Colonne 1 -->
        <div>
            <!-- Sélection de l'équipe -->
            <div class="flex items-center mb-4">
                <label for="create_player_team" class="w-1/3">Équipe :</label>
                <select name="team_id" id="create_player_team" class="bg-[#282828]" required>
                    <option value="">Sélectionnez une équipe</option>
                    <?php foreach ($teams as $team): ?>
                        <option value="<?= htmlspecialchars($team['id']) ?>">
                            <?= htmlspecialchars($team['nom']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="flex items-center mb-4">
                <label for="player_nom" class="w-1/3">Nom :</label>
                <input type="text" id="player_nom" name="player_nom" placeholder="Nom du joueur" class="mystyle1" required>
            </div>

            <div class="flex items-center mb-4">
                <label for="date_naissance" class="w-1/3">Date de Naissance :</label>
                <input type="date" id="date_naissance" name="date_naissance" class="mystyle1" required>
            </div>

            <div class="flex items-center mb-4">
                <label for="position" class="w-1/3">Position :</label>
                <select id="position" name="position" class="bg-[#282828]" required>
                    <option value="">Sélectionnez une position</option>
                    <?php
                    $positions = [
                        "gardien" => "Gardien de but",
                        "defenseur_central" => "Défenseur central",
                        "defenseur_lateral" => "Défenseur latéral",
                        "milieu_defensif" => "Milieu défensif",
                        "milieu_offensif" => "Milieu offensif",
                        "ailier_droit" => "Ailier droit",
                        "ailier_gauche" => "Ailier gauche",
                        "attaquant" => "Attaquant"
                    ];
                    foreach ($positions as $key => $value) {
                        echo "<option value='$key'>" . htmlspecialchars($value) . "</option>";
                    }
                    ?>
                </select>
            </div>

            <div class="flex items-center mb-4">
                <label for="date_debut" class="w-1/3">Date de Début :</label>
                <input type="date" id="date_debut" name="date_debut" class="mystyle1" required>
            </div>

            <div class="flex items-center mb-4">
                <label for="date_fin" class="w-1/3">Date de Fin :</label>
                <input type="date" id="date_fin" name="date_fin" class="mystyle1" required>
            </div>
        </div>

        <!-- Colonne 2 -->
        <div>
            <div class="flex items-center mb-4">
                <label for="player_prenom" class="w-1/3">Prénom :</label>
                <input type="text" id="player_prenom" name="player_prenom" placeholder="Prénom du joueur" class="mystyle1" required>
            </div>

            <div class="flex items-center mb-4">
                <label for="nationalite" class="w-1/3">Nationalité :</label>
                <input type="text" id="nationalite" name="nationalite" placeholder="Nationalité" class="mystyle1" required>
            </div>
            <div class="flex items-center mb-4">
                    <label for="origine" class="w-1/3">Origine :</label>
                    <input type="text" id="origine" name="origine" placeholder="origine" required>
            </div>
            <div class="flex items-center mb-4">
                <label for="numero_maillot" class="w-1/3">Numéro de Maillot :</label>
                <input type="number" id="numero_maillot" name="numero_maillot" placeholder="Numéro du maillot" class="mystyle1" required>
            </div>
            <div class="flex items-center mb-4">
                <label for="photo" class="w-1/3">Photo :</label>
                <input type="file" id="photo" name="photo" accept="image/png, image/jpeg" class="mystyle1" required>
            </div>
        </div>
    </div>
    <button type="submit" class="green_button w-full">🚀 Soumettre</button>
</form>

    <!-- update Player Form -->
            <?php
                require_once 'fetch_all_element.php';
                $players = fetchAllRecords('joueurs');
            ?>
            <form id="updatePlayerForm" action="../general_admin_panel/process_manage_joueur.php" method="POST" class="hidden">
                 <h3 class="text-xl text-center font-semibold mb-4">Manage Players</h3>

                <div class="grid grid-cols-2 gap-4">
                <!-- Colonne 1 -->
                <div>
                <div class="flex items-center mb-4">
                <label for="update_player_team_selection" class="w-1/3">Équipe :</label>
                    <select name="team_id" id="update_player_team_selection" class="bg-[#282828]" required>
                        <option value="">Sélectionnez une équipe</option>
                        <?php foreach ($teams as $team): ?>
                            <option value="<?= htmlspecialchars($team['id']) ?>" 
                                    data-nom="<?= htmlspecialchars($team['nom']) ?>" 
                                    data-ville="<?= htmlspecialchars($team['ville']) ?>">
                                <?= htmlspecialchars($team['nom']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="flex items-center mb-4">
                <label for="update_player_selection" class="w-1/3">joueurs :</label>
                <select name="player_id" id="update_player_selection" class="mystyle1 bg-[#282828]" required>
                    <option value="">Sélectionnez un joueur</option>
                </select>
                </div>
                <div class="flex items-center mb-4">
                    <label for="update_player_nom" class="w-1/3">Nom :</label>
                    <input type="text" id="update_player_nom" name="player_nom" placeholder="Nom du joueur" class="" required>
                </div>
                <div class="flex items-center mb-4">
                    <label for="update_player_naissance" class="w-1/3">Date de Naissance :</label>
                    <input type="date" id="update_player_naissance" name="date_naissance" class="mystyle1" required>
                </div>
                <div class="flex items-center mb-4">
                    <label for="update_player_position" class="w-1/3">Position :</label>
                    <select id="update_player_position" name="position" class="bg-[#282828]" required>
                            <option value="">Sélectionnez une position</option>
                            <option value="gardien">Gardien de but</option>
                            <option value="defenseur_central">Défenseur central</option>
                            <option value="defenseur_lateral">Défenseur latéral</option>
                            <option value="milieu_defensif">Milieu défensif</option>
                            <option value="milieu_offensif">Milieu offensif</option>
                            <option value="ailier_droit">Ailier droit</option>
                            <option value="ailier_gauche">Ailier gauche</option>
                            <option value="attaquant">Attaquant</option>
                    </select>
                </div>
                <div class="flex items-center mb-4">
                       <label for="update_player_date_debut" class="w-1/3">Date de Debut:</label>
                       <input type="date" id="update_player_date_debut" name="date_debut" class="mystyle1" required>
                </div>
                <div class="flex items-center mb-4">
                       <label for="update_player_date_fin" class="w-1/3">Date de Fin :</label>
                       <input type="date" id="update_player_date_fin" name="date_fin" class="mystyle1" required>
                </div>
                </div>

                <!-- Colonne 2 -->
                <div>
                <div class="flex items-center mb-4">
                    <label for="update_player_prenom" class="w-1/3">Prénom :</label>
                    <input type="text" id="update_player_prenom" name="player_prenom" placeholder="Prénom du joueur" class="mystyle1" required>
                </div>

                <div class="flex items-center mb-4">
                    <label for="update_player_nationalite" class="w-1/3">Nationalité :</label>
                    <input type="text" id="update_player_nationalite" name="nationalite" placeholder="Nationalité" class="mystyle1" required>
                </div>
                <div class="flex items-center mb-4">
                    <label for="update_player_origine" class="w-1/3">Origine:</label>
                    <input type="text" id="update_player_origine" name="origine" placeholder="Origine"  required>
                </div>

                <div class="flex items-center mb-4">
                    <label for="update_player_numero_maillot" class="w-1/3">Numéro de Maillot :</label>
                    <input type="number" id="update_player_numero_maillot" name="numero_maillot" placeholder="Numéro du maillot" class="mystyle1" required>
                </div>
                <img src="" width="90" id="update_player_photo" alt="" class="mx-auto my-5 justify-center">
                </div>
                </div>
                <div class="flex items-center mb-4">
                        <button type="submit" name="update_player" class="green_button w-full ">🚀 Update</button>
                        <div class="w-5"></div>
                        <button type="submit" name="drop_player" class="red_button w-full">🧨 Drop Player</button>
                </div> 
            </form>
        </div>
      </div>
       <div class="w-5"></div>
           <!-- Dashboard Section -->
        <div class=" bg-gray-800 p-6 rounded-xl mb-6 shadow-lg">
            <h2 class="text-2xl font-bold text-center mb-4 text-blue-300">Dashboard</h2>
            <div class="grid grid-cols-2 gap-4">
            <?php   require_once 'count_table_elements.php'; // Inclure les fonctions
               $totalTeams = countRecords('equipes'); // Compter la table equipes?>
                <div data-form="updateTeamForm" class="menu-btn bg-gradient-to-br from-cyan-600 to-blue-700 p-4 rounded-lg shadow-md" >
                    <h2 class="font-bold text-center  text-white">Équipes </h2>
                    <h2 class="font-bold text-center text-white"><?= htmlspecialchars($totalTeams) ?></h2>
                </div>
                <?php   require_once 'count_table_elements.php'; // Inclure les fonctions
               $totalArbitres = countRecords('arbitres'); // Compter la table equipes?>
                <div data-form="updateArbitreForm" class="menu-btn bg-gradient-to-br from-cyan-600 to-blue-700 p-4 rounded-lg shadow-md">
                    <h2 class="font-bold text-center text-white">Arbitres</h2>
                    <h2 class="font-bold text-center text-white"><?= htmlspecialchars( $totalArbitres) ?> </h2>
                </div>
                <?php   require_once 'count_table_elements.php'; // Inclure les fonctions
               $totalPlayers = countRecords('joueurs'); // Compter la table equipes?>
                <div data-form="updatePlayerForm"  class="menu-btn bg-gradient-to-br from-cyan-600 to-blue-700 p-4 rounded-lg shadow-md">
                    <h2 class="font-bold text-center text-white">Players</h2>
                    <h2 class="font-bold text-center text-white"><?= htmlspecialchars($totalPlayers) ?> </h2>
                </div>
                <?php   require_once 'count_table_elements.php'; // Inclure les fonctions
               $totalStadium = countRecords('stades'); // Compter la table equipes?>
                <div data-form="updateStadiumForm"  class="menu-btn  bg-gradient-to-br from-cyan-600 to-blue-700 p-4 rounded-lg shadow-md">
                    <h2 class="font-bold text-center text-white">Stadiums</h2>
                    <h2 class="font-bold text-center text-white"><?= htmlspecialchars($totalStadium) ?> </h2>
                </div>
                <?php   require_once 'count_table_elements.php'; // Inclure les fonctions
               $totalStaff = countRecords('staff'); ?>
                <div data-form="updateStaffForm"  class="menu-btn  bg-gradient-to-br from-cyan-600 to-blue-700 p-4 rounded-lg shadow-md">
                    <h2 class="font-bold text-center text-white">Staffes</h2>
                    <h2 class="font-bold text-center text-white"><?= htmlspecialchars($totalStaff) ?> </h2>
                </div>
                <?php   require_once 'count_table_elements.php'; // Inclure les fonctions
               $totalnormalusers = countRecordsByColumnValue('comptes',"type_compte ","user"); // Compter la table equipes?>
                <div data-form="updateNormalUserForm"  class="menu-btn  bg-gradient-to-br from-cyan-600 to-blue-700 p-4 rounded-lg shadow-md">
                    <h2 class="font-bold text-center text-white">Normal users</h2>
                    <h2 class="font-bold text-center text-white"><?= htmlspecialchars($totalnormalusers) ?> </h2>
                </div>
                <?php   require_once 'count_table_elements.php'; // Inclure les fonctions
               $totaladmingeneral =  countRecordsByColumnValue('comptes',"type_compte ","admin_global");?>
                <div data-form=""  class="menu-btn  bg-gradient-to-br from-cyan-600 to-blue-700 p-4 rounded-lg shadow-md">
                    <h2 class="font-bold text-center text-white">Admin general</h2>
                    <h2 class="font-bold text-center text-white"><?= htmlspecialchars($totaladmingeneral) ?> </h2>
                </div>
                <?php   require_once 'count_table_elements.php'; // Inclure les fonctions
               $totaladmintournois= countRecordsByColumnValue('comptes',"type_compte ","admin_tournoi");?>
                <div data-form=""  class="menu-btn  bg-gradient-to-br from-cyan-600 to-blue-700 p-4 rounded-lg shadow-md">
                    <h2 class="font-bold text-center text-white">Admin tournois</h2>
                    <h2 class="font-bold text-center text-white"><?= htmlspecialchars($totaladmintournois) ?> </h2>
                </div>
            <a href="logout.php">
                <div  class="logout bg-red p-4 rounded-lg shadow-md">
                    <h2 class="font-bold text-center  text-white">LogOut </h2>
                </div>
            </a> 
            </div>
        </div>
        
    </div>

    <script src="general_admin_panel/main.js"> </script>
    <style>
        .logout{
          background-color: light;
        }
    </style>

</body>

</html>
