// Configuration
const API_URL = 'http://localhost:5000/api';

// Éléments DOM
let currentPlanning = null;

// Initialisation
document.addEventListener('DOMContentLoaded', () => {
    loadSavedProfile();
    setupEventListeners();
});

function setupEventListeners() {
    // Sliders pour concentration et agitation
    document.getElementById('concentration').addEventListener('input', (e) => {
        document.getElementById('concentrationValue').textContent = e.target.value;
    });
    document.getElementById('agitation').addEventListener('input', (e) => {
        document.getElementById('agitationValue').textContent = e.target.value;
    });
}

// Gestion des thérapies
function addTherapy() {
    const container = document.getElementById('therapies-list');
    const div = document.createElement('div');
    div.className = 'therapy-item';
    div.innerHTML = `
        <input type="text" placeholder="Jour" class="therapy-day">
        <input type="text" placeholder="Horaire (ex: 17:00-18:00)" class="therapy-time">
        <input type="text" placeholder="Description" class="therapy-desc">
        <button onclick="removeTherapy(this)">❌</button>
    `;
    container.appendChild(div);
}

function removeTherapy(btn) {
    btn.parentElement.remove();
}

function addActivity() {
    const container = document.getElementById('activities-list');
    const div = document.createElement('div');
    div.className = 'activity-item';
    div.innerHTML = `
        <input type="text" placeholder="Jour" class="activity-day">
        <input type="text" placeholder="Horaire" class="activity-time">
        <input type="text" placeholder="Activité" class="activity-desc">
        <button onclick="removeActivity(this)">❌</button>
    `;
    container.appendChild(div);
}

function removeActivity(btn) {
    btn.parentElement.remove();
}

function addTask() {
    const container = document.getElementById('tasks-list');
    const div = document.createElement('div');
    div.className = 'task-item';
    div.innerHTML = `
        <input type="text" placeholder="Titre" class="task-title">
        <input type="number" placeholder="Durée (min)" class="task-duration" value="30">
        <select class="task-difficulty">
            <option value="facile">Facile</option>
            <option value="moyen">Moyen</option>
            <option value="difficile">Difficile</option>
        </select>
        <button onclick="removeTask(this)">❌</button>
    `;
    container.appendChild(div);
}

function removeTask(btn) {
    btn.parentElement.remove();
}

// Sauvegarder le profil
async function saveProfile() {
    const profil = collectProfileData();
    
    try {
        const response = await fetch(`${API_URL}/save_manual_profile`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(profil)
        });
        
        const data = await response.json();
        if (data.success) {
            alert('✅ Profil sauvegardé avec succès !');
        } else {
            alert('❌ Erreur: ' + data.error);
        }
    } catch (error) {
        console.error('Erreur sauvegarde:', error);
        alert('❌ Impossible de sauvegarder. Vérifiez que l\'API est lancée.');
    }
}

async function loadSavedProfile() {
    try {
        const response = await fetch(`${API_URL}/get_last_profile`);
        const data = await response.json();
        
        if (data.success && data.profil) {
            const p = data.profil;
            document.getElementById('age').value = p.age || 8;
            document.getElementById('concentration').value = p.niveau_concentration_moyen || 3;
            document.getElementById('agitation').value = p.niveau_agitation_moyen || 2.5;
            document.getElementById('pic_debut').value = p.pic_attention_debut || '08:00';
            document.getElementById('pic_fin').value = p.pic_attention_fin || '11:00';
            document.getElementById('meilleure_matiere').value = p.meilleure_matiere || 'Lecture';
            if (p.matieres_difficiles) {
                document.getElementById('matieres_difficiles').value = p.matieres_difficiles.join(', ');
            }
            
            document.getElementById('concentrationValue').textContent = p.niveau_concentration_moyen || 3;
            document.getElementById('agitationValue').textContent = p.niveau_agitation_moyen || 2.5;
        }
    } catch (error) {
        console.log('Aucun profil sauvegardé trouvé');
    }
}

// Simuler le chargement depuis les carnets (à connecter à ton backend Symfony)
async function loadProfileFromCarnets() {
    alert("Cette fonctionnalité chargera les données depuis les carnets éducatifs.\n\nPour l'instant, vous pouvez saisir manuellement le profil.");
    // Ici, tu feras un appel à ton API Symfony pour récupérer les carnets
    // Exemple fictif:
    /*
    const response = await fetch('/api/carnets/user');
    const carnets = await response.json();
    
    const profileResponse = await fetch(`${API_URL}/build_profile_from_carnets`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ carnets: carnets })
    });
    */
}

function collectProfileData() {
    const matieresDiff = document.getElementById('matieres_difficiles').value;
    return {
        age: parseInt(document.getElementById('age').value),
        niveau_concentration_moyen: parseFloat(document.getElementById('concentration').value),
        niveau_agitation_moyen: parseFloat(document.getElementById('agitation').value),
        niveau_autonomie_moyen: 3, // Par défaut
        interruptions_moyennes: 3,
        temps_concentration_max_minutes: 35,
        pic_attention_debut: document.getElementById('pic_debut').value,
        pic_attention_fin: document.getElementById('pic_fin').value,
        meilleure_matiere: document.getElementById('meilleure_matiere').value,
        matieres_difficiles: matieresDiff ? matieresDiff.split(',').map(m => m.trim()) : []
    };
}

