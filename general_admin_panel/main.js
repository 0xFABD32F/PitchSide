// Fonction utilitaire pour remplir les champs
function remplirChamps(champs, data) {
    for (const [key, value] of Object.entries(champs)) {
        const element = document.getElementById(value);
        if (element) {
            if (key === 'photo') {
                element.src = data[key] ? `../uploads/${data[key]}` : '';
                //alert(`../uploads/${data[key]}`);
            } else {
                element.value = data[key] || '';
            }
        }
    }
}

// Initialisation des listeners pour la sélection
typeSelectionHandlers = {
    player: {
        selector: 'update_player_selection',
        champs: {
            nom: 'update_player_nom',
            prenom: 'update_player_prenom',
            date_naissance: 'update_player_naissance',
            position: 'update_player_position',
            numero_maillot: 'update_player_numero_maillot',
            nationalite: 'update_player_nationalite',
            date_debut: 'update_player_date_debut',
            date_fin: 'update_player_date_fin',
            origine: 'update_player_origine',
            photo: 'update_player_photo'
        }
    },
    staff: {
        selector: 'update_staff_selection',
        champs: {
            nom: 'update_staff_nom',
            prenom: 'update_staff_prenom',
            email: 'update_staff_email',
            date_debut: 'update_staff_date_debut',
            date_fin: 'update_staff_date_fin',
            date_naissance :'update_staff_naissance',
            poste : 'update_staff_poste',
        }
    },
    stadium:{
        selector: 'update_stadium_selections',
        champs: {
            nom: 'update_stadium_nom',
            ville: 'update_stadium_ville',
            capacite: 'update_stadium_capacite',
        }
    },
    users: {
        selector: 'create_admin_selections',
        champs: {
            type_compte: 'user_curent_type',
        }
    },
    arbitres:{
        selector: 'update_arbitre_selections',
        champs: {
            nom: 'update_arbitre_nom',
            grade: 'update_arbitre_grade',
        }
    },
    teams:{
        selector: 'update_team_selection',
        champs: {
            nom: 'update_team_nom',
            ville: 'update_team_ville',
            photo: 'update_team_photo',
        }
    },

};


// Ajout des écouteurs sur les sélections
function initSelections() {
    for (const [key, { selector, champs }] of Object.entries(typeSelectionHandlers)) {
        const selectElement = document.getElementById(selector);
        if (selectElement) {
            selectElement.addEventListener('change', function () {
                const option = this.options[this.selectedIndex];
                const data = {};
                for (const champ in champs) {
                    data[champ] = option.dataset[champ];
                }
                remplirChamps(champs, data);
            });
        }
    }
}
function setupTeamSelection(teamSelectId, targetSelectId, tableName) {
    const teamSelect = document.getElementById(teamSelectId);
    const targetSelect = document.getElementById(targetSelectId);

    if (!teamSelect || !targetSelect) return;

    teamSelect.addEventListener('change', function() {
        const teamId = this.value;
        targetSelect.innerHTML = `<option value="">Sélectionnez un ${tableName === 'joueurs' ? 'joueur' : 'staff'}</option>`;

        if (teamId) {
            fetch(`/FootballFinal-main/public/fech_table_json_by_team_id.php?team_id=${teamId}&table=${tableName}`)
            .then(response => response.json())
                .then(items => {
                    if (items.length > 0) {
                        items.forEach(item => {
                            const option = document.createElement('option');
                            option.value = item.id;
                            option.textContent = `${item.prenom} ${item.nom}`;

                            // Ajouter automatiquement tous les attributs disponibles
                            Object.entries(item).forEach(([key, value]) => {
                                if (value !== null && value !== undefined) {
                                    option.dataset[key] = value;
                                }
                            });

                            targetSelect.appendChild(option);
                        });
                    } else {
                        targetSelect.innerHTML = `<option value="">Aucun ${tableName === 'joueurs' ? 'joueur' : 'staff'} trouvé</option>`;
                    }
                })
                .catch(error => {
                    console.error(`Erreur lors de la récupération des ${tableName} :` + error);
                    targetSelect.innerHTML = '<option value="">Erreur de chargement</option>';
                });
        }
    });
}
// Fonction principale d'initialisation
document.addEventListener('DOMContentLoaded', () => {
       initSelections();
       // Configuration pour les joueurs
       setupTeamSelection('update_player_team_selection', 'update_player_selection', 'joueurs');
       // Configuration pour les staffs
       setupTeamSelection('update_staff_team_selection', 'update_staff_selection', 'staff');
});



 // Afficher le formulaire correspondant au bouton cliqué
 document.querySelectorAll('.menu-btn').forEach(button => {
    button.addEventListener('click', () => {
        const formId = button.getAttribute('data-form');
        document.querySelectorAll('.form-container > .custom, .form-container > form').forEach(form => {
            form.classList.add('hidden');
        });
        document.getElementById(formId)?.classList.remove('hidden');
    });
});


// Afficher le formulaire de création d'équipe par default
document.getElementById("createTeamForm")?.classList.remove('hidden');