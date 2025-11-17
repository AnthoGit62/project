// État global
let currentRole = '';
let commandesData = [];
let personnelData = [];

// Initialisation
document.addEventListener('DOMContentLoaded', function() {
    currentRole = document.body.dataset.userRole || 'client';
    loadInitialData();
});

// Charger les données initiales
async function loadInitialData() {
    await loadCommandes();
    if (currentRole === 'direction') {
        await loadPersonnel();
    }
}

// Charger les commandes
async function loadCommandes() {
    try {
        const response = await fetch('api/commandes.php?action=list');
        const data = await response.json();
        
        if (data.success) {
            commandesData = data.commandes;
            updateCommandesDisplay();
        }
    } catch (error) {
        console.error('Erreur lors du chargement des commandes:', error);
    }
}

// Afficher les commandes
function updateCommandesDisplay() {
    const commandesContent = document.getElementById('commandes-content');
    const toutesCommandesContent = document.getElementById('toutes-commandes-content');
    
    if (!commandesData || commandesData.length === 0) {
        const noData = '<p style="text-align: center; color: #999;">Aucune commande pour le moment</p>';
        if (commandesContent) commandesContent.innerHTML = noData;
        if (toutesCommandesContent) toutesCommandesContent.innerHTML = noData;
        return;
    }
    
    const enAttente = commandesData.filter(c => c.statut === 'en_attente');
    const terminees = commandesData.filter(c => c.statut === 'terminee');
    
    const html = `
        <div style="margin-bottom: 2rem;">
            <h3 style="color: #e94560; margin-bottom: 1rem;">📦 Commandes en attente (${enAttente.length})</h3>
            ${renderCommandesTable(enAttente)}
        </div>
        
        <div>
            <h3 style="color: #4caf50; margin-bottom: 1rem;">✅ Commandes terminées (${terminees.length})</h3>
            ${renderCommandesTable(terminees)}
        </div>
    `;
    
    if (commandesContent) commandesContent.innerHTML = html;
    if (toutesCommandesContent) toutesCommandesContent.innerHTML = html;
}

// Générer le tableau des commandes
function renderCommandesTable(commandes) {
    if (commandes.length === 0) {
        return '<p style="color: #999;">Aucune commande</p>';
    }
    
    let html = '<table><thead><tr>';
    html += '<th>ID</th>';
    html += '<th>Service</th>';
    html += '<th>Quantité</th>';
    html += '<th>Type</th>';
    
    if (currentRole !== 'client') {
        html += '<th>Client</th>';
    }
    
    html += '<th>Date</th>';
    html += '<th>Statut</th>';
    html += '<th>Actions</th>';
    html += '</tr></thead><tbody>';
    
    commandes.forEach(commande => {
        html += `<tr>
            <td>#${commande.id}</td>
            <td>${escapeHtml(commande.nom_service)}</td>
            <td>${commande.quantite}</td>
            <td>${escapeHtml(commande.type_commande)}</td>`;
        
        if (currentRole !== 'client') {
            html += `<td>${escapeHtml(commande.prenom || '')} ${escapeHtml(commande.nom || '')}</td>`;
        }
        
        html += `<td>${formatDate(commande.date_creation)}</td>
            <td><span class="badge badge-${commande.statut === 'terminee' ? 'completed' : 'pending'}">
                ${commande.statut === 'terminee' ? 'Terminée' : 'En attente'}
            </span></td>
            <td>
                <button class="btn btn-primary" onclick="openCommande(${commande.id})" style="padding: 0.5rem 1rem; font-size: 0.9rem;">
                    💬 Ouvrir
                </button>
            </td>
        </tr>`;
    });
    
    html += '</tbody></table>';
    return html;
}

// Ouvrir une commande
function openCommande(id) {
    window.location.href = `commande.php?id=${id}`;
}

