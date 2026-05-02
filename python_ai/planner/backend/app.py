"""
API Flask pour le planificateur IA
"""

from flask import Flask, request, jsonify
from flask_cors import CORS
import json
import os
from datetime import datetime

from ia_planner import IAPlanner
from profile_builder import ProfileBuilder

app = Flask(__name__)
CORS(app)  # Permet les requêtes depuis le frontend

# Initialisation
ia_planner = IAPlanner()

# Fichiers de stockage
PROFILES_FILE = "data/profiles.json"
PLANNING_HISTORY_FILE = "data/planning_history.json"

# Créer les dossiers si besoin
os.makedirs("data", exist_ok=True)

@app.route("/api/health", methods=["GET"])
def health_check():
    """Vérifie que l'API fonctionne"""
    return jsonify({"status": "ok", "message": "API IA Planner fonctionnelle"})

@app.route("/api/generate_plan", methods=["POST"])
def generate_plan():
    """
    Génère un planning à partir des données reçues
    Body attendu: {
        "profil_enfant": {...},  # Optionnel
        "contraintes": {...},
        "taches": [...]
    }
    """
    try:
        data = request.json
        
        # Récupérer le profil (ou en créer un par défaut)
        profil = data.get("profil_enfant")
        if not profil:
            # Si pas de profil fourni, essayer de charger depuis l'historique
            profil = load_last_profile()
            if not profil:
                profil = ProfileBuilder.get_default_profile()
        
        contraintes = data.get("contraintes", {})
        taches = data.get("taches", [])
        
        # Générer le planning
        planning = ia_planner.generate_weekly_plan(profil, contraintes, taches)
        
        # Sauvegarder pour historique
        save_planning(planning, profil, contraintes, taches)
        
        return jsonify({
            "success": True,
            "planning": planning,
            "text_version": ia_planner.get_weekly_plan_as_text(planning)
        })
        
    except Exception as e:
        return jsonify({
            "success": False,
            "error": str(e)
        }), 500

@app.route("/api/build_profile_from_carnets", methods=["POST"])
def build_profile_from_carnets():
    """
    Construit un profil IA à partir des données des carnets éducatifs
    Body: { "carnets": [...] }
    """
    try:
        data = request.json
        carnets = data.get("carnets", [])
        
        if not carnets:
            return jsonify({
                "success": False,
                "error": "Aucune donnée de carnet fournie"
            }), 400
        
        profil = ProfileBuilder.build_profile_from_carnet(carnets)
        
        # Sauvegarder le profil
        save_profile(profil)
        
        return jsonify({
            "success": True,
            "profil": profil
        })
        
    except Exception as e:
        return jsonify({
            "success": False,
            "error": str(e)
        }), 500

@app.route("/api/save_manual_profile", methods=["POST"])
def save_manual_profile():
    """Sauvegarde un profil saisi manuellement par le parent"""
    try:
        profil = request.json
        save_profile(profil)
        return jsonify({"success": True, "message": "Profil sauvegardé"})
    except Exception as e:
        return jsonify({"success": False, "error": str(e)}), 500

@app.route("/api/get_last_profile", methods=["GET"])
def get_last_profile():
    """Récupère le dernier profil sauvegardé"""
    profil = load_last_profile()
    if profil:
        return jsonify({"success": True, "profil": profil})
    return jsonify({"success": False, "profil": None})

@app.route("/api/get_planning_history", methods=["GET"])
def get_planning_history():
    """Récupère l'historique des plannings générés"""
    history = load_planning_history()
    return jsonify({"success": True, "history": history})

# ========== FONCTIONS UTILITAIRES ==========

def save_profile(profil):
    """Sauvegarde le profil dans un fichier"""
    try:
        with open(PROFILES_FILE, "w", encoding="utf-8") as f:
            json.dump({
                "last_update": datetime.now().isoformat(),
                "profil": profil
            }, f, ensure_ascii=False, indent=2)
    except Exception as e:
        print(f"Erreur sauvegarde profil: {e}")

def load_last_profile():
    """Charge le dernier profil sauvegardé"""
    try:
        if os.path.exists(PROFILES_FILE):
            with open(PROFILES_FILE, "r", encoding="utf-8") as f:
                data = json.load(f)
                return data.get("profil")
    except Exception as e:
        print(f"Erreur chargement profil: {e}")
    return None

def save_planning(planning, profil, contraintes, taches):
    """Sauvegarde le planning dans l'historique"""
    try:
        history = load_planning_history()
        history.append({
            "date_generation": datetime.now().isoformat(),
            "planning": planning,
            "profil_utilise": profil,
            "contraintes": contraintes,
            "taches": taches
        })
        # Garder seulement les 20 derniers
        if len(history) > 20:
            history = history[-20:]
        
        with open(PLANNING_HISTORY_FILE, "w", encoding="utf-8") as f:
            json.dump(history, f, ensure_ascii=False, indent=2)
    except Exception as e:
        print(f"Erreur sauvegarde planning: {e}")

def load_planning_history():
    """Charge l'historique des plannings"""
    try:
        if os.path.exists(PLANNING_HISTORY_FILE):
            with open(PLANNING_HISTORY_FILE, "r", encoding="utf-8") as f:
                return json.load(f)
    except Exception as e:
        print(f"Erreur chargement historique: {e}")
    return []

if __name__ == "__main__":
    print("🚀 Démarrage de l'API IA Planner...")
    print("📍 API disponible sur http://localhost:5000")
    print("📍 Health check: http://localhost:5000/api/health")
    app.run(debug=True, host="0.0.0.0", port=5000)