function collectConstraints() {
    const ecole = {};
    const jours = ['lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi', 'dimanche'];
    jours.forEach(jour => {
        const value = document.getElementById(`ecole_${jour}`).value;
        if (value && value.trim()) {
            ecole[jour] = value;
        }
    });
    
    const therapies = {};
    document.querySelectorAll('#therapies-list .therapy-item').forEach(item => {
        const jour = item.querySelector('.therapy-day').value.toLowerCase();
        if (jour) {
            therapies[jour] = {
                horaire: item.querySelector('.therapy-time').value,
                description: item.querySelector('.therapy-desc').value
            };
        }
    });
    
    const activites = {};
    document.querySelectorAll('#activities-list .activity-item').forEach(item => {
        const jour = item.querySelector('.activity-day').value.toLowerCase();
        if (jour) {
            activites[jour] = {
                horaire: item.querySelector('.activity-time').value,
                description: item.querySelector('.activity-desc').value
            };
        }
    });
    
    return { ecole, therapies, activites };
}

function collectTasks() {
    const tasks = [];
    document.querySelectorAll('#tasks-list .task-item').forEach(item => {
        const title = item.querySelector('.task-title').value;
        if (title) {
            tasks.push({
                titre: title,
                duree_estimee: parseInt(item.querySelector('.task-duration').value),
                difficulte: item.querySelector('.task-difficulty').value
            });
        }
    });
    return tasks;
}

async function generatePlanning() {
    const profil = collectProfileData();
    const contraintes = collectConstraints();
    const taches = collectTasks();
    
    if (taches.length === 0) {
        alert('⚠️ Veuillez ajouter au moins une tâche à programmer.');
        return;
    }
    
    // Afficher le loader
    document.getElementById('loading').style.display = 'block';
    document.getElementById('planning-result').style.display = 'none';
    
    try {
        const response = await fetch(`${API_URL}/generate_plan`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                profil_enfant: profil,
                contraintes: contraintes,
                taches: taches
            })
        });
        
        const data = await response.json();
        
        document.getElementById('loading').style.display = 'none';
        
        if (data.success) {
            currentPlanning = data.planning;
            displayPlanning(data.planning);
            displayTextVersion(data.text_version);
            document.getElementById('planning-result').style.display = 'block';
        } else {
            alert('❌ Erreur: ' + data.error);
        }
    } catch (error) {
        document.getElementById('loading').style.display = 'none';
        console.error('Erreur:', error);
        alert('❌ Impossible de contacter l\'API. Vérifiez que le serveur Flask est lancé sur http://localhost:5000');
    }
}

function displayPlanning(planning) {
    const container = document.getElementById('planning-content');
    
    if (!planning || !planning.semaine) {
        container.innerHTML = '<p>Erreur: Planning invalide</p>';
        return;
    }
    
    let html = '<table class="planning-table"><thead><tr>';
    
    // En-têtes des jours
    planning.semaine.forEach(day => {
        html += `<th>${day.jour}<br><small>${day.date || ''}</small></th>`;
    });
    html += '</tr></thead><tbody><tr>';
    
    // Pour chaque jour, afficher les créneaux
    planning.semaine.forEach(day => {
        html += '<td>';
        if (day.creneaux && day.creneaux.length > 0) {
            day.creneaux.forEach(slot => {
                const typeClass = `slot-${slot.type.replace(/_/g, '-')}`;
                html += `
                    <div class="slot ${typeClass}" onclick="showTooltip(this, '${slot.conseil_ia || 'Pas de conseil'}')">
                        <strong>${slot.heure}</strong><br>
                        ${slot.titre}<br>
                        <small>${slot.duree_min} min</small>
                    </div>
                `;
            });
        } else {
            html += '<em>Aucun créneau</em>';
        }
        html += '</td>';
    });
    
    html += '</tr></tbody></table>';
    container.innerHTML = html;
}

function displayTextVersion(text) {
    const container = document.getElementById('planning-text');
    container.innerHTML = `
        <details>
            <summary>📝 Voir la version texte</summary>
            <pre style="white-space: pre-wrap; background: #f4f4f4; padding: 15px; border-radius: 8px; margin-top: 10px;">${text}</pre>
        </details>
    `;
}

function showTooltip(element, message) {
    // Supprimer les tooltips existants
    const existing = document.querySelector('.tooltip');
    if (existing) existing.remove();
    
    const tooltip = document.createElement('div');
    tooltip.className = 'tooltip';
    tooltip.textContent = message;
    document.body.appendChild(tooltip);
    
    const rect = element.getBoundingClientRect();
    tooltip.style.left = rect.left + 'px';
    tooltip.style.top = (rect.top - tooltip.offsetHeight - 10) + 'px';
    
    setTimeout(() => {
        tooltip.remove();
    }, 3000);
}

function downloadPDF() {
    if (!currentPlanning) {
        alert('Aucun planning à télécharger. Générez d\'abord un planning.');
        return;
    }
    
    // Convertir le tableau en texte pour le PDF
    const planningText = document.getElementById('planning-text').innerText || 
                        document.getElementById('planning-content').innerText;
    
    // Créer un blob et télécharger
    const blob = new Blob([planningText], {type: 'text/plain'});
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `planning_${new Date().toISOString().split('T')[0]}.txt`;
    a.click();
    URL.revokeObjectURL(url);
    
    alert('📄 Planning téléchargé en format texte. Pour un PDF formaté, vous pouvez copier le contenu dans Word/Google Docs.');
}