// Formulaire nouvelle commande
function renderNouvelleCommande() {
    const content = document.getElementById('nouvelle-commande-content');
    if (!content) return;
    
    content.innerHTML = `
        <form id="nouvelleCommandeForm" style="max-width: 600px;">
            <div id="formAlert"></div>
            
            <div style="margin-bottom: 1.5rem;">
                <label style="display: block; margin-bottom: 0.5rem; font-weight: bold;">
                    Nom du service <span style="color: #e94560;">*</span>
                </label>
                <input type="text" id="nomService" required 
                    style="width: 100%; padding: 0.8rem; border: 1px solid rgba(255,255,255,0.2); border-radius: 5px; background: rgba(255,255,255,0.05); color: #fff; font-size: 1rem;">
            </div>
            
            <div style="margin-bottom: 1.5rem;">
                <label style="display: block; margin-bottom: 0.5rem; font-weight: bold;">
                    Quantité <span style="color: #e94560;">*</span>
                </label>
                <input type="number" id="quantite" min="1" value="1" required 
                    style="width: 100%; padding: 0.8rem; border: 1px solid rgba(255,255,255,0.2); border-radius: 5px; background: rgba(255,255,255,0.05); color: #fff; font-size: 1rem;">
            </div>
            
            <div style="margin-bottom: 1.5rem;">
                <label style="display: block; margin-bottom: 0.5rem; font-weight: bold;">
                    Type de commande <span style="color: #e94560;">*</span>
                </label>
                <select id="typeCommande" required 
                    style="width: 100%; padding: 0.8rem; border: 1px solid rgba(255,255,255,0.2); border-radius: 5px; background: rgba(255,255,255,0.05); color: #fff; font-size: 1rem;">
                    <option value="">-- Sélectionnez --</option>
                    <option value="personnel">Personnel</option>
                    <option value="professionnel">Professionnel</option>
                </select>
            </div>
            
            <button type="submit" class="btn btn-primary" style="width: 100%; padding: 1rem; font-size: 1.1rem;">
                ✓ Créer la commande
            </button>
        </form>
    `;
    
    document.getElementById('nouvelleCommandeForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const nomService = document.getElementById('nomService').value.trim();
        const quantite = parseInt(document.getElementById('quantite').value);
        const typeCommande = document.getElementById('typeCommande').value;
        
        try {
            const response = await fetch('api/commandes.php?action=create', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    nom_service: nomService,
                    quantite: quantite,
                    type_commande: typeCommande
                })
            });
            
            const data = await response.json();
            
            if (data.success) {
                showFormAlert('formAlert', 'Commande créée avec succès !', 'success');
                this.reset();
                await loadCommandes();
                
                setTimeout(() => {
                    openCommande(data.commande_id);
                }, 1500);
            } else {
                showFormAlert('formAlert', data.error, 'error');
            }
        } catch (error) {
            showFormAlert('formAlert', 'Erreur lors de la création', 'error');
        }
    });
}

// Charger le personnel (Direction)
async function loadPersonnel() {
    try {
        const response = await fetch('api/personnel.php?action=list');
        const data = await response.json();
        
        if (data.success) {
            personnelData = data.personnel;
            updatePersonnelDisplay();
        }
    } catch (error) {
        console.error('Erreur lors du chargement du personnel:', error);
    }
}

// Afficher le personnel
function updatePersonnelDisplay() {
    const content = document.getElementById('gestion-personnel-content');
    if (!content) return;
    
    if (personnelData.length === 0) {
        content.innerHTML = '<p style="text-align: center; color: #999;">Aucun membre du personnel</p>';
        return;
    }
    
    let html = '<table><thead><tr>';
    html += '<th>Nom</th>';
    html += '<th>Email</th>';
    html += '<th>Téléphone</th>';
    html += '<th>Statut</th>';
    html += '<th>Date création</th>';
    html += '<th>Actions</th>';
    html += '</tr></thead><tbody>';
    
    personnelData.forEach(user => {
        html += `<tr>
            <td>${escapeHtml(user.prenom)} ${escapeHtml(user.nom)}</td>
            <td>${escapeHtml(user.email)}</td>
            <td>${escapeHtml(user.telephone || '-')}</td>
            <td><span class="badge badge-${user.actif ? 'completed' : 'pending'}">
                ${user.actif ? 'Actif' : 'Inactif'}
            </span></td>
            <td>${formatDate(user.date_creation)}</td>
            <td>
                <button class="btn btn-primary" onclick="editPersonnel(${user.id})" 
                    style="padding: 0.4rem 0.8rem; font-size: 0.85rem; margin-right: 0.5rem;">
                    ✏️ Modifier
                </button>
                <button class="btn" onclick="deletePersonnel(${user.id})" 
                    style="padding: 0.4rem 0.8rem; font-size: 0.85rem; background: #ff4444; color: white;">
                    🗑️ Supprimer
                </button>
            </td>
        </tr>`;
    });
    
    html += '</tbody></table>';
    content.innerHTML = html;
}

