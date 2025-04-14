<?php
session_start();
$db = new PDO("mysql:host=localhost;dbname=finaldb", "root");

// Get match ID from URL
$match_id = isset($_GET['match_id']) ? (int)$_GET['match_id'] : 0;
if ($match_id == 0) {
    die("Invalid match ID");
}

// Fetch match details
$match_query = "SELECT m.*, e1.nom AS domicile, e1.photo AS domicile_photo, e2.nom AS exterieur, e2.photo AS exterieur_photo, s.nom AS stade 
                FROM matches m 
                JOIN equipes e1 ON m.equipe_domicile_id = e1.id 
                JOIN equipes e2 ON m.equipe_exterieur_id = e2.id 
                JOIN stades s ON m.stade_id = s.id 
                WHERE m.id = ?";
$stmt = $db->prepare($match_query);
$stmt->execute([$match_id]);
$match = $stmt->fetch(PDO::FETCH_ASSOC);

// Fetch team statistics
$stats_query = "SELECT equipe_id, passes, tirs, corners, penalties, coups_franc, centres, hors_jeu, tirs 
                FROM equipe_statistics 
                WHERE match_id = ? AND equipe_id IN (?, ?)";
$stmt = $db->prepare($stats_query);
$stmt->execute([$match_id, $match['equipe_domicile_id'], $match['equipe_exterieur_id']]);
$team_stats = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate possession
$total_passes = 0;
$domicile_passes = 0;
$exterieur_passes = 0;
foreach ($team_stats as $stat) {
    $total_passes += $stat['passes'];
    if ($stat['equipe_id'] == $match['equipe_domicile_id']) {
        $domicile_passes = $stat['passes'];
    } elseif ($stat['equipe_id'] == $match['equipe_exterieur_id']) {
        $exterieur_passes = $stat['passes'];
    }
}
$domicile_possession = $total_passes > 0 ? round(($domicile_passes / $total_passes) * 100, 1) : 0;
$exterieur_possession = $total_passes > 0 ? round(($exterieur_passes / $total_passes) * 100, 1) : 0;

// Fetch staff for both teams
$staff_query = "SELECT s.nom, s.prenom, s.poste, s.equipe_id 
                FROM staff s 
                WHERE s.equipe_id IN (?, ?) AND (s.date_fin IS NULL OR s.date_fin > CURDATE())";
$stmt = $db->prepare($staff_query);
$stmt->execute([$match['equipe_domicile_id'], $match['equipe_exterieur_id']]);
$staff = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch team composition (with player photos)
$composition_query = "SELECT j.nom, j.prenom, j.photo, p.position, p.equipe_id 
                      FROM participations p 
                      JOIN joueurs j ON p.joueur_id = j.id 
                      WHERE p.match_id = ? AND p.titulaire = TRUE";
$stmt = $db->prepare($composition_query);
$stmt->execute([$match_id]);
$composition = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch comments
$comments_query = "SELECT c.message, c.created_at, co.login 
                   FROM commentaires c 
                   JOIN comptes co ON c.user_id = co.id_compte 
                   WHERE c.match_id = ? 
                   ORDER BY c.created_at DESC";
$stmt = $db->prepare($comments_query);
$stmt->execute([$match_id]);
$comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle comment submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment']) && isset($_SESSION['user_id'])) {
    $comment = trim($_POST['comment']);
    if (!empty($comment)) {
        $insert_query = "INSERT INTO commentaires (match_id, user_id, message) VALUES (?, ?, ?)";
        $stmt = $db->prepare($insert_query);
        $stmt->execute([$match_id, $_SESSION['user_id'], $comment]);
        header("Location: match_stats.php?match_id=$match_id");
        exit;
    }
}

$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'stats';
?>

