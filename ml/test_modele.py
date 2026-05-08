# test_modele.py
import pandas as pd
import joblib
import numpy as np

print("=" * 60)
print("🧪 TEST DU MODÈLE DE PRÉDICTION")
print("=" * 60)

# 1. Charger le modèle et les scalers
print("\n📂 Chargement des modèles...")
modele = joblib.load('modele.pkl')
scaler = joblib.load('scaler.pkl')
scaler_score = joblib.load('scaler_score.pkl')

print("✅ Modèle chargé avec succès !")

# 2. Définir des enfants avec leurs stats (depuis TA base)
enfants = [
    {
        "id": 1,
        "nom": "Asma",
        "score_moyen": 15.5,     # Moyenne de ses scores (sur 20)
        "temps_moyen": 48,       # Moyenne de ses temps (secondes)
        "nb_exercices": 45       # Nombre d'exercices déjà faits
    },
    {
        "id": 2,
        "nom": "Youssef",
        "score_moyen": 8.5,
        "temps_moyen": 110,
        "nb_exercices": 30
    },
    {
        "id": 3,
        "nom": "Fatima",
        "score_moyen": 18.0,
        "temps_moyen": 35,
        "nb_exercices": 60
    },
    {
        "id": 4,
        "nom": "Mohamed",
        "score_moyen": 11.0,
        "temps_moyen": 85,
        "nb_exercices": 20
    }
]

# 3. Nouvel exercice à prédire
print("\n📝 Nouvel exercice à prédire :")
exercice = {
    "titre": "Les animaux de la forêt",
    "difficulte": 3,
    "type": 1,  # 1=MEMOIRE, 2=ATTENTION, 3=LOGIQUE, 4=CHRONO
    "type_nom": "MÉMOIRE"
}
print(f"   Titre : {exercice['titre']}")
print(f"   Difficulté : {exercice['difficulte']}/5")
print(f"   Type : {exercice['type_nom']}")

# 4. Faire les prédictions
print("\n" + "=" * 60)
print("🔮 PRÉDICTIONS :")
print("=" * 60)

for enfant in enfants:
    # Normaliser le score (de son barème vers 0-100)
    score_normalise = scaler_score.transform([[enfant['score_moyen']]])[0][0]
    
    # Préparer les features
    features = pd.DataFrame([[
        exercice['difficulte'],
        exercice['type'],
        score_normalise,
        enfant['temps_moyen']
    ]], columns=['difficulte', 'type_exercice', 'score_normalise', 'temps_passe'])
    
    # Standardiser
    features_scaled = scaler.transform(features)
    
    # Prédire
    prediction = modele.predict(features_scaled)[0]
    probabilites = modele.predict_proba(features_scaled)[0]
    
    # Afficher résultat
    if prediction == 1:
        statut = "✅ RÉUSSITE"
        couleur = "🟢"
        recommandation = "Peut faire l'exercice directement"
    else:
        statut = "❌ ÉCHEC"
        couleur = "🔴"
        recommandation = "⚠️ Proposer exercice plus facile d'abord"
    
    print(f"\n{couleur} {enfant['nom']} (ID: {enfant['id']})")
    print(f"   📊 Historique : score_moyen={enfant['score_moyen']}/20, temps_moyen={enfant['temps_moyen']}s, nb_exos={enfant['nb_exercices']}")
    print(f"   🔮 {statut} (probabilité: {probabilites[1]*100:.1f}%)")
    print(f"   💡 {recommandation}")

# 5. Tester plusieurs exercices pour le MÊME enfant
print("\n" + "=" * 60)
print("🎯 TEST POUR UN MÊME ENFANT AVEC DIFFÉRENTS EXERCICES")
print("=" * 60)

enfant_test = enfants[0]  # Asma
print(f"\n👧 Enfant : {enfant_test['nom']} (score moyen: {enfant_test['score_moyen']}/20)")

exercices_test = [
    {"difficulte": 1, "type": 1, "nom": "Très facile - Mémoire"},
    {"difficulte": 2, "type": 2, "nom": "Facile - Attention"},
    {"difficulte": 3, "type": 3, "nom": "Moyen - Logique"},
    {"difficulte": 4, "type": 4, "nom": "Difficile - Chrono"},
    {"difficulte": 5, "type": 1, "nom": "Très difficile - Mémoire"},
]

print("\n📊 Résultats :")
for ex in exercices_test:
    score_normalise = scaler_score.transform([[enfant_test['score_moyen']]])[0][0]
    
    features = pd.DataFrame([[
        ex['difficulte'],
        ex['type'],
        score_normalise,
        enfant_test['temps_moyen']
    ]], columns=['difficulte', 'type_exercice', 'score_normalise', 'temps_passe'])
    
    features_scaled = scaler.transform(features)
    prediction = modele.predict(features_scaled)[0]
    proba = modele.predict_proba(features_scaled)[0]
    
    resultat = "✅ Réussite" if prediction == 1 else "❌ Échec"
    print(f"   {ex['nom']:30s} : {resultat} (prob: {proba[1]*100:.1f}%)")

# 6. Simuler ce qui se passe dans TON application
print("\n" + "=" * 60)
print("💻 SIMULATION DANS TON APPLICATION")
print("=" * 60)

print("""
Quand un enseignant crée un exercice et l'assigne à des enfants :

1. Enseignant clique "Enregistrer"
2. Ton PHP/Symfony appelle ce modèle
3. Le modèle prédit pour CHAQUE enfant assigné
4. Les prédictions sont stockées dans table 'exercice_enfant'
5. L'enseignant voit immédiatement qui va réussir ou échouer

Exemple d'affichage pour l'enseignant :
┌─────────────────────────────────────────┐
│  📊 Prédictions pour l'exercice         │
│  "Les animaux de la forêt"              │
├─────────────────────────────────────────┤
│  👶 Asma     : ✅ RÉUSSITE (85%)        │
│  👶 Youssef  : ❌ ÉCHEC (30%)            │
│  👶 Fatima   : ✅ RÉUSSITE (92%)        │
│  👶 Mohamed  : ⚠️ INCERTAIN (55%)        │
└─────────────────────────────────────────┘
""")

print("=" * 60)
print("🎉 Modèle testé avec succès !")
print("=" * 60)