// Modal pour ajouter un personnel
function showAddPersonnelModal() {
    const modal = createModal('Ajouter un membre du personnel', `
        <form id="addPersonnelForm">
            <div id="modalAlert"></div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                <div>
                    <label style="display: block; margin-bottom: 0.5rem; color: #fff;">Nom *</label>
                    <input type="text" name="nom" required style="width: 100%; padding: 0.6rem; border-radius: 5px; border: 1px solid #ccc;">
                </div>
                <div>
                    <label style="display: block; margin-bottom: 0.5rem; color: #fff;">Prénom *</label>
                    <input type="text" name="prenom" required style="width: 100%; padding: 0.6rem; border-radius: 5px; border: 1px solid #ccc;">
                </div>
            </div>
            <div style="margin-bottom: 1rem;">
                <label style="display: block; margin-bottom: 0.5rem; color: #fff;">Email *</label>
                <input type="email" name="email" required style="width: 100%; padding: 0.6rem; border-radius: 5px; border: 1px solid #ccc;">
            </div>
            <div style="margin-bottom: 1rem;">
                <label style="display: block; margin-bottom: 0.5rem; color: #fff;">Mot de passe *</label>
                <input type="password" name="password" required minlength="6" style="width: 100%; padding: 0.6rem; border-radius: 5px; border: 1px solid #ccc;">
            </div>
            <div style="margin-bottom: 1rem;">
                <label style="display: block; margin-bottom: 0.5rem; color: #fff;">Téléphone</label>
                <input type="tel" name="telephone" style="width: 100%; padding: 0.6rem; border-radius: 5px; border: 1px solid #ccc;">
            </div>
            <div style="display: flex; gap: 1rem; justify-content: flex-end; margin-top: 1.5rem;">
                <button type="button" onclick="closeModal()" style="padding: 0.6rem 1.5rem; border: none; background: #666; color: white; border-radius: 5px; cursor: pointer;">
                    Annuler
                </button>
                <button type="submit" style="padding: 0.6rem 1.5rem; border: none; background: #e94560; color: white; border-radius: 5px; cursor: pointer;">
                    Créer
                </button>
            </div>
        </form>
    `);
    
    document.getElementById('addPersonnelForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const data = {
            nom: formData.get('nom'),
            prenom: formData.get('prenom'),
            email: formData.get('email'),
            password: formData.get('password'),
            telephone: formData.get('telephone')
        };
        
        try {
            const response = await fetch('api/personnel.php?action=create', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            });
            
            const result = await response.json();
            
            if (result.success) {
                showFormAlert('modalAlert', 'Membre créé avec succès !', 'success');
                setTimeout(() => {
                    closeModal();
                    loadPersonnel();
                }, 1500);
            } else {
                showFormAlert('modalAlert', result.error, 'error');
            }
        } catch (error) {
            showFormAlert('modalAlert', 'Erreur lors de la création', 'error');
        }
    });
}

// Modifier un personnel
function editPersonnel(id) {
    alert('Fonction de modification en cours de développement');
    // TODO: Implémenter la modification
}

// Supprimer un personnel
async function deletePersonnel(id) {
    if (!confirm('Êtes-vous sûr de vouloir supprimer ce membre du personnel ?')) {
        return;
    }
    
    try {
        const response = await fetch('api/personnel.php?action=delete', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ id: id })
        });
        
        const data = await response.json();
        
        if (data.success) {
            alert('Membre supprimé avec succès');
            loadPersonnel();
        } else {
            alert('Erreur : ' + data.error);
        }
    } catch (error) {
        alert('Erreur lors de la suppression');
    }
}

// Utilitaires
function escapeHtml(text) {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return String(text || '').replace(/[&<>"']/g, m => map[m]);
}

function formatDate(dateString) {
    if (!dateString) return '-';
    const date = new Date(dateString);
    return date.toLocaleDateString('fr-FR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

function showFormAlert(elementId, message, type) {
    const alertDiv = document.getElementById(elementId);
    if (!alertDiv) return;
    
    alertDiv.className = `alert alert-${type}`;
    alertDiv.textContent = message;
    alertDiv.style.display = 'block';
    
    setTimeout(() => {
        alertDiv.style.display = 'none';
    }, 5000);
}

function createModal(title, content) {
    const modal = document.createElement('div');
    modal.id = 'customModal';
    modal.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0,0,0,0.8);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 1000;
    `;
    
    modal.innerHTML = `
        <div style="background: #16213e; padding: 2rem; border-radius: 10px; max-width: 600px; width: 90%; max-height: 90vh; overflow-y: auto;">
            <h2 style="color: #e94560; margin-bottom: 1.5rem;">${title}</h2>
            ${content}
        </div>
    `;
    
    document.body.appendChild(modal);
    return modal;
}

function closeModal() {
    const modal = document.getElementById('customModal');
    if (modal) {
        modal.remove();
    }
}