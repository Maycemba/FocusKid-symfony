from flask import Flask, request, jsonify
from flask_cors import CORS
import joblib
import pandas as pd
import os
import sys

# Ajouter le chemin pour les imports
sys.path.append(os.path.dirname(os.path.abspath(__file__)))

app = Flask(__name__)
CORS(app)

# Chemins des fichiers
BASE_DIR = os.path.dirname(os.path.abspath(__file__))
MODELES_DIR = os.path.join(BASE_DIR, 'modeles')
DATA_DIR = os.path.join(BASE_DIR, 'data')

# Charger les modèles
modele = joblib.load(os.path.join(MODELES_DIR, 'modele.pkl'))
scaler = joblib.load(os.path.join(MODELES_DIR, 'scaler.pkl'))
scaler_score = joblib.load(os.path.join(MODELES_DIR, 'scaler_score.pkl'))

print("✅ Modèles chargés avec succès !")

@app.route('/predict', methods=['POST', 'GET'])  # 🔥 AJOUTER GET ICI
def predict():
    # Si c'est une requête GET (navigateur), retourner une page simple
    if request.method == 'GET':
        return '''
        <html>
            <head><title>API Prédiction</title></head>
            <body>
                <h1>✅ API de prédiction fonctionne !</h1>
                <p>Pour utiliser l'API, envoie une requête POST à cette adresse :</p>
                <pre>
curl -X POST http://localhost:5000/predict \\
  -H "Content-Type: application/json" \\
  -d '{"difficulte":3,"type_exercice":1,"score_moyen":15,"temps_moyen":48}'
                </pre>
                <h2>Test rapide :</h2>
                <button onclick="testPrediction()">Tester la prédiction</button>
                <pre id="result"></pre>
                <script>
                async function testPrediction() {
                    const response = await fetch('http://localhost:5000/predict', {
                        method: 'POST',
                        headers: {'Content-Type': 'application/json'},
                        body: JSON.stringify({
                            difficulte: 3,
                            type_exercice: 1,
                            score_moyen: 15,
                            temps_moyen: 48
                        })
                    });
                    const data = await response.json();
                    document.getElementById('result').innerText = JSON.stringify(data, null, 2);
                }
                </script>
            </body>
        </html>
        '''
    
    # Requête POST normale
    try:
        data = request.json
        
        # Normaliser le score
        score_normalise = scaler_score.transform([[data['score_moyen']]])[0][0]
        
        # Préparer features
        features = pd.DataFrame([[
            data['difficulte'],
            data['type_exercice'],
            score_normalise,
            data['temps_moyen']
        ]], columns=['difficulte', 'type_exercice', 'score_normalise', 'temps_passe'])
        
        # Standardiser
        features_scaled = scaler.transform(features)
        
        # Prédire
        prediction = modele.predict(features_scaled)[0]
        probabilite = modele.predict_proba(features_scaled)[0]
        
        return jsonify({
            'success': True,
            'reussite_predite': int(prediction),
            'probabilite_reussite': float(probabilite[1]) * 100,
            'probabilite_echec': float(probabilite[0]) * 100
        })
    except Exception as e:
        return jsonify({'success': False, 'error': str(e)})

@app.route('/health', methods=['GET'])
def health():
    return jsonify({'status': 'ok', 'model_loaded': True})

if __name__ == '__main__':
    print("🚀 Démarrage de l'API de prédiction...")
    print("   - Test GET : http://localhost:5000/predict")
    print("   - Health : http://localhost:5000/health")
    print("   - POST pour prédire")
    app.run(host='0.0.0.0', port=5000, debug=True)  # debug=True pour voir les erreurs