<?php
session_start();

// Connexion à la base de données
$host = 'localhost';
$dbname = 'footballdb';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

// Récupérer les données des sections principales
$stmt_tournois = $pdo->query("SELECT t.id, tt.nom AS type_tournoi, t.date_debut, t.date_fin FROM tournois t JOIN types_tournois tt ON t.type_id = tt.id ORDER BY t.created_at DESC LIMIT 3");
$tournois = $stmt_tournois->fetchAll(PDO::FETCH_ASSOC);

$stmt_equipes = $pdo->query("SELECT nom, ville FROM equipes ORDER BY created_at DESC LIMIT 3");
$equipes = $stmt_equipes->fetchAll(PDO::FETCH_ASSOC);

$stmt_matches = $pdo->query("SELECT m.id, ed.nom AS equipe_domicile, ee.nom AS equipe_exterieur, m.date_match, m.heure_debut, m.etat FROM matches m JOIN equipes ed ON m.equipe_domicile_id = ed.id JOIN equipes ee ON m.equipe_exterieur_id = ee.id ORDER BY m.date_match DESC LIMIT 3");
$matches = $stmt_matches->fetchAll(PDO::FETCH_ASSOC);

// Récupérer les derniers matches pour le carrousel
$stmt_recent_matches = $pdo->query("
    SELECT m.id, ed.nom AS equipe_domicile, ee.nom AS equipe_exterieur, m.date_match, m.heure_debut, m.etat, 
           m.score_domicile, m.score_exterieur, s.nom AS stade, ed.photo AS photo_domicile, ee.photo AS photo_exterieur 
    FROM matches m 
    JOIN equipes ed ON m.equipe_domicile_id = ed.id 
    JOIN equipes ee ON m.equipe_exterieur_id = ee.id 
    JOIN stades s ON m.stade_id = s.id 
    WHERE m.date_match >= CURDATE() OR m.etat = 'termine'
    ORDER BY m.date_match ASC 
    LIMIT 6
");
$recent_matches = $stmt_recent_matches->fetchAll(PDO::FETCH_ASSOC);

// Récupérer les dernières actualités
$stmt_news = $pdo->query("
    SELECT title, message, photo, created_at 
    FROM publications 
    ORDER BY created_at DESC 
    LIMIT 6
");
$news = $stmt_news->fetchAll(PDO::FETCH_ASSOC);

// Récupérer les 3 meilleurs buteurs avec photos
$stmt_buteurs = $pdo->query("
    SELECT 
    j.id AS joueur_id, 
    j.nom AS joueur_nom, 
    j.prenom AS joueur_prenom, 
    j.photo AS joueur_photo, 
    j.numero_maillot AS numero_maillot, 
    COUNT(me.id) AS nombre_de_buts
FROM joueurs j
LEFT JOIN match_events me 
    ON j.id = me.joueur_id AND me.event_type = 'but'
GROUP BY j.id, j.nom, j.prenom, j.photo, j.numero_maillot
ORDER BY nombre_de_buts DESC;
");
$buteurs = $stmt_buteurs->fetchAll(PDO::FETCH_ASSOC);

// Récupérer les 3 premières équipes avec photos
$stmt_top_equipes = $pdo->query("
    SELECT e.nom, c.points, e.photo 
    FROM equipes e 
    JOIN classement_equipes c ON e.id = c.equipe_id 
    ORDER BY c.points DESC 
    LIMIT 5
");
$top_equipes = $stmt_top_equipes->fetchAll(PDO::FETCH_ASSOC);

// Récupérer les notifications non lues
try {
    $notifications = [];
    if (isset($_SESSION['user_id'])) {
        $stmt_notifications = $pdo->prepare("
            SELECT id, message, statut, created_at 
            FROM notifications 
            WHERE utilisateur_id = :user_id 
            ORDER BY created_at DESC 
            LIMIT 5
        ");
        $stmt_notifications->execute(['user_id' => $_SESSION['user_id']]);
        $notifications = $stmt_notifications->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    $notifications = [];
    echo "<!-- Erreur notifications : " . $e->getMessage() . " -->";
}
$unread_count = count(array_filter($notifications, fn($n) => $n['statut'] === 'non_lu'));
// Gestion de la recherche
$search_results = [];
if (isset($_GET['search']) && !empty(trim($_GET['search']))) {
    $search_term = '%' . trim($_GET['search']) . '%';
    try {
        $stmt_search = $pdo->prepare("
            SELECT 'equipe' AS type, nom, ville AS info FROM equipes WHERE nom LIKE :term
            UNION
            SELECT 'joueur' AS type, CONCAT(nom, ' ', prenom) AS nom, position AS info FROM joueurs WHERE nom LIKE :term OR prenom LIKE :term
        ");
        $stmt_search->execute(['term' => $search_term]);
        $search_results = $stmt_search->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $search_results = [];
        echo "<!-- Erreur SQL : " . $e->getMessage() . " -->";
    }
}
$stmt_all_news = $pdo->query("
    SELECT title, message, photo, created_at 
    FROM publications 
    ORDER BY created_at DESC
");
$all_news = $stmt_all_news->fetchAll(PDO::FETCH_ASSOC);
/*
$stmt_all_buteurs = $pdo->query("
    SELECT CONCAT(nom, ' ', prenom) AS nom_complet, photo 
    FROM joueurs 
    WHERE buts > 0 
    ORDER BY buts DESC
");

$all_buteurs = $stmt_all_buteurs->fetchAll(PDO::FETCH_ASSOC);*/
$stmt_recent_matches = $pdo->query("
    SELECT 
        m.id, 
        ed.nom AS equipe_domicile, 
        ee.nom AS equipe_exterieur, 
        m.date_match, 
        m.heure_debut, 
        m.etat, 
        m.score_domicile, 
        m.score_exterieur, 
        s.nom AS stade, 
        ed.photo AS photo_domicile, 
        ee.photo AS photo_exterieur,
        ed.id AS equipe_domicile_id, 
        ee.id AS equipe_exterieur_id
    FROM matches m 
    JOIN equipes ed ON m.equipe_domicile_id = ed.id 
    JOIN equipes ee ON m.equipe_exterieur_id = ee.id 
    JOIN stades s ON m.stade_id = s.id 
    WHERE m.date_match >= CURDATE() OR m.etat = 'termine'
    ORDER BY m.date_match ASC 
    LIMIT 6
");
$recent_matches = $stmt_recent_matches->fetchAll(PDO::FETCH_ASSOC);
// Récupérer les classements à partir de la table matches
$stmt_all_classements = $pdo->query("
    SELECT 
        e.id,
        e.nom,
        e.photo,
        COALESCE(SUM(CASE 
            WHEN m.equipe_domicile_id = e.id THEN 
                CASE 
                    WHEN m.score_domicile > m.score_exterieur THEN 3
                    WHEN m.score_domicile = m.score_exterieur THEN 1
                    ELSE 0
                END
            WHEN m.equipe_exterieur_id = e.id THEN 
                CASE 
                    WHEN m.score_exterieur > m.score_domicile THEN 3
                    WHEN m.score_exterieur = m.score_domicile THEN 1
                    ELSE 0
                END
            ELSE 0
        END), 0) AS points,
        COALESCE(COUNT(CASE WHEN (m.equipe_domicile_id = e.id OR m.equipe_exterieur_id = e.id) AND m.etat = 'termine' THEN 1 END), 0) AS matchs_joues,
        COALESCE(COUNT(CASE 
            WHEN m.equipe_domicile_id = e.id AND m.score_domicile > m.score_exterieur AND m.etat = 'termine' THEN 1
            WHEN m.equipe_exterieur_id = e.id AND m.score_exterieur > m.score_domicile AND m.etat = 'termine' THEN 1
            ELSE NULL
        END), 0) AS victoires,
        COALESCE(COUNT(CASE 
            WHEN m.equipe_domicile_id = e.id AND m.score_domicile < m.score_exterieur AND m.etat = 'termine' THEN 1
            WHEN m.equipe_exterieur_id = e.id AND m.score_exterieur < m.score_domicile AND m.etat = 'termine' THEN 1
            ELSE NULL
        END), 0) AS defaites,
        COALESCE(COUNT(CASE 
            WHEN (m.equipe_domicile_id = e.id OR m.equipe_exterieur_id = e.id) AND m.score_domicile = m.score_exterieur AND m.etat = 'termine' THEN 1
            ELSE NULL
        END), 0) AS nuls,
        COALESCE(SUM(CASE 
            WHEN m.equipe_domicile_id = e.id THEN m.score_domicile
            WHEN m.equipe_exterieur_id = e.id THEN m.score_exterieur
            ELSE 0
        END), 0) AS buts_marques,
        COALESCE(SUM(CASE 
            WHEN m.equipe_domicile_id = e.id THEN m.score_exterieur
            WHEN m.equipe_exterieur_id = e.id THEN m.score_domicile
            ELSE 0
        END), 0) AS buts_encaisse
    FROM equipes e
    LEFT JOIN matches m ON e.id = m.equipe_domicile_id OR e.id = m.equipe_exterieur_id
    GROUP BY e.id, e.nom, e.photo
    ORDER BY points DESC, buts_marques DESC
");
$all_classements = $stmt_all_classements->fetchAll(PDO::FETCH_ASSOC);
// Dans index.php, mise à jour de la requête existante
$stmt_recent_matches = $pdo->query("
    SELECT 
        m.id, 
        ed.nom AS equipe_domicile, 
        ee.nom AS equipe_exterieur, 
        m.date_match, 
        m.heure_debut, 
        m.etat, 
        m.score_domicile, 
        m.score_exterieur, 
        s.nom AS stade, 
        ed.photo AS photo_domicile, 
        ee.photo AS photo_exterieur,
        ed.id AS equipe_domicile_id, 
        ee.id AS equipe_exterieur_id
    FROM matches m 
    JOIN equipes ed ON m.equipe_domicile_id = ed.id 
    JOIN equipes ee ON m.equipe_exterieur_id = ee.id 
    JOIN stades s ON m.stade_id = s.id 
    WHERE m.date_match >= CURDATE() OR m.etat IN ('prevu', 'en_cours')
    ORDER BY m.date_match ASC 
    LIMIT 6
");
$recent_matches = $stmt_recent_matches->fetchAll(PDO::FETCH_ASSOC);

$stmt_all_equipes = $pdo->query("
    SELECT 
        e.id,
        e.nom AS equipe_nom,
        e.ville,
        e.photo,
        e.created_at,
        CONCAT(st.nom, ' ', st.prenom) AS entraineur
    FROM equipes e 
    LEFT JOIN staff st ON e.id = st.equipe_id 
        AND st.poste = 'entraineur' 
        AND (st.date_fin IS NULL OR st.date_fin > CURDATE())
    ORDER BY e.created_at DESC
");
$all_equipes = $stmt_all_equipes->fetchAll(PDO::FETCH_ASSOC);


$stmt_all_tournois = $pdo->query("
    SELECT t.id, tt.nom AS type_tournoi, t.date_debut, t.date_fin 
    FROM tournois t 
    JOIN types_tournois tt ON t.type_id = tt.id 
    ORDER BY t.created_at DESC
");
$all_tournois = $stmt_all_tournois->fetchAll(PDO::FETCH_ASSOC);

// Après les autres requêtes dans index.php
$stmt_votes = $pdo->query("
    SELECT 
        m.id AS match_id, 
        ed.nom AS equipe_domicile, 
        ee.nom AS equipe_exterieur, 
        m.etat,
        ed.id AS equipe_domicile_id,
        ee.id AS equipe_exterieur_id,
        ed.photo AS photo_domicile,
        ee.photo AS photo_exterieur,
        (SELECT COUNT(*) FROM votes v WHERE v.match_id = m.id AND v.voted_team_id = ed.id) AS votes_domicile,
        (SELECT COUNT(*) FROM votes v WHERE v.match_id = m.id AND v.voted_team_id = ee.id) AS votes_exterieur,
        (SELECT COUNT(*) FROM votes v WHERE v.match_id = m.id AND v.voted_team_id IS NULL) AS votes_nul
    FROM matches m
    JOIN equipes ed ON m.equipe_domicile_id = ed.id
    JOIN equipes ee ON m.equipe_exterieur_id = ee.id
    WHERE m.etat IN ('en_cours', 'prevu')
    ORDER BY m.date_match DESC
");
$votes = $stmt_votes->fetchAll(PDO::FETCH_ASSOC);

;
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FootballDB - Accueil</title>
    <!-- Swiper CSS -->
    <link rel="stylesheet" href="https://unpkg.com/swiper@8/swiper-bundle.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        .polls-card {
    max-width: 1000px;
    margin: 20px auto;
    padding: 20px;
    background: #fff;
    border-radius: 15px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    text-align: center;
    display:
}

body.dark-mode .polls-card {
    background: #2A2A2A;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.4);
}

.polls-list {
    display: flex;
    flex-direction: column;
    gap: 20px;
    margin-top: 20px;
}

.poll-item {
    background: #f9f9f9;
    padding: 15px;
    border-radius: 10px;
    text-align: left;
}

body.dark-mode .poll-item {
    background: #3A3A3A;
}

.poll-item h3 {
    margin: 0 0 10px;
    font-size: 18px;
    color: #1A3C34;
}

body.dark-mode .poll-item h3 {
    color: #F4A261;
}

.poll-form {
    margin: 10px 0;
}

.poll-form label {
    display: block;
    margin: 5px 0;
    color: #333;
}

body.dark-mode .poll-form label {
    color: #E0E0E0;
}

.poll-form input[type="radio"] {
    margin-right: 5px;
}

.poll-form button {
    background-color: #F4A261;
    color: #fff;
    border: none;
    padding: 5px 15px;
    border-radius: 40px;
    cursor: pointer;
    margin-top: 10px;
}

.poll-form button:hover {
    background-color: #E68A41;
}

.poll-results p {
    margin: 5px 0;
    font-size: 14px;
    color: #666;
}

body.dark-mode .poll-results p {
    color: #B0B0B0;
}
        body {
            font-family: 'Poppins', Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #F5F6F5;
            color: #333;
            transition: background-color 0.3s, color 0.3s;
        }
        body.dark-mode {
            background-color: #1A1A1A;
            color: #E0E0E0;
        }

        /* Navbar */
        .navbar {
            background-color: #1A3C34;
            padding: 15px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        .navbar .brand {
            display: flex;
            align-items: center;
        }
        .navbar .brand img {
            width: 40px;
            height: 40px;
            margin-right: 10px;
            border-radius: 50%;
        }
        .navbar .brand span {
            color: #F5F6F5;
            font-size: 26px;
            font-weight: 700;
        }
        .navbar .nav-links button {
            color: #F5F6F5;
            background: none;
            border: none;
            font-size: 16px;
            padding: 10px 20px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .navbar .nav-links button:hover,
        .navbar .nav-links button.active {
            background-color: #F4A261;
            border-radius: 25px;
        }
        .navbar .actions {
            display: flex;
            align-items: center;
        }
        .navbar .search-bar form {
            display: flex;
        }
        .navbar .search-bar input {
            padding: 8px 15px;
            border: none;
            border-radius: 25px 0 0 25px;
            outline: none;
            width: 200px;
        }
        .navbar .search-bar button {
            padding: 8px 15px;
            border: none;
            background-color: #F4A261;
            color: #fff;
            border-radius: 0 25px 25px 0;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .navbar .search-bar button:hover {
            background-color: #E68A41;
        }
        <?php
        // Vérifier si le formulaire a été soumis ou si un paramètre 'search' est présent
        $isFormSubmitted = $_SERVER['REQUEST_METHOD'] === 'GET' && (isset($_GET['search']) && trim($_GET['search']) !== '');
        ?>
        .navbar .search-results {
            position:relative;
            top: 100%;
            left: 0;
            background-color: #fff;
            width: 250px;
            max-height: 200px;
            overflow-y: auto;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            border-radius: 5px;
            display: <?php echo $isFormSubmitted ? 'block' : 'none'; ?>;
            z-index: 1001;
        }
        body.dark-mode .navbar .search-results {
            background-color: #2A2A2A;
        }
        .navbar .search-results li {
            padding: 10px;
            color: #1A3C34;
            border-bottom: 1px solid #ddd;
        }
        body.dark-mode .navbar .search-results li {
            color: #E0E0E0;
            border-bottom: 1px solid #444;
        }
        .navbar .search-results li:hover {
            background-color: #F4A261;
            color: #fff;
        }
        .navbar .dark-mode-toggle {
            background: none;
            border: none;
            color: #F5F6F5;
            font-size: 24px;
            cursor: pointer;
            margin: 0 15px;
            transition: transform 0.3s;
        }
        .navbar .dark-mode-toggle:hover {
            transform: scale(1.1);
        }
        .navbar .login-btn {
            background-color: #F4A261;
            color: #fff;
            padding: 8px 20px;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 600;
            transition: background-color 0.3s;
        }
        .navbar .login-btn:hover {
            background-color: #E68A41;
        }

        /* Section */
        .section {
            padding: 50px 20px;
            text-align: center;
            display: none;
        }
        .section.active {
            display: block;
        }
        h1 {
            font-size: 40px;
            color: #1A3C34;
            margin-bottom: 20px;
        }
        body.dark-mode h1 {
            color: #F4A261;
        }
        h2 {
            font-size: 28px;
            color: #1A3C34;
            margin-bottom: 30px;
        }
        body.dark-mode h2 {
            color: #F4A261;
        }

        /* Accueil */
        #accueil .intro {
            font-size: 20px;
            color: #666;
            margin-bottom: 50px;
        }
        body.dark-mode #accueil .intro {
            color: #B0B0B0;
        }

        /* Carrousel Matches */
        .matches-carousel {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto 50px;
        }
        .swiper-slide {
            width: 380px;
        }
        .match-card {
            background: linear-gradient(135deg, #ffffff, #f0f0f0);
            border-radius: 20px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
            padding: 25px;
            text-align: center;
            transition: transform 0.3s, box-shadow 0.3s;
            position: relative;
            overflow: hidden;
        }
        body.dark-mode .match-card {
            background: linear-gradient(135deg, #2A2A2A, #1A1A1A);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.5);
        }
        .match-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 12px 25px rgba(0, 0, 0, 0.25);
        }
        .match-card .team-images {
            position: relative;
            height: 130px;
            background: linear-gradient(90deg, rgba(244, 162, 97, 0.1), rgba(26, 60, 52, 0.1));
            border-radius: 15px;
            margin-bottom: 20px;
        }
        .match-card .team-images .home-team {
            position: absolute;
            left: 20px;
            top: 10px;
            width: 110px;
            height: 110px;
            object-fit: contain;
            border-radius: 50%;
            border: 4px solid #F4A261;
            background-color: #fff;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
            transition: transform 0.3s;
        }
        .match-card .team-images .away-team {
            position: absolute;
            right: 20px;
            top: 10px;
            width: 110px;
            height: 110px;
            object-fit: contain;
            border-radius: 50%;
            border: 4px solid #F4A261;
            background-color: #fff;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
            transition: transform 0.3s;
        }
        body.dark-mode .match-card .team-images .home-team,
        body.dark-mode .match-card .team-images .away-team {
            background-color: #2A2A2A;
        }
        .match-card:hover .home-team {
            transform: translateX(10px);
        }
        .match-card:hover .away-team {
            transform: translateX(-10px);
        }
        .match-card .date {
            font-size: 14px;
            color: #F4A261;
            font-weight: 600;
            margin-bottom: 15px;
        }
        .match-card .date::before {
            content: "📅 ";
        }
        .match-card .teams {
            font-size: 22px;
            font-weight: 700;
            color: #1A3C34;
            margin-bottom: 15px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        body.dark-mode .match-card .teams {
            color: #E0E0E0;
        }
        .match-card .score-status {
            display: inline-block;
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 15px;
        }
        .match-card .score-status.upcoming {
            background-color: #28A745;
            color: #fff;
        }
        .match-card .score-status.finished {
            background-color: #DC3545;
            color: #fff;
        }
        .match-card .score-status.live {
            background-color: #F4A261;
            color: #fff;
        }
        .match-card .stade {
            font-size: 14px;
            color: #777;
            font-weight: 400;
            margin-top: 10px;
        }
        .match-card .stade::before {
            content: "🏟️ ";
        }
        body.dark-mode .match-card .stade {
            color: #999;
        }
        .match-card::after {
            content: "";
            display: block;
            width: 50%;
            height: 1px;
            background: #F4A261;
            margin: 15px auto;
        }
        .swiper-button-next, .swiper-button-prev {
            color: #F4A261;
        }
        .swiper-pagination-bullet-active {
            background-color: #F4A261;
        }

        /* Layout des cartes */
        .stats-container {
            display: flex;
            max-width: 1200px;
            margin: 0 auto 50px;
            gap: 20px;
        }
        .news-container {
            width: 50%;
            position: relative;
            height: 400px;
        }
        .right-cards {
            width: 50%;
            display: flex;
            gap: 20px;
        }
        .news-card, .buteurs-card, .equipes-card {
            background: #fff;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            padding: 20px;
            text-align: left;
        }
        body.dark-mode .news-card,
        body.dark-mode .buteurs-card,
        body.dark-mode .equipes-card {
            background: #2A2A2A;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.4);
        }

        /* Carte des Actualités */
        .news-card {
            position: absolute;
            top: 0;
            left: 0;
            width: 90%;
            height: 100%;
            opacity: 0;
            transition: opacity 0.5s ease-in-out;
        }
        .news-card.active {
            opacity: 1;
        }
        .news-card img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 10px;
            margin-bottom: 15px;
        }
        .news-card .title {
            font-size: 20px;
            font-weight: 700;
            color: #1A3C34;
            margin-bottom: 10px;
        }
        body.dark-mode .news-card .title {
            color: #F4A261;
        }
        .news-card .date {
            font-size: 14px;
            color: #999;
            margin-bottom: 10px;
        }
        body.dark-mode .news-card .date {
            color: #888;
        }
        .news-card .content {
            font-size: 16px;
            color: #666;
            line-height: 1.5;
        }
        body.dark-mode .news-card .content {
            color: #B0B0B0;
        }

        /* Carte des Buteurs */
        .buteurs-card {
            width: 50%;
            height: 400px;
        }
        .buteurs-card h3 {
            font-size: 20px;
            color: #1A3C34;
            margin-bottom: 15px;
        }
        body.dark-mode .buteurs-card h3 {
            color: #F4A261;
        }
        .buteurs-card .buteur {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }
        .buteurs-card .buteur img {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            margin-right: 10px;
            object-fit: cover;
        }
        .buteurs-card .buteur .info {
            flex-grow: 1;
        }
        .buteurs-card .buteur .nom {
            font-size: 16px;
            font-weight: 600;
            color: #333;
        }
        body.dark-mode .buteurs-card .buteur .nom {
            color: #E0E0E0;
        }
        .buteurs-card .buteur .buts {
            font-size: 14px;
            color: #F4A261;
        }

        /* Carte des Équipes */
        .equipes-card {
            width: 50%;
            height: 400px;
        }
        .equipes-card h3 {
            font-size: 20px;
            color: #1A3C34;
            margin-bottom: 15px;
        }
        body.dark-mode .equipes-card h3 {
            color: #F4A261;
        }
        .equipes-card .equipe {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }
        .equipes-card .equipe img {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            margin-right: 10px;
            object-fit: cover;
        }
        .equipes-card .equipe .info {
            flex-grow: 1;
        }
        .equipes-card .equipe .nom {
            font-size: 16px;
            font-weight: 600;
            color: #333;
        }
        body.dark-mode .equipes-card .equipe .nom {
            color: #E0E0E0;
        }
        .equipes-card .equipe .points {
            font-size: 14px;
            color: #F4A261;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .navbar {
                flex-direction: column;
                align-items: flex-start;
            }
            .navbar .nav-links {
                width: 100%;
                justify-content: center;
                margin: 10px 0;
            }
            .navbar .actions {
                width: 100%;
                justify-content: center;
            }
            .navbar .search-bar input {
                width: 150px;
            }
            .swiper-slide {
                width: 300px;
            }
            .match-card {
                padding: 15px;
            }
            .match-card .team-images {
                height: 100px;
            }
            .match-card .team-images .home-team,
            .match-card .team-images .away-team {
                width: 90px;
                height: 90px;
            }
            .match-card .teams {
                font-size: 18px;
            }
            .stats-container {
                flex-direction: column;
            }
            .news-container, .right-cards {
                width: 100%;
            }
            .right-cards {
                flex-direction: column;
                gap: 20px;
            }
            .buteurs-card, .equipes-card {
                width: 100%;
                height: auto;
            }
            h1 {
                font-size: 32px;
            }
            h2 {
                font-size: 24px;
            }
        }
        /* Ajoutez ce CSS dans la partie <style> de votre document */

/* Style des cartes avec en-tête amélioré */
.buteurs-card, .equipes-card {
    position: relative;
    overflow: hidden;
}

.card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
    padding-bottom: 10px;
    border-bottom: 2px solid rgba(244, 162, 97, 0.2);
}

/* Style des boutons "Voir plus" */
.see-more-btn {
    background-color: #1A3C34;
    color: #F5F6F5;
    border: none;
    padding: 6px 12px;
    border-radius: 20px;
    cursor: pointer;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    transition: all 0.3s ease;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    display: flex;
    align-items: center;
    gap: 5px;
}

.see-more-btn:hover {
    background-color: #F4A261;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
}

.see-more-btn:active {
    transform: translateY(0);
}

.see-more-btn::after {
    content: '→';
    font-size: 14px;
    transition: transform 0.3s ease;
}

.see-more-btn:hover::after {
    transform: translateX(3px);
}

/* Version dark mode */
body.dark-mode .see-more-btn {
    background-color: #F4A261;
    color: #1A1A1A;
}

body.dark-mode .see-more-btn:hover {
    background-color: #E68A41;
    color: #F5F6F5;
}
/* Styles pour le footer */
.site-footer {
    background-color: #1A3C34;
    color: #F5F6F5;
    padding: 50px 0 0;
    margin-top: 50px;
    font-size: 14px;
}

body.dark-mode .site-footer {
    background-color: #0D1F1A;
}

.footer-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 30px;
}

.footer-column h3 {
    color: #F4A261;
    font-size: 18px;
    margin-bottom: 20px;
    position: relative;
    padding-bottom: 10px;
}

.footer-column h3::after {
    content: '';
    position: absolute;
    left: 0;
    bottom: 0;
    width: 50px;
    height: 2px;
    background-color: #F4A261;
}

.footer-column ul {
    list-style: none;
    padding: 0;
}

.footer-column ul li {
    margin-bottom: 10px;
}

.footer-column ul li a {
    color: #F5F6F5;
    text-decoration: none;
    transition: color 0.3s;
}

.footer-column ul li a:hover {
    color: #F4A261;
}

.social-icons {
    display: flex;
    gap: 15px;
    margin-top: 20px;
}

.social-icon {
    color: #F5F6F5;
    background-color: rgba(255, 255, 255, 0.1);
    width: 35px;
    height: 35px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s;
}

.social-icon:hover {
    background-color: #F4A261;
    transform: translateY(-3px);
}

.contact-info li {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 15px;
}

.contact-info i {
    color: #F4A261;
    width: 20px;
    text-align: center;
}

.newsletter-form {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.newsletter-form input {
    padding: 10px 15px;
    border: none;
    border-radius: 5px;
    background-color: rgba(255, 255, 255, 0.1);
    color: #F5F6F5;
}

.newsletter-form input::placeholder {
    color: #ccc;
}

.newsletter-form button {
    background-color: #F4A261;
    color: #1A3C34;
    border: none;
    padding: 10px 15px;
    border-radius: 5px;
    cursor: pointer;
    font-weight: 600;
    transition: background-color 0.3s;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 5px;
}

.newsletter-form button:hover {
    background-color: #E68A41;
}

.footer-bottom {
    background-color: rgba(0, 0, 0, 0.2);
    padding: 20px 0;
    margin-top: 50px;
    text-align: center;
}

.footer-bottom p {
    margin: 0;
    color: #ccc;
    font-size: 13px;
}

.legal-links {
    margin-top: 10px;
    display: flex;
    justify-content: center;
    flex-wrap: wrap;
    gap: 15px;
}

.legal-links a {
    color: #ccc;
    text-decoration: none;
    font-size: 12px;
    transition: color 0.3s;
}

.legal-links a:hover {
    color: #F4A261;
}

@media (max-width: 768px) {
    .footer-container {
        grid-template-columns: 1fr;
    }
    
    .footer-column {
        text-align: center;
    }
    
    .footer-column h3::after {
        left: 50%;
        transform: translateX(-50%);
    }
    
    .social-icons {
        justify-content: center;
    }
}
/* Conteneur des tableaux */
.buteurs-table-container, .classements-table-container {
    max-width: 1000px;
    margin: 20px auto;
    overflow-x: auto;
}

/* Style des tableaux */
.buteurs-table, .classements-table {
    width: 100%;
    border-collapse: collapse;
    background-color: #fff;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    border-radius: 10px;
    overflow: hidden;
}

body.dark-mode .buteurs-table,
body.dark-mode .classements-table {
    background-color: #2A2A2A;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.4);
}

/* En-têtes des tableaux */
.buteurs-table thead th,
.classements-table thead th {
    background-color: #1A3C34;
    color: #F5F6F5;
    padding: 15px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 1px;
}

body.dark-mode .buteurs-table thead th,
body.dark-mode .classements-table thead th {
    background-color: #F4A261;
    color: #1A1A1A;
}

/* Lignes des tableaux */
.buteurs-table tbody tr,
.classements-table tbody tr {
    border-bottom: 1px solid #ddd;
    transition: background-color 0.3s;
}

body.dark-mode .buteurs-table tbody tr,
body.dark-mode .classements-table tbody tr {
    border-bottom: 1px solid #444;
}

.buteurs-table tbody tr:hover,
.classements-table tbody tr:hover {
    background-color: #f5f5f5;
}

body.dark-mode .buteurs-table tbody tr:hover,
body.dark-mode .classements-table tbody tr:hover {
    background-color: #3A3A3A;
}

/* Cellules des tableaux */
.buteurs-table td,
.classements-table td {
    padding: 15px;
    text-align: center;
    color: #333;
}

body.dark-mode .buteurs-table td,
body.dark-mode .classements-table td {
    color: #E0E0E0;
}

/* Photos dans les tableaux */
.buteur-photo, .equipe-photo {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid #F4A261;
}

/* Responsive */
@media (max-width: 768px) {
    .buteurs-table td, .classements-table td {
        padding: 10px;
        font-size: 14px;
    }
    .buteur-photo, .equipe-photo {
        width: 40px;
        height: 40px;
    }
}
/* Carte des matchs */
.matches-card {
    background: #fff;
    border-radius: 15px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    padding: 20px;
    max-width: 800px;
    margin: 20px auto;
    text-align: center;
}

body.dark-mode .matches-card {
    background: #2A2A2A;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.4);
}

/* Élément individuel de match */
.match-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 15px 0;
    border-bottom: 1px solid #ddd;
}

body.dark-mode .match-item {
    border-bottom: 1px solid #444;
}

.match-item:last-child {
    border-bottom: none;
}

/* Disposition des équipes et infos */
.match-teams {
    display: flex;
    justify-content: space-between;
    align-items: center;
    width: 100%;
    gap: 20px;
}

.team {
    display: flex;
    flex-direction: column;
    align-items: center;
    width: 40%;
}

.team-logo {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid #F4A261;
    margin-bottom: 10px;
}

.team-name {
    font-size: 16px;
    font-weight: 600;
    color: #333;
    text-transform: uppercase;
}

body.dark-mode .team-name {
    color: #E0E0E0;
}

/* Infos du match (score ou état) */
.match-info {
    display: flex;
    align-items: center;
}

.score {
    font-size: 20px;
    font-weight: 700;
    color: #1A3C34;
    background-color: #DC3545;
    color: #fff;
    padding: 5px 15px;
    border-radius: 20px;
}

body.dark-mode .score {
    color: #E0E0E0;
}

.status {
    font-size: 16px;
    font-weight: 600;
    padding: 5px 15px;
    border-radius: 20px;
    color: #fff;
}

.status.upcoming {
    background-color: #28A745;
}

.status.live {
    background-color: #F4A261;
}

/* Détails du match (date et stade) */
.match-details {
    margin-top: 10px;
    font-size: 14px;
    color: #777;
}

body.dark-mode .match-details {
    color: #999;
}

.match-details .date {
    margin-right: 20px;
}

.match-details .stade {
    margin-left: 20px;
}

/* Responsive */
@media (max-width: 768px) {
    .matches-card {
        padding: 15px;
    }

    .match-teams {
        flex-direction: column;
        gap: 10px;
    }

    .team {
        width: 100%;
    }

    .team-logo {
        width: 50px;
        height: 50px;
    }

    .team-name {
        font-size: 14px;
    }

    .score, .status {
        font-size: 14px;
    }

    .match-details .date, .match-details .stade {
        display: block;
        margin: 5px 0;
    }
}
/* Ajustement du conteneur pour plus de colonnes */
.equipes-table-container {
    max-width: 1200px; /* Augmenté pour accueillir plus de colonnes */
    margin: 20px auto;
    overflow-x: auto;
}

/* Ajustement des cellules pour éviter le débordement */
.equipes-table td, .equipes-table th {
    padding: 15px;
    text-align: center;
    color: #333;
    min-width: 100px; /* Largeur minimale pour chaque colonne */
}

body.dark-mode .equipes-table td,
body.dark-mode .equipes-table th {
    color: #E0E0E0;
}

/* Réduire la taille de la photo si nécessaire */
.equipe-photo {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid #F4A261;
}

/* Responsive */
@media (max-width: 768px) {
    .equipes-table td, .equipes-table th {
        padding: 10px;
        font-size: 12px;
        min-width: 80px;
    }
    .equipe-photo {
        width: 40px;
        height: 40px;
    }
}
/* Conteneur des tableaux */
.equipes-table-container, .tournois-table-container {
    max-width: 1200px;
    margin: 20px auto;
    overflow-x: auto;
}

/* Style des tableaux (Équipes et Tournois) */
.equipes-table, .tournois-table {
    width: 100%;
    border-collapse: collapse;
    background-color: #fff;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    border-radius: 10px;
    overflow: hidden;
}

body.dark-mode .equipes-table,
body.dark-mode .tournois-table {
    background-color: #2A2A2A;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.4);
}

/* En-têtes des tableaux */
.equipes-table thead th,
.tournois-table thead th {
    background-color: #1A3C34;
    color: #F5F6F5;
    padding: 15px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 1px;
}

body.dark-mode .equipes-table thead th,
body.dark-mode .tournois-table thead th {
    background-color: #F4A261;
    color: #1A1A1A;
}

/* Lignes des tableaux */
.equipes-table tbody tr,
.tournois-table tbody tr {
    border-bottom: 1px solid #ddd;
    transition: background-color 0.3s;
}

body.dark-mode .equipes-table tbody tr,
body.dark-mode .tournois-table tbody tr {
    border-bottom: 1px solid #444;
}

.equipes-table tbody tr:hover,
.tournois-table tbody tr:hover {
    background-color: #f5f5f5;
}

body.dark-mode .equipes-table tbody tr:hover,
body.dark-mode .tournois-table tbody tr:hover {
    background-color: #3A3A3A;
}

/* Cellules des tableaux */
.equipes-table td,
.tournois-table td {
    padding: 15px;
    text-align: center;
    color: #333;
}

body.dark-mode .equipes-table td,
body.dark-mode .tournois-table td {
    color: #E0E0E0;
}

/* Photos dans le tableau Équipes */
.equipe-photo {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid #F4A261;
}

/* Liste des actualités */
.news-list {
    max-width: 1000px;
    margin: 20px auto;
}

.news-item {
    display: flex;
    background-color: #fff;
    border-radius: 10px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    margin-bottom: 20px;
    overflow: hidden;
    transition: transform 0.3s;
}

body.dark-mode .news-item {
    background-color: #2A2A2A;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.4);
}

.news-item:hover {
    transform: translateY(-5px);
}

.news-photo {
    width: 300px;
    height: auto;
    object-fit: cover;
}

.news-content {
    padding: 20px;
    flex-grow: 1;
}

.news-title {
    font-size: 20px;
    font-weight: 700;
    color: #1A3C34;
    margin-bottom: 10px;
}

body.dark-mode .news-title {
    color: #F4A261;
}

.news-date {
    font-size: 14px;
    color: #999;
    display: block;
    margin-bottom: 10px;
}

body.dark-mode .news-date {
    color: #888;
}

.news-message {
    font-size: 16px;
    color: #666;
    line-height: 1.5;
}

body.dark-mode .news-message {
    color: #B0B0B0;
}

/* Responsive */
@media (max-width: 768px) {
    .equipes-table td, .tournois-table td {
        padding: 10px;
        font-size: 14px;
    }
    .equipe-photo {
        width: 40px;
        height: 40px;
    }
    .news-item {
        flex-direction: column;
    }
    .news-photo {
        width: 100%;
        height: 200px;
    }
    .news-content {
        padding: 15px;
    }
}
/* Styles pour les notifications */
.notifications-dropdown {
    position: relative;
    margin: 0 15px;
}

.notifications-btn {
    background: none;
    border: none;
    color: #F5F6F5;
    font-size: 24px;
    cursor: pointer;
    position: relative;
    padding: 5px;
    transition: transform 0.3s;
}

.notifications-btn:hover {
    transform: scale(1.1);
}

.unread-badge {
    position: absolute;
    top: 0;
    right: 0;
    background-color: #DC3545;
    color: #fff;
    font-size: 12px;
    width: 18px;
    height: 18px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.notifications-list {
    position: absolute;
    top: 100%;
    right: 0;
    background-color: #fff;
    width: 300px;
    max-height: 400px;
    overflow-y: auto;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
    border-radius: 5px;
    z-index: 1001;
}

body.dark-mode .notifications-list {
    background-color: #2A2A2A;
}

.notification-item {
    padding: 10px;
    border-bottom: 1px solid #ddd;
    cursor: pointer;
    transition: background-color 0.3s;
}

body.dark-mode .notification-item {
    border-bottom: 1px solid #444;
}

.notification-item.unread {
    background-color: #f8d7da;
}

body.dark-mode .notification-item.unread {
    background-color: #4a2c31;
}

.notification-item.read {
    background-color: #fff;
}

body.dark-mode .notification-item.read {
    background-color: #2A2A2A;
}

.notification-item:hover {
    background-color: #f5f5f5;
}

body.dark-mode .notification-item:hover {
    background-color: #3A3A3A;
}

.notification-item p {
    margin: 0;
    font-size: 14px;
    color: #333;
}

body.dark-mode .notification-item p {
    color: #E0E0E0;
}

.notification-date {
    font-size: 12px;
    color: #999;
    display: block;
    margin-top: 5px;
}

body.dark-mode .notification-date {
    color: #888;
}

/* Responsive */
@media (max-width: 768px) {
    .notifications-list {
        width: 250px;
    }
}
.match-card {
    background: #fff;
    border-radius: 15px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    padding: 20px;
    text-align: center;
    transition: transform 0.3s;
    position: relative;
}

body.dark-mode .match-card {
    background: #2A2A2A;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.4);
}

.score-status {
    margin: 10px 0;
    font-weight: 600;
}

.score-status .finished {
    font-size: 20px;
    color: #1A3C34;
}

body.dark-mode .score-status .finished {
    color: #F4A261;
}

.score-status .live {
    font-size: 16px;
    color: #fff;
    background-color: #F4A261;
    padding: 5px 10px;
    border-radius: 20px;
    display: inline-block;
}

.score-status .current-score {
    font-size: 18px;
    color: #DC3545;
    margin-top: 5px;
    display: block;
}

body.dark-mode .score-status .current-score {
    color: #FF6B6B;
}

.score-status .upcoming {
    font-size: 16px;
    color: #fff;
    background-color: #6C757D;
    padding: 5px 10px;
    border-radius: 20px;
    display: inline-block;
}
.abonner-btn {
    background-color: #28A745;
    color: #fff;
    border: none;
    padding: 8px 15px;
    border-radius: 5px;
    cursor: pointer;
}

.abonner-btn:hover {
    background-color: #218838;
}

#abonnements table {
    background: #fff;
    border-radius: 8px;
    margin-top: 20px;
}

#abonnements th, #abonnements td {
    padding: 10px;
    border: 1px solid #ddd;
    text-align: center;
}

#abonnements th {
    background: #1A3C34;
    color: #fff;
}
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="brand">
            <img src="imgs/logobotola.png" alt="Logo FootballDB">
            <span>Botola Pro</span>
        </div>
        <div class="nav-links">
            <button onclick="showSection('accueil')" class="active">Accueil</button>
            <button onclick="showSection('actualites')">Actualités</button>
            <button onclick="showSection('matches')">Matches</button>
            <button onclick="showSection('classements')">Classements</button>
            <button onclick="showSection('equipes')">Équipes</button>
            <button onclick="showSection('tournois')">Tournois</button>
    
            <button onclick="showSection('abonnements')">abonnements</button>
            <button onclick="showSection('buteurs')">Buteurs</button>
            
        </div>
        <div class="actions">
            <div class="search-bar">
                <form method="GET" action="">
                    <input type="text" name="search" placeholder="Search" >
                    <button type="submit">🔍</button>
                </form>
                <div class="search-results">
                    <ul>
                        <?php if (isset($_GET['search']) && !empty(trim($_GET['search']))): ?>
                            <?php if (!empty($search_results)): ?>
                                <?php foreach ($search_results as $result): ?>
                                    <li>
                                        <?php echo ($result['type'] === 'equipe' ? '[Équipe]' : '[Joueur]') . ' ' . 
                                                   htmlspecialchars($result['nom']) . ' - ' . htmlspecialchars($result['info']); ?>
                                    </li>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <li>Aucun résultat pour "<?php echo htmlspecialchars($_GET['search']); ?>"</li>
                            <?php endif; ?>
                        <?php endif; ?>
                    </ul>
                </div>
                
            </div>
            <!-- Ajout de la section notifications pour utilisateurs connectés -->
            <?php if (isset($_SESSION['user_id'])): ?>
    <div class="notifications-dropdown">
        <button class="notifications-btn">
            🔔
            <?php if ($unread_count > 0): ?>
                <span class="unread-badge"><?php echo $unread_count; ?></span>
            <?php endif; ?>
        </button>
        <div class="notifications-list" style="display: none;">
            <?php if (!empty($notifications)): ?>
                <?php foreach ($notifications as $notification): ?>
                    <div class="notification-item <?php echo $notification['statut'] === 'lu' ? 'read' : 'unread'; ?>" data-id="<?php echo $notification['id']; ?>">
                        <p><?php echo htmlspecialchars($notification['message']); ?></p>
                        <span class="notification-date"><?php echo date('d/m/Y H:i', strtotime($notification['created_at'])); ?></span>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="notification-item">Aucune notification pour le moment.</div>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>
            
            <button class="dark-mode-toggle" onclick="toggleDarkMode()">☀️</button>
          
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="logout.php" class="login-btn">Deconnexion</a>
             <?php else: ?>
                <a href="create_account.php" class="login-btn">Connexion</a>
             <?php endif; ?>

        </div>
    </nav>

    <!-- Sections -->
    <div id="accueil" class="section active">
        <h1>Bienvenue sur FootballDB</h1>
        <p class="intro">Votre hub ultime pour suivre le football en temps réel : matches, actualités et plus encore.</p>

        <!-- Carrousel Matches -->
        <h2>Matches Récents et à Venir</h2>
        <div class="swiper matches-carousel">
    <div class="swiper-wrapper">
        <?php if (empty($recent_matches)): ?>
            <p>Aucun match à afficher dans le carrousel.</p>
        <?php else: ?>
            <?php foreach ($recent_matches as $match): ?>
                <div class="swiper-slide">
                    <div class="match-card" onclick="window.location.href='match_stats.php?match_id=<?php echo $match['id']; ?>'" style="cursor: pointer;">
                        <div class="team-images">
                            <img src="<?php echo $match['photo_domicile'] ?: 'https://via.placeholder.com/150'; ?>" alt="<?php echo htmlspecialchars($match['equipe_domicile']); ?>" class="home-team">
                            <img src="<?php echo $match['photo_exterieur'] ?: 'https://via.placeholder.com/150'; ?>" alt="<?php echo htmlspecialchars($match['equipe_exterieur']); ?>" class="away-team">
                        </div>
                        <div class="date"><?php echo htmlspecialchars($match['date_match']) . " " . $match['heure_debut']; ?></div>
                        <div class="teams"><?php echo htmlspecialchars($match['equipe_domicile']) . " vs " . htmlspecialchars($match['equipe_exterieur']); ?></div>
                        <div class="score-status">
                            <?php if ($match['etat'] === 'termine' && $match['score_domicile'] !== null && $match['score_exterieur'] !== null): ?>
                                <span class="finished"><?php echo $match['score_domicile'] . " - " . $match['score_exterieur']; ?></span>
                            <?php elseif ($match['etat'] === 'en_cours'): ?>
                                <span class="live">En Direct</span>
                                <?php if ($match['score_domicile'] !== null && $match['score_exterieur'] !== null): ?>
                                    <span class="current-score"><?php echo $match['score_domicile'] . " - " . $match['score_exterieur']; ?></span>
                                <?php else: ?>
                                    <span class="current-score">0 - 0</span>
                                <?php endif; ?>
                            <?php else: ?>
                                <span class="upcoming">À Venir</span>
                            <?php endif; ?>
                        </div>
                        <div class="stade"><?php echo htmlspecialchars($match['stade']); ?></div>
                        <?php if (isset($_SESSION['user_id'])): ?>
    <?php
    $stmt_check = $pdo->prepare("SELECT id FROM abonnement WHERE utilisateur_id = :user_id AND type = 'match' AND reference_id = :id");
    $stmt_check->execute(['user_id' => $_SESSION['user_id'], 'id' => $match['id']]);
    if (!$stmt_check->fetch()):
    ?>
        <form action="abonner.php" method="POST" style="margin-top: 10px;">
            <input type="hidden" name="type" value="match">
            <input type="hidden" name="reference_id" value="<?php echo $match['id']; ?>">
            <button type="submit" class="abonner-btn">S'abonner</button>
        </form>
    <?php else: ?>
        <span style="color: #28A745; font-size: 14px;">Abonné</span>
    <?php endif; ?>
<?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    <div class="swiper-button-next"></div>
    <div class="swiper-button-prev"></div>
    <div class="swiper-pagination"></div>
</div>
        <!-- Stats Container -->
        <h2>Statistiques et Actualités</h2>
        <div class="stats-container">
            <!-- Carte des Actualités -->
            <div class="news-container">
                <?php if (!empty($news)): ?>
                    <?php foreach ($news as $index => $article): ?>
                        <div class="news-card <?php echo $index === 0 ? 'active' : ''; ?>" data-index="<?php echo $index; ?>">
                            <?php if ($article['photo']): ?>
                                <img src="<?php echo htmlspecialchars($article['photo']); ?>" alt="Image de l'article">
                            <?php else: ?>
                                <img src="https://via.placeholder.com/600x200" alt="Placeholder">
                            <?php endif; ?>
                            <div class="title"><?php echo htmlspecialchars($article['title']); ?></div>
                            <div class="date"><?php echo date('d/m/Y H:i', strtotime($article['created_at'])); ?></div>
                            <div class="content"><?php echo htmlspecialchars($article['message']); ?></div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="news-card active">
                        <p>Aucune actualité disponible pour le moment.</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Cartes Droites (Buteurs et Équipes) -->
            <div class="right-cards">
                <!-- Carte des Buteurs -->
                <div class="buteurs-card">
                    <h3>Top 3 Buteurs</h3>
                    
                    <?php /* if (!empty($buteurs)): ?>
                        <?php foreach ($buteurs as $buteur): ?>
                            <div class="buteur">
                                <img src="<?php echo $buteur['photo'] ?: 'https://via.placeholder.com/50'; ?>" alt="<?php echo htmlspecialchars($buteur['nom_complet']); ?>">
                                <div class="info">
                                    <div class="nom"><?php echo htmlspecialchars($buteur['nom_complet']); ?></div>
                                    <div class="buts"><?php echo $buteur['buts']; ?> buts</div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>Aucun buteur disponible.</p>
                    <?php endif; */?>

                    <button onclick="showSection('buteurs')" class="see-more-btn">
            Voir plus
        </button>
                </div>  

                <!-- Carte des Équipes -->
                <div class="equipes-card">
                    <h3>Top 3 Équipes</h3>
                    <?php if (!empty($top_equipes)): ?>
                        <?php foreach ($top_equipes as $equipe): ?>
                            <div class="equipe">
                                <img src="<?php echo $equipe['photo'] ?: 'https://via.placeholder.com/50'; ?>" alt="<?php echo htmlspecialchars($equipe['nom']); ?>">
                                <div class="info">
                                    <div class="nom"><?php echo htmlspecialchars($equipe['nom']); ?></div>
                                    <div class="points"><?php echo $equipe['points']; ?> points</div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>Aucune équipe disponible.</p>
                    <?php endif; ?>
                    <button onclick="showSection('classements')" class="see-more-btn">
            Voir plus
        </button>
                </div>
            </div>
        </div>




        <div class="polls-card">
        <h2>Sondages des matchs</h2>
        <div class="polls-list">
            <?php if (!empty($votes)): ?>
                <?php foreach ($votes as $vote): ?>
                    <div style="text-align: center;" class="poll-item">
                    <img style="width: 40px;" src="<?php echo $vote['photo_domicile'] ?: 'https://via.placeholder.com/40'; ?>" alt="<?php echo htmlspecialchars($vote['equipe_domicile']); ?>">
                        
                        vs
                        <img  style="width: 40px;"src="<?php echo $vote['photo_exterieur'] ?: 'https://via.placeholder.com/40'; ?>" alt="<?php echo htmlspecialchars($vote['equipe_exterieur']); ?>">
                        
                        <p>Qui va gagner ce match ?</p>
                        <?php if ($vote['etat'] === 'en_cours' || $vote['etat'] === 'prevu'): ?>
                            <!-- Formulaire de vote pour matchs en cours -->
                            <form action="submit_vote.php" method="POST">
                                <input type="hidden" name="match_id" value="<?php echo htmlspecialchars($vote['match_id']); ?>">
                                <label>
                                    <input type="radio" name="voted_team_id" value="<?php echo htmlspecialchars($vote['equipe_domicile_id']); ?>" required>
                                    Victoire <?php echo htmlspecialchars($vote['equipe_domicile']); ?>
                                </label>

                                <label>
                                    <input type="radio" name="voted_team_id" value="<?php echo htmlspecialchars($vote['equipe_exterieur_id']); ?>">
                                    Victoire <?php echo htmlspecialchars($vote['equipe_exterieur']); ?>
                                </label>

                                <br>
                                <button style="width: 100px; color: black;background-color:orange; border-radius: 30px;" type="submit">Voter</button>
                            </form>

                        <?php endif; ?>
                        <!-- Résultats du sondage -->
                        <div class="poll-results">
                            <?php 
                            $total_votes = $vote['votes_domicile'] + $vote['votes_exterieur'] + $vote['votes_nul'];
                            $percent_domicile = $total_votes > 0 ? round(($vote['votes_domicile'] / $total_votes) * 100) : 0;
                            $percent_exterieur = $total_votes > 0 ? round(($vote['votes_exterieur'] / $total_votes) * 100) : 0;
                            $percent_nul = $total_votes > 0 ? round(($vote['votes_nul'] / $total_votes) * 100) : 0;
                            ?>
                            <p><?php echo htmlspecialchars($vote['equipe_domicile']); ?>  : <?php echo $vote['votes_domicile']; ?> votes (<?php echo $percent_domicile; ?>%)</p>
                            <p><?php echo htmlspecialchars($vote['equipe_exterieur']); ?>: <?php echo $vote['votes_exterieur']; ?> votes (<?php echo $percent_exterieur; ?>%)</p>
                           
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Aucun sondage disponible pour le moment.</p>
            <?php endif; ?>
        </div>
    </div>


    </div>

    <div id="tournois" class="section">
    <h1>Tournois</h1>
    <p>Retrouvez tous les tournois en cours ou passés :</p>

    <div class="tournois-table-container">
        <?php if (!empty($all_tournois)): ?>
            
            <table class="tournois-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Type de Tournoi</th>
                        <th>Date de Début</th>
                        <th>Date de Fin</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($all_tournois as $index => $tournoi): ?>
                        <tr>
                            <td><?php echo $index + 1; ?></td>
                            <td><?php echo htmlspecialchars($tournoi['type_tournoi']); ?></td>
                            <td><?php echo htmlspecialchars($tournoi['date_debut']); ?></td>
                            <td><?php echo $tournoi['date_fin'] ? htmlspecialchars($tournoi['date_fin']) : 'En cours'; ?></td>
                            <td>
    <?php if (isset($_SESSION['user_id'])): ?>
        <?php
        $stmt_check = $pdo->prepare("SELECT id FROM abonnement WHERE utilisateur_id = :user_id AND type = 'tournoi' AND reference_id = :id");
        $stmt_check->execute(['user_id' => $_SESSION['user_id'], 'id' => $tournoi['id']]);
        if (!$stmt_check->fetch()):
        ?>
            <form action="abonner.php" method="POST">
                <input type="hidden" name="type" value="tournoi">
                <input type="hidden" name="reference_id" value="<?php echo $tournoi['id']; ?>">
                <button type="submit" class="abonner-btn">S'abonner</button>
            </form>
        <?php else: ?>
            <span style="color: #28A745; font-size: 14px;">Abonné</span>
        <?php endif; ?>
    <?php endif; ?>
</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>Aucun tournoi disponible pour le moment.</p>
        <?php endif; ?>
    </div>
</div>


    <div id="equipes" class="section">
    <h1>Équipes</h1>
    <p>Découvrez toutes les équipes enregistrées avec leurs détails :</p>
    <div class="equipes-table-container">
        <?php if (!empty($all_equipes)): ?>
            <table class="equipes-table">
                <thead>
                    <tr>
                        <th>#</th>
                       
                        <th>Photo</th>
                        <th>Nom</th>
                        <th>Ville</th>
                        <th>Date de Création</th>
                        <th>Entraîneur</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($all_equipes as $index => $equipe): ?>
                        <tr>
                            <td><?php echo $index + 1; ?></td>
                           
                            <td><img src="<?php echo $equipe['photo'] ?: 'https://via.placeholder.com/50'; ?>" alt="<?php echo htmlspecialchars($equipe['equipe_nom']); ?>" class="equipe-photo"></td>
                            <td><?php echo htmlspecialchars($equipe['equipe_nom']); ?></td>
                            <td><?php echo htmlspecialchars($equipe['ville']); ?></td>
                            <td><?php echo $equipe['created_at'] ? htmlspecialchars($equipe['created_at']) : 'N/A'; ?></td>
                            <td><?php echo $equipe['entraineur'] ? htmlspecialchars($equipe['entraineur']) : 'N/A'; ?></td>
                            <td>
    <?php if (isset($_SESSION['user_id'])): ?>
        <?php
        $stmt_check = $pdo->prepare("SELECT id FROM abonnement WHERE utilisateur_id = :user_id AND type = 'equipe' AND reference_id = :id");
        $stmt_check->execute(['user_id' => $_SESSION['user_id'], 'id' => $equipe['id']]);
        if (!$stmt_check->fetch()):
        ?>
            <form action="abonner.php" method="POST">
                <input type="hidden" name="type" value="equipe">
                <input type="hidden" name="reference_id" value="<?php echo $equipe['id']; ?>">
                <button type="submit" class="abonner-btn">S'abonner</button>
            </form>
        <?php else: ?>
            <span style="color: #28A745;">Abonné</span>
        <?php endif; ?>
    <?php else: ?>
        <span>Connexion requise</span>
    <?php endif; ?>
</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>Aucune équipe disponible pour le moment.</p>
        <?php endif; ?>
    </div>
</div>


    <div id="matches" class="section">
    <h1>Matches</h1>
    <p>Les derniers matches à ne pas manquer :</p>
    <div class="matches-card">
        <?php if (!empty($recent_matches)): ?>
            <?php foreach ($recent_matches as $match): ?>
                <div class="match-item" onclick="window.location.href='match_stats.php?match_id=<?php echo $match['id']; ?>'" style="cursor: pointer;">
                    <div class="match-teams">
                        <div class="team" >
                            <img src="<?php echo $match['photo_domicile'] ?: 'https://via.placeholder.com/50'; ?>" alt="<?php echo htmlspecialchars($match['equipe_domicile']); ?>" class="team-logo">
                            <span class="team-name"><?php echo htmlspecialchars($match['equipe_domicile']); ?></span>
                        </div>
                        <div class="match-info" >
                            <?php if ($match['etat'] === 'termine' && $match['score_domicile'] !== null && $match['score_exterieur'] !== null): ?>
                                <span class="score"><?php echo $match['score_domicile'] . " - " . $match['score_exterieur']; ?></span>
                            <?php elseif ($match['etat'] === 'en_cours'): ?>
                                <span class="status live">En Direct</span>
                            <?php else: ?>
                                <span class="status upcoming">À Venir</span>
                            <?php endif; ?>
                        </div>
                        <div class="team">
                            <img src="<?php echo $match['photo_exterieur'] ?: 'https://via.placeholder.com/50'; ?>" alt="<?php echo htmlspecialchars($match['equipe_exterieur']); ?>" class="team-logo">
                            <span class="team-name"><?php echo htmlspecialchars($match['equipe_exterieur']); ?></span>
                        </div>
                    </div>
                    <div class="match-details">
                        <span class="date">📅 <?php echo htmlspecialchars($match['date_match']) . " " . $match['heure_debut']; ?></span>
                        <span class="stade">🏟️ <?php echo htmlspecialchars($match['stade']); ?></span>
                    </div>
                    <?php if (isset($_SESSION['user_id'])): ?>
    <?php
    $stmt_check = $pdo->prepare("SELECT id FROM abonnement WHERE utilisateur_id = :user_id AND type = 'match' AND reference_id = :id");
    $stmt_check->execute(['user_id' => $_SESSION['user_id'], 'id' => $match['id']]);
    if (!$stmt_check->fetch()):
    ?>
        <form action="abonner.php" method="POST" style="margin-top: 10px;">
            <input type="hidden" name="type" value="match">
            <input type="hidden" name="reference_id" value="<?php echo $match['id']; ?>">
            <button type="submit" class="abonner-btn">S'abonner</button>
        </form>
    <?php else: ?>
        <span style="color: #28A745; font-size: 14px;">Abonné</span>
    <?php endif; ?>
<?php endif; ?>
                    
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>Aucun match disponible pour le moment.</p>
        <?php endif; ?>
    </div>
    
</div>



<div id="abonnements" class="section" style="">
    <h1>Mes Abonnements</h1>
    <?php if (isset($_SESSION['user_id'])): ?>
        <?php
        $stmt = $pdo->prepare("
            SELECT a.id, a.type, a.reference_id,
                CASE 
                    WHEN a.type = 'match' THEN (SELECT CONCAT(ed.nom, ' vs ', ee.nom) FROM matches m JOIN equipes ed ON m.equipe_domicile_id = ed.id JOIN equipes ee ON m.equipe_exterieur_id = ee.id WHERE m.id = a.reference_id)
                    WHEN a.type = 'equipe' THEN (SELECT nom FROM equipes WHERE id = a.reference_id)
                    WHEN a.type = 'tournoi' THEN (SELECT tt.nom FROM tournois t JOIN types_tournois tt ON t.type_id = tt.id WHERE t.id = a.reference_id)
                END AS nom
            FROM abonnement a
            WHERE a.utilisateur_id = :user_id
        ");
        $stmt->execute(['user_id' => $_SESSION['user_id']]);
        $abonnements = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if ($abonnements):
        ?>
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr>
                        <th>Type</th>
                        <th>Nom</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($abonnements as $abonnement): ?>
                        <tr>
                            <td style="color:rgb(16, 15, 16); text-transform: uppercase; font-weight: bold;"><?php echo htmlspecialchars(ucfirst($abonnement['type'])); ?></td>
                            <td style="color:rgb(16, 15, 16); text-transform: uppercase; font-weight: bold;"    ><?php echo htmlspecialchars($abonnement['nom'] ?? 'Inconnu'); ?></td>
                            <td>
                                <form action="desabonner.php" method="POST">
                                    <input type="hidden" name="abonnement_id" value="<?php echo $abonnement['id']; ?>">
                                    <button type="submit" style="background: #DC3545; color: #fff; border: none; padding: 5px 10px; border-radius: 5px;">Désabonner</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>Vous n'avez aucun abonnement.</p>
        <?php endif; ?>
    <?php else: ?>
        <p>Connectez-vous pour voir vos abonnements.</p>
    <?php endif; ?>
</div>

    <div id="classements" class="section">
    <h1>Classements des Équipes</h1>
    <p>Consultez le classement actuel des équipes basé sur leurs points.</p>
    <div class="classements-table-container">
        <?php if (!empty($all_classements)): ?>
            <table class="classements-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Photo</th>
                        <th>Équipe</th>
                        <th>Points</th>
                        <th>Play</th>
                        <th>Wins</th>
                        <th>Lose</th>
                        <th>Draw</th>
                        <th>Goals</th>
                        <th>Against</th>

                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($all_classements as $index => $equipe): ?>
                        <tr>
                            <td><?php echo $index + 1; ?></td>
                            <td><img src="<?php echo $equipe['photo'] ?: 'https://via.placeholder.com/50'; ?>" alt="<?php echo htmlspecialchars($equipe['nom']); ?>" class="equipe-photo"></td>
                            <td><?php echo htmlspecialchars($equipe['nom']); ?></td>
                            <td><?php echo $equipe['points']; ?></td>
                            <td><?php echo $equipe['matchs_joues']; ?></td>
                            <td><?php echo $equipe['victoires']; ?></td>
                            <td><?php echo $equipe['defaites']; ?></td>
                            <td><?php echo $equipe['nuls']; ?></td>
                            <td><?php echo $equipe['buts_marques']; ?></td>
                            <td><?php echo $equipe['buts_encaisse']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>Aucun classement disponible pour le moment.</p>
        <?php endif; ?>
    </div>
</div>
<div id="actualites" class="section">
    <h1>Actualités</h1>
    <p>Toutes les dernières nouvelles du monde du football :</p>
    <div class="news-list">
        <?php if (!empty($all_news)): ?>
            <?php foreach ($all_news as $article): ?>
                <div class="news-item">
                    <?php if ($article['photo']): ?>
                        <img src="<?php echo htmlspecialchars($article['photo']); ?>" alt="Image de l'article" class="news-photo">
                    <?php else: ?>
                        <img src="https://via.placeholder.com/300x150" alt="Placeholder" class="news-photo">
                    <?php endif; ?>
                    <div class="news-content">
                        <h3 class="news-title"><?php echo htmlspecialchars($article['title']); ?></h3>
                        <span class="news-date"><?php echo date('d/m/Y H:i', strtotime($article['created_at'])); ?></span>
                        <p class="news-message"><?php echo htmlspecialchars($article['message']); ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>Aucune actualité disponible pour le moment.</p>
        <?php endif; ?>
    </div>
</div>




    <div id="buteurs" class="section">
    <h1>Meilleurs Buteurs</h1>
    <p>Découvrez les meilleurs buteurs de la saison en cours.</p>
    <div class="buteurs-table-container">
        <?php if (!empty($buteurs)): ?>
            <table class="buteurs-table">
                <thead>
                
                    <tr>
                        <th>#</th>
                        <th>Photo</th>
                        <th>Joueur</th>
                        <th>Buts</th>
                    </tr>
                </thead>
                <tbody>
                

                    <?php foreach ($buteurs as $index => $buteur): ?>
                        <tr>
                            <td><?php echo $index + 1; ?></td>
                            <td>
    <img src="<?php echo $buteur['joueur_photo'] ?: 'https://via.placeholder.com/50'; ?>" 
         alt="<?php echo htmlspecialchars($buteur['joueur_nom'] . ' ' . $buteur['joueur_prenom']); ?>" 
         style="width: 60px; height: 60px; border-radius: 50%; object-fit: cover;">
</td>
                            <td><?php echo htmlspecialchars($buteur['joueur_nom'] ." ". $buteur['joueur_prenom']  ); ?></td>
                            <td><?php echo $buteur['nombre_de_buts']; ?></td>
                        </tr>

                      
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>Aucun buteur n'a encore marqué cette saison.</p>
        <?php endif; ?>
    </div>
    
</div>



    <!-- Ajoutez ce code juste avant la fermeture du </body> -->

<footer class="site-footer">
    <div class="footer-container">
        <!-- Première colonne : A propos -->
        <div class="footer-column">
            <h3>FootballDB</h3>
            <p>La référence pour suivre le football en temps réel. Statistiques, résultats et actualités du monde du football.</p>
            <div class="social-icons">
                <a href="#" class="social-icon"><i class="fab fa-facebook-f"></i></a>
                <a href="#" class="social-icon"><i class="fab fa-twitter"></i></a>
                <a href="#" class="social-icon"><i class="fab fa-instagram"></i></a>
                <a href="#" class="social-icon"><i class="fab fa-youtube"></i></a>
                <a href="#" class="social-icon"><i class="fab fa-tiktok"></i></a>
            </div>
        </div>

        <!-- Deuxième colonne : Liens rapides -->
        <div class="footer-column">
            <h3>Liens rapides</h3>
            <ul>
                <li><a href="#" onclick="showSection('accueil')">Accueil</a></li>
                <li><a href="#" onclick="showSection('tournois')">Tournois</a></li>
                <li><a href="#" onclick="showSection('equipes')">Équipes</a></li>
                <li><a href="#" onclick="showSection('matches')">Matches</a></li>
                <li><a href="#" onclick="showSection('classements')">Classements</a></li>
                <li><a href="#" onclick="showSection('buteurs')">Buteurs</a></li>
            </ul>
        </div>

        <!-- Troisième colonne : Contact -->
        <div class="footer-column">
            <h3>Contact</h3>
            <ul class="contact-info">
                <li><i class="fas fa-envelope"></i> contact@footballdb.com</li>
                <li><i class="fas fa-phone"></i> +33 1 23 45 67 89</li>
                <li><i class="fas fa-map-marker-alt"></i> 10 Rue du Football, 75000 Paris</li>
            </ul>
        </div>

        <!-- Quatrième colonne : Newsletter -->
        <div class="footer-column">
            <h3>Newsletter</h3>
            <p>Abonnez-vous pour recevoir les dernières actualités</p>
            <form class="newsletter-form">
                <input type="email" placeholder="Votre email" required>
                <button type="submit">S'abonner <i class="fas fa-paper-plane"></i></button>
            </form>
        </div>
    </div>

    <div class="footer-bottom">
        <p>&copy; 2023 FootballDB. Tous droits réservés.</p>
        <div class="legal-links">
            <a href="#">Mentions légales</a>
            <a href="#">Politique de confidentialité</a>
            <a href="#">Conditions d'utilisation</a>
            <a href="#">Cookies</a>
        </div>
    </div>
</footer>

<!-- Ajoutez Font Awesome pour les icônes dans le <head> -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <!-- Swiper JS -->
    <script src="https://unpkg.com/swiper@8/swiper-bundle.min.js"></script>
    <script>
        // Initialisation du carrousel des matches
        const matchesCarousel = new Swiper('.matches-carousel', {
            slidesPerView: 1,
            spaceBetween: 20,
            navigation: {
                nextEl: '.swiper-button-next',
                prevEl: '.swiper-button-prev',
            },
            pagination: {
                el: '.swiper-pagination',
                clickable: true,
            },
            breakpoints: {
                640: { slidesPerView: 2 },
                1024: { slidesPerView: 3 }
            }
        });

        // Défilement automatique des actualités
        const newsCards = document.querySelectorAll('.news-card');
        let currentIndex = 0;

        function showNews(index) {
            newsCards.forEach(card => card.classList.remove('active'));
            newsCards[index].classList.add('active');
        }

        function nextNews() {
            currentIndex = (currentIndex + 1) % newsCards.length;
            showNews(currentIndex);
        }

        if (newsCards.length > 1) {
            showNews(currentIndex);
            setInterval(nextNews, 5000); // Changer toutes les 5 secondes
        } else if (newsCards.length === 1) {
            newsCards[0].classList.add('active');
        }

        // Gestion des sections
        function showSection(sectionId) {
            const sections = document.querySelectorAll('.section');
            const buttons = document.querySelectorAll('.nav-links button');

            sections.forEach(section => section.classList.remove('active'));
            buttons.forEach(button => button.classList.remove('active'));

            document.getElementById(sectionId).classList.add('active');
            const activeButton = document.querySelector(`button[onclick="showSection('${sectionId}')"]`);
            if (activeButton) activeButton.classList.add('active');
        }

        // Charger le mode sombre
        if (localStorage.getItem('darkMode') === 'enabled') {
            document.body.classList.add('dark-mode');
            document.querySelector('.dark-mode-toggle').textContent = '🌙';
        }

        // Toggle mode sombre
        function toggleDarkMode() {
            const body = document.body;
            const button = document.querySelector('.dark-mode-toggle');
            body.classList.toggle('dark-mode');

            if (body.classList.contains('dark-mode')) {
                button.textContent = '🌙';
                localStorage.setItem('darkMode', 'enabled');
            } else {
                button.textContent = '☀️';
                localStorage.setItem('darkMode', 'disabled');
            }
        }
        // Gestion de l'affichage de la liste des notifications
const notificationsBtn = document.querySelector('.notifications-btn');
const notificationsList = document.querySelector('.notifications-list');

if (notificationsBtn && notificationsList) {
    notificationsBtn.addEventListener('click', () => {
        notificationsList.style.display = notificationsList.style.display === 'block' ? 'none' : 'block';
    });

    // Cacher la liste si on clique en dehors
    document.addEventListener('click', (e) => {
        if (!notificationsDropdown.contains(e.target)) {
            notificationsList.style.display = 'none';
        }
    });
}

// Marquer une notification comme lue
const notificationItems = document.querySelectorAll('.notification-item');
notificationItems.forEach(item => {
    item.addEventListener('click', () => {
        const notificationId = item.getAttribute('data-id');
        if (item.classList.contains('unread')) {
            fetch('mark_notification_read.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `notification_id=${notificationId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    item.classList.remove('unread');
                    item.classList.add('read');
                    const badge = document.querySelector('.unread-badge');
                    const currentCount = parseInt(badge.textContent);
                    if (currentCount > 1) {
                        badge.textContent = currentCount - 1;
                    } else {
                        badge.style.display = 'none';
                    }
                }
            })
            .catch(error => console.error('Erreur:', error));
        }
    });
});
document.querySelectorAll('.poll-form').forEach(form => {
    form.addEventListener('submit', (e) => {
        e.preventDefault();
        const matchId = form.getAttribute('data-match-id');
        const votedTeamId = form.querySelector('input[name="voted_team_id"]:checked')?.value;

        if (!votedTeamId) {
            alert('Veuillez sélectionner une option avant de voter.');
            return;
        }

        fetch('vote_match.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `match_id=${matchId}&voted_team_id=${votedTeamId}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const resultsDiv = form.nextElementSibling;
                const totalVotes = data.results.votes_domicile + data.results.votes_exterieur + data.results.votes_nul;
                resultsDiv.innerHTML = `
                    <p>${form.closest('.poll-item').querySelector('h3').textContent.split(' vs ')[0]}: ${data.results.votes_domicile} votes (${totalVotes > 0 ? Math.round((data.results.votes_domicile / totalVotes) * 100) : 0}%)</p>
                    <p>${form.closest('.poll-item').querySelector('h3').textContent.split(' vs ')[1]}: ${data.results.votes_exterieur} votes (${totalVotes > 0 ? Math.round((data.results.votes_exterieur / totalVotes) * 100) : 0}%)</p>
                    <p>Match nul: ${data.results.votes_nul} votes (${totalVotes > 0 ? Math.round((data.results.votes_nul / totalVotes) * 100) : 0}%)</p>
                `;
                form.style.display = 'none'; // Cacher le formulaire après le vote
            } else {
                alert(data.message);
            }
        })
        .catch(error => console.error('Erreur:', error));
    });
}); 
    </script>
</body>
</html>