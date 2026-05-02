# 🧠 IA Planner - Planificateur de semaine pour enfants TDAH

## 📖 Description

Ce module IA génère automatiquement des plannings hebdomadaires personnalisés pour les enfants ayant un TDAH. L'IA analyse le profil cognitif de l'enfant, les contraintes familiales et les tâches à accomplir pour créer un planning optimisé.

### Fonctionnalités
- ✅ Profilage automatique à partir des carnets éducatifs
- ✅ Génération intelligente des créneaux (pics d'attention, pauses, routines)
- ✅ Interface web pour saisir les contraintes
- ✅ Export PDF/Texte du planning
- ✅ Conseils IA personnalisés pour chaque activité

## 🚀 Installation

### 1. Prérequis
- Python 3.9+
- Node.js (optionnel, pour le frontend)
- Une clé API Anthropic (Claude)

### 2. Installation du backend

```bash
cd planner/backend

# Créer un environnement virtuel
python -m venv venv
source venv/bin/activate  # Sur Windows: venv\Scripts\activate

# Installer les dépendances
pip install -r requirements.txt

# Configurer la clé API
echo "ANTHROPIC_API_KEY=votre_clé_api_ici" > .env