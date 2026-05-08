import numpy as np
import json
import pickle
import os
from sklearn.ensemble import RandomForestClassifier
from sklearn.model_selection import train_test_split
from sklearn.preprocessing import LabelEncoder

# --- Génération de données synthétiques ---
# On simule 1000 enfants avec des profils émotionnels variés

def generate_synthetic_data(n=1200):
    """
    Génère des données synthétiques basées sur des règles psychologiques.
    Chaque ligne = 5 scores (0-4) correspondant à 5 questions aléatoires.
    Label = état émotionnel global.
    """
    np.random.seed(42)
    X, y = [], []

    emotions = {
        "serein":    {"range": (14, 20), "weight": 0.30},
        "anxieux":   {"range": (8, 14),  "weight": 0.25},
        "triste":    {"range": (0, 8),   "weight": 0.20},
        "agite":     {"range": (6, 14),  "weight": 0.15},
        "en_colere": {"range": (0, 10),  "weight": 0.10},
    }

    for emotion, cfg in emotions.items():
        n_samples = int(n * cfg["weight"])
        lo, hi = cfg["range"]

        for _ in range(n_samples):
            # 5 scores simulant 5 questions (valeurs 0 à 4)
            total_target = np.random.randint(lo, hi + 1)
            scores = np.random.dirichlet(np.ones(5)) * total_target
            scores = np.clip(np.round(scores).astype(int), 0, 4)

            # Ajout de bruit réaliste (comportement TDAH)
            noise_idx = np.random.randint(0, 5)
            scores[noise_idx] = np.random.randint(0, 5)

            X.append(scores.tolist())
            y.append(emotion)

    return np.array(X), np.array(y)


# --- Entraînement ---
X, y = generate_synthetic_data(1200)

le = LabelEncoder()
y_enc = le.fit_transform(y)

X_train, X_test, y_train, y_test = train_test_split(
    X, y_enc, test_size=0.2, random_state=42
)

model = RandomForestClassifier(n_estimators=100, random_state=42)
model.fit(X_train, y_train)

score = model.score(X_test, y_test)
print(f"Précision du modèle : {score:.2%}")

# --- Sauvegarde ---
script_dir = os.path.dirname(os.path.abspath(__file__))
model_path = os.path.join(script_dir, "model.pkl")

with open(model_path, "wb") as f:
    pickle.dump({"model": model, "label_encoder": le}, f)

print(f"Modèle sauvegardé dans {model_path}")