<!DOCTYPE html>
<html>
<head>
    <title>Match Stats</title>
    <script src="https://cdn.tailwindcss.com"></script>

    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        /* General Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            color: #2E7D32; /* Dark Green */
            line-height: 1.6;
            overflow-x: hidden;
        }

        .container {
            max-width: 1300px;
            margin: 0 auto;
            padding: 30px;
        }

        /* Header */
        .header {
            background: linear-gradient(120deg, #2E7D32, #F57C00); /* Dark Green to Orange */
            color: white;
            padding: 50px 20px;
            text-align: center;
            font-size: 18px;
            font-weight: 600;


            border-radius: 12px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
            position: relative;
            overflow: hidden;
           

        }

        .header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.1);
            z-index: 0;
        }

        .header .match-line {
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 20px 0;
        }

        .header img {
            width: 90px;
            height: 90px;
            object-fit: cover;
            border-radius: 50%;
            margin: 0 25px;
            border: 4px solid #fff;
            transition: transform 0.3s ease;
        }

        .header img:hover {
            transform: scale(1.1);
        }

        .score {
            font-size: 48px;
            font-weight: 700;
            color: #fff;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }

        .vs {
            font-size: 28px;
            font-weight: 600;
            color: #F57C00; /* Orange */
            margin: 0 15px;
        }

        .header h1 {
            font-size: 32px;
            font-weight: 600;
            position: relative;
            z-index: 1;
        }

        .header p {
            font-size: 18px;
            opacity: 0.9;
            position: relative;
            z-index: 1;
        }

        /* Navbar */
        .navbar {
            background: #fff;
            padding: 15px 0;
            border-radius: 12px;
            margin-top: 20px;
            display: flex;
            justify-content: center;
            gap: 20px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 20px;
            z-index: 10;
        }

        .navbar a {
            color: #2E7D32; /* Dark Green */
            text-decoration: none;
            padding: 12px 30px;
            font-weight: 600;
            font-size: 16px;
            border-radius: 25px;
            transition: all 0.3s ease;
        }

        .navbar a:hover, .navbar a.active {
            background: linear-gradient(90deg, #F57C00, #E65100); /* Orange Gradient */
            color: white;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
        }

        /* Sections */
        .section {
            display: none;
            background: #fff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
            margin-top: 30px;
            animation: fadeIn 0.5s ease;
        }

        .section.active {
            display: block;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .stats, .staff, .composition {
            display: flex;
            justify-content: space-between;
            gap: 25px;
        }

        .team-box {
            width: 48%;
            background: #fff;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s ease;
        }

        .team-box:hover {
            transform: translateY(-5px);
        }

        .team-box h3 {
            color: #2E7D32; /* Dark Green */
            font-size: 26px;
            font-weight: 600;
            margin-bottom: 20px;
            border-bottom: 3px solid #F57C00; /* Orange */
            padding-bottom: 10px;
        }

        /* Stats Section */
        .stats-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 12px;
        }

        .stats-grid p {
            margin: 10px 0;
            padding: 12px;
            background: linear-gradient(135deg, #ecf0f1, #dfe4ea);
            border-radius: 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 16px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
        }

        .stats-grid p strong {
            font-weight: 600;
            color: #2E7D32; /* Dark Green */
        }

        .stats-grid p span {
            font-weight: 600;
            color: #F57C00; /* Orange */
        }

        /* Staff Section */
        .staff-item {
            margin: 15px 0;
            padding: 15px;
            background: #ecf0f1;
            border-radius: 8px;
            font-size: 16px;
            transition: background 0.3s ease;
        }

        .staff-item:hover {
            background: #dfe4ea;
        }

        .staff-item strong {
            font-weight: 600;
            color: #2E7D32; /* Dark Green */
        }

        /* Composition Section */
        .player-item {
            display: flex;
            align-items: center;
            margin: 15px 0;
            padding: 15px;
            background: #ecf0f1;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .player-item:hover {
            background: #dfe4ea;
            transform: translateX(5px);
        }

        .player-item img {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 50%;
            margin-right: 15px;
            border: 3px solid #F57C00; /* Orange */
            transition: transform 0.3s ease;
        }

        .player-item:hover img {
            transform: scale(1.1);
        }

        .player-item p {
            font-size: 16px;
            margin: 0;
        }

        .player-item p strong {
            font-weight: 600;
            color: #2E7D32; /* Dark Green */
        }

        /* Comments Section */
        .section h3 {
            color: #2E7D32; /* Dark Green */
            font-size: 26px;
            font-weight: 600;
            margin-bottom: 25px;
        }

        .comments form {
            margin-bottom: 25px;
        }

        .comments form textarea {
            width: 100%;
            padding: 15px;
            border: 2px solid #dfe4ea;
            border-radius: 8px;
            font-size: 16px;
            resize: vertical;
            min-height: 120px;
            background: #fff;
            transition: border-color 0.3s ease;
        }

        .comments form textarea:focus {
            border-color: #F57C00; /* Orange */
            outline: none;
        }

        .comments form input[type="submit"] {
            background: linear-gradient(90deg, #F57C00, #E65100); /* Orange Gradient */
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 25px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .comments form input[type="submit"]:hover {
            background: linear-gradient(90deg, #E65100, #D84315);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
        }

        .comments .login-btn {
            display: inline-block;
            background: linear-gradient(90deg, #2E7D32, #1B5E20); /* Dark Green Gradient */
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 600;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 10px;
        }

        .comments .login-btn:hover {
            background: linear-gradient(90deg, #1B5E20, #124D17);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
        }

        .comment {
            padding: 20px;
            border-bottom: 1px solid #dfe4ea;
            background: #fff;
            border-radius: 8px;
            margin-bottom: 15px;
            transition: transform 0.3s ease;
        }

        .comment:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
        }

        .comment strong {
            font-weight: 600;
            color: #2E7D32; /* Dark Green */
        }

        .comment span {
            color: #7f8c8d;
            font-size: 14px;
            margin-left: 10px;
        }

        .comment p {
            margin: 8px 0 0;
            font-size: 16px;
            color: #34495e;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .header {
                padding: 30px 15px;
            }

            .header img {
                width: 60px;
                height: 60px;
            }

            .score {
                font-size: 36px;
            }

            .vs {
                font-size: 20px;
                margin: 0 10px;
            }

            .header h1 {
                font-size: 24px;
            }

            .navbar {
                flex-wrap: wrap;
                padding: 10px;
                gap: 10px;
            }

            .navbar a {
                padding: 10px 20px;
                font-size: 14px;
            }

            .stats, .staff, .composition {
                flex-direction: column;
            }

            .team-box {
                width: 100%;
            }

            .section {
                padding: 20px;
            }

            .stats-grid {
                grid-template-columns: 1fr 1fr;
            }

            .player-item img {
                width: 45px;
                height: 45px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="match-line">
                <img src="<?php echo $match['domicile_photo'] ?? 'default_team.jpg'; ?>" alt="<?php echo $match['domicile']; ?>">
                <span class="score"><?php echo ($match['score_domicile'] ?? '-'); ?></span>
                <span class="vs">-</span>
                <span class="score"><?php echo ($match['score_exterieur'] ?? '-'); ?></span>
                <img src="<?php echo $match['exterieur_photo'] ?? 'default_team.jpg'; ?>" alt="<?php echo $match['exterieur']; ?>">
            </div>
            <h1 ><?php echo $match['domicile'] . "    vs                  " . $match['exterieur']; ?></h1>
            <p>Stade: <?php echo $match['stade']; ?> | <?php echo $match['date_match'] . " " . $match['heure_debut']; ?></p>
        </div>

        <!-- Navbar -->
        <div class="navbar">
            <a href="?match_id=<?php echo $match_id; ?>&tab=stats" class="<?php echo $active_tab === 'stats' ? 'active' : ''; ?>">Stats</a>
            <a href="?match_id=<?php echo $match_id; ?>&tab=staff" class="<?php echo $active_tab === 'staff' ? 'active' : ''; ?>">Staff</a>
            <a href="?match_id=<?php echo $match_id; ?>&tab=composition" class="<?php echo $active_tab === 'composition' ? 'active' : ''; ?>">Composition</a>
            <a href="?match_id=<?php echo $match_id; ?>&tab=comments" class="<?php echo $active_tab === 'comments' ? 'active' : ''; ?>">Commentaires</a>
            <a href="acceuil.php">Back Home</a>
        </div>

        <!-- Stats Section -->
        <div class="section <?php echo $active_tab === 'stats' ? 'active' : ''; ?>">
            <div class="stats">
                <div class="team-box">
                    <h3><?php echo $match['domicile']; ?> Stats</h3>
                    <div class="stats-grid">
                        <?php foreach ($team_stats as $stat) {
                            if ($stat['equipe_id'] == $match['equipe_domicile_id']) {
                                echo "<p><strong>Possession</strong><span>$domicile_possession%</span></p>";
                                echo "<p><strong>Passes</strong><span>" . $stat['passes'] . "</span></p>";
                                echo "<p><strong>Tirs</strong><span>" . $stat['tirs'] . "</span></p>";
                                echo "<p><strong>Tirs cadrés</strong><span>" . $stat['tirs'] . "</span></p>";
                                echo "<p><strong>Corners</strong><span>" . $stat['corners'] . "</span></p>";
                                echo "<p><strong>Pénalties</strong><span>" . $stat['penalties'] . "</span></p>";
                                echo "<p><strong>Coups francs</strong><span>" . $stat['coups_franc'] . "</span></p>";
                                echo "<p><strong>Centres</strong><span>" . $stat['centres'] . "</span></p>";
                                echo "<p><strong>Hors-jeu</strong><span>" . $stat['hors_jeu'] . "</span></p>";
                            }
                        } ?>
                    </div>
                </div>
                <div class="team-box">
                    <h3><?php echo $match['exterieur']; ?> Stats</h3>
                    <div class="stats-grid">
                        <?php foreach ($team_stats as $stat) {
                            if ($stat['equipe_id'] == $match['equipe_exterieur_id']) {
                                echo "<p><strong>Possession</strong><span>$exterieur_possession%</span></p>";
                                echo "<p><strong>Passes</strong><span>" . $stat['passes'] . "</span></p>";
                                echo "<p><strong>Tirs</strong><span>" . $stat['tirs'] . "</span></p>";
                                echo "<p><strong>Tirs cadrés</strong><span>" . $stat['tirs'] . "</span></p>";
                                echo "<p><strong>Corners</strong><span>" . $stat['corners'] . "</span></p>";
                                echo "<p><strong>Pénalties</strong><span>" . $stat['penalties'] . "</span></p>";
                                echo "<p><strong>Coups francs</strong><span>" . $stat['coups_franc'] . "</span></p>";
                                echo "<p><strong>Centres</strong><span>" . $stat['centres'] . "</span></p>";
                                echo "<p><strong>Hors-jeu</strong><span>" . $stat['hors_jeu'] . "</span></p>";
                            }
                        } ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Staff Section -->
        <div class="section <?php echo $active_tab === 'staff' ? 'active' : ''; ?>">
            <div class="staff">
                <div class="team-box">
                    <h3><?php echo $match['domicile']; ?> Staff</h3>
                    <?php foreach ($staff as $member) {
                        if ($member['equipe_id'] == $match['equipe_domicile_id']) {
                            echo "<div class='staff-item'><strong>" . $member['prenom'] . " " . $member['nom'] . "</strong> - " . $member['poste'] . "</div>";
                        }
                    } ?>
                </div>
                <div class="team-box">
                    <h3><?php echo $match['exterieur']; ?> Staff</h3>
                    <?php foreach ($staff as $member) {
                        if ($member['equipe_id'] == $match['equipe_exterieur_id']) {
                            echo "<div class='staff-item'><strong>" . $member['prenom'] . " " . $member['nom'] . "</strong> - " . $member['poste'] . "</div>";
                        }
                    } ?>
                </div>
            </div>
        </div>

        <!-- Composition Section -->
        <div class="section <?php echo $active_tab === 'composition' ? 'active' : ''; ?>">
            <div class="composition">
                <div class="team-box">
                    <h3><?php echo $match['domicile']; ?> Composition</h3>
                    <?php foreach ($composition as $player) {
                        if ($player['equipe_id'] == $match['equipe_domicile_id']) {
                            echo "<div class='player-item'>";
                            echo "<img src='" . ($player['photo'] ?? 'default_player.jpg') . "' alt='" . $player['prenom'] . " " . $player['nom'] . "'>";
                            echo "<p><strong>" . $player['prenom'] . " " . $player['nom'] . "</strong> - " . $player['position'] . "</p>";
                            echo "</div>";
                        }
                    } ?>
                </div>
                <div class="team-box">
                    <h3><?php echo $match['exterieur']; ?> Composition</h3>
                    <?php foreach ($composition as $player) {
                        if ($player['equipe_id'] == $match['equipe_exterieur_id']) {
                            echo "<div class='player-item'>";
                            echo "<img src='" . ($player['photo'] ?? 'default_player.jpg') . "' alt='" . $player['prenom'] . " " . $player['nom'] . "'>";
                            echo "<p><strong>" . $player['prenom'] . " " . $player['nom'] . "</strong> - " . $player['position'] . "</p>";
                            echo "</div>";
                        }
                    } ?>
                </div>
            </div>
        </div>

        <!-- Comments Section -->
        <div class="section <?php echo $active_tab === 'comments' ? 'active' : ''; ?>">
            <h3>Commentaires</h3>
            <?php if (isset($_SESSION['user_id'])): ?>
                <form method="POST" class="max-w-md mx-auto p-4 bg-white shadow-md rounded-xl space-y-4">
  <textarea
    name="comment"
    placeholder="Ajoutez un commentaire..."
    class="w-full h-32 p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"
  ></textarea>
  <input
    type="submit"
    value="Poster"
    class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 transition duration-300 cursor-pointer"
  >
</form>

            <?php else: ?>
                <p>Vous devez être connecté pour commenter.</p>
                <a href="login.php" class="login-btn">Connectez-vous</a>
            <?php endif; ?>
            <?php foreach ($comments as $comment): ?>
                <div class="comment">
                    <strong><?php echo $comment['login']; ?></strong> <span>(<?php echo $comment['created_at']; ?>)</span>
                    <p><?php echo htmlspecialchars($comment['message']); ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>