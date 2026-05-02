"""
Tests unitaires pour l'API IA Planner
"""

import requests
import json

BASE_URL = "http://localhost:5000/api"

def test_health():
    """Test 1: Vérifier que l'API est en ligne"""
    print("🔍 Test 1: Health check...")
    response = requests.get(f"{BASE_URL}/health")
    if response.status_code == 200:
        print("✅ API en ligne !")
        print(f"   Réponse: {response.json()}")
    else:
        print(f"❌ Erreur: {response.status_code}")
    print("-" * 50)

def test_build_profile():
    """Test 2: Construire un profil à partir de carnets"""
    print("🔍 Test 2: Construction du profil depuis carnets...")
    
    # Simuler des données de carnets éducatifs
    carnets_example = [
        {
            "matiere": "Mathématiques",
            "niveau_concentration": 4,
            "niveau_agitation": 2,
            "niveau_autonomie": 3,
            "nombre_interruptions": 2,
            "temps_avant_perte_concentration": 45,
            "niveau_difficulte": "Difficile"
        },
        {
            "matiere": "Lecture",
            "niveau_concentration": 5,
            "niveau_agitation": 1,
            "niveau_autonomie": 4,
            "nombre_interruptions": 1,
            "temps_avant_perte_concentration": 60,
            "niveau_difficulte": "Facile"
        },
        {
            "matiere": "Écriture",
            "niveau_concentration": 3,
            "niveau_agitation": 3,
            "niveau_autonomie": 2,
            "nombre_interruptions": 4,
            "temps_avant_perte_concentration": 25,
            "niveau_difficulte": "Difficile"
        }
    ]
    
    response = requests.post(
        f"{BASE_URL}/build_profile_from_carnets",
        json={"carnets": carnets_example}
    )
    
    if response.status_code == 200:
        data = response.json()
        if data.get("success"):
            print("✅ Profil construit avec succès !")
            profil = data.get("profil", {})
            print(f"   👤 Profil: Âge {profil.get('age', 8)} ans")
            print(f"   📊 Concentration: {profil.get('niveau_concentration_moyen', 0)}/5")
            print(f"   🎯 Meilleure matière: {profil.get('meilleure_matiere')}")
            print(f"   ⚠️ Matières difficiles: {profil.get('matieres_difficiles')}")
        else:
            print(f"❌ Erreur API: {data.get('error')}")
    else:
        print(f"❌ Erreur HTTP: {response.status_code}")
    print("-" * 50)

def test_generate_plan():
    """Test 3: Générer un planning complet"""
    print("🔍 Test 3: Génération du planning...")
    
    # Données de test
    contraintes = {
        "ecole": {
            "lundi": "08:30-16:00",
            "mardi": "08:30-16:00",
            "mercredi": "08:30-12:00",
            "jeudi": "08:30-16:00",
            "vendredi": "08:30-16:00"
        },
        "therapie": {
            "mardi": "17:00-18:00"
        },
        "activites": {
            "mercredi": "14:00-15:30 (Natation)"
        }
    }
    
    taches = [
        {"titre": "Devoirs de maths", "duree_estimee": 45, "difficulte": "difficile"},
        {"titre": "Lecture", "duree_estimee": 30, "difficulte": "facile"},
        {"titre": "Exercices d'écriture", "duree_estimee": 30, "difficulte": "moyen"},
        {"titre": "Méditation/relaxation", "duree_estimee": 15, "difficulte": "facile"}
    ]
    
    # Profil personnalisé
    profil = {
        "age": 9,
        "niveau_concentration_moyen": 4.2,
        "niveau_agitation_moyen": 2.1,
        "niveau_autonomie_moyen": 3.5,
        "interruptions_moyennes": 2,
        "temps_concentration_max_minutes": 50,
        "pic_attention_debut": "09:00",
        "pic_attention_fin": "11:30",
        "meilleure_matiere": "Lecture",
        "matieres_difficiles": ["Mathématiques", "Écriture"]
    }
    
    response = requests.post(
        f"{BASE_URL}/generate_plan",
        json={
            "profil_enfant": profil,
            "contraintes": contraintes,
            "taches": taches
        }
    )
    
    if response.status_code == 200:
        data = response.json()
        if data.get("success"):
            print("✅ Planning généré avec succès !")
            planning = data.get("planning", {})
            if "semaine" in planning:
                print(f"   📅 Planning sur {len(planning['semaine'])} jours")
                # Afficher un aperçu
                if planning["semaine"]:
                    premier_jour = planning["semaine"][0]
                    print(f"   📌 {premier_jour['jour']}: {len(premier_jour.get('creneaux', []))} créneaux")
                    for creneau in premier_jour.get("creneaux", [])[:3]:
                        print(f"      • {creneau['heure']} - {creneau['titre']}")
            
            # Afficher la version texte
            print("\n📝 Version texte du planning:")
            print(data.get("text_version", "Non disponible")[:500])
        else:
            print(f"❌ Erreur API: {data.get('error')}")
    else:
        print(f"❌ Erreur HTTP: {response.status_code}")
    print("-" * 50)

def test_save_and_load_profile():
    """Test 4: Sauvegarder et charger un profil"""
    print("🔍 Test 4: Sauvegarde et chargement de profil...")
    
    profil_test = {
        "age": 10,
        "niveau_concentration_moyen": 4.5,
        "niveau_agitation_moyen": 1.8,
        "pic_attention_debut": "08:30",
        "pic_attention_fin": "10:30"
    }
    
    # Sauvegarder
    response1 = requests.post(
        f"{BASE_URL}/save_manual_profile",
        json=profil_test
    )
    
    if response1.status_code == 200:
        print("✅ Profil sauvegardé")
        
        # Charger
        response2 = requests.get(f"{BASE_URL}/get_last_profile")
        if response2.status_code == 200:
            data = response2.json()
            if data.get("success"):
                profil_charge = data.get("profil", {})
                print(f"✅ Profil chargé: Âge {profil_charge.get('age')} ans")
                print(f"   Pic attention: {profil_charge.get('pic_attention_debut')} - {profil_charge.get('pic_attention_fin')}")
            else:
                print("❌ Erreur chargement")
    else:
        print("❌ Erreur sauvegarde")
    print("-" * 50)

def run_all_tests():
    """Exécute tous les tests"""
    print("\n" + "=" * 50)
    print("🧪 DÉMARRAGE DES TESTS IA PLANNER")
    print("=" * 50 + "\n")
    
    try:
        test_health()
        test_build_profile()
        test_generate_plan()
        test_save_and_load_profile()
    except requests.exceptions.ConnectionError:
        print("\n❌ ERREUR: Impossible de se connecter à l'API !")
        print("   Assurez-vous que le serveur Flask est lancé:")
        print("   cd planner/backend")
        print("   python app.py")
    except Exception as e:
        print(f"\n❌ Erreur inattendue: {e}")
    
    print("\n" + "=" * 50)
    print("🏁 TESTS TERMINÉS")
    print("=" * 50)

if __name__ == "__main__":
    run_all_tests()