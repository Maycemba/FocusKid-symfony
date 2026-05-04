import json
import re
from math import log
from pathlib import Path

BASE_DIR = Path(__file__).resolve().parent
MODEL_PATH = BASE_DIR / "var" / "ai" / "model.json"

# Copier les fonctions du script original
def normaliser(texte: str) -> str:
    texte = texte.lower()
    replacements = {
        "é": "e", "è": "e", "ê": "e", "ë": "e",
        "à": "a", "â": "a", "ä": "a",
        "î": "i", "ï": "i",
        "ô": "o", "ö": "o",
        "ù": "u", "û": "u", "ü": "u",
        "ç": "c",
    }
    for src, dst in replacements.items():
        texte = texte.replace(src, dst)
    texte = re.sub(r"[^a-z0-9\s]", " ", texte)
    texte = re.sub(r"\s+", " ", texte).strip()
    return texte

def extraire_ngrams(texte: str, min_n: int = 1, max_n: int = 2) -> list[str]:
    mots = normaliser(texte).split()
    grams = []
    for n in range(min_n, max_n + 1):
        for i in range(len(mots) - n + 1):
            grams.append(" ".join(mots[i:i + n]))
    return grams

def vectoriser(texte: str, model):
    grams = extraire_ngrams(texte)
    tf = {}
    for gram in grams:
        if gram in model["vocabulary"]:
            tf[gram] = tf.get(gram, 0) + 1
    
    vec = [0.0] * len(model["vocabulary"])
    for gram, freq in tf.items():
        idx = model["vocabulary"][gram]
        idf = model["idf"][idx]
        vec[idx] = (1.0 + log(freq)) * idf
    
    # Normalisation
    norm = sum(v * v for v in vec) ** 0.5
    if norm > 0:
        vec = [v / norm for v in vec]
    
    return vec

def predire(texte: str, model):
    vec = vectoriser(texte, model)
    
    # Calculer la similarité cosinus avec chaque centroïde
    scores = []
    for i, centroid in enumerate(model["coef"]):
        score = sum(vec[j] * centroid[j] for j in range(len(vec)))
        scores.append(score)
    
    # Retourner la classe avec le score le plus élevé
    best_index = scores.index(max(scores))
    return model["classes"][best_index], scores

# Charger le modèle
with MODEL_PATH.open("r", encoding="utf-8") as f:
    model = json.load(f)

# Tester avec des exemples
tests = [
    "cours sur les mammiferes de la foret",
    "addition et multiplication pour enfants",
    "la revolution francaise et ses personnages",
    "conjugaison des verbes au present"
]

print("\n📊 Prédictions du modèle :\n")
for texte in tests:
    categorie, scores = predire(texte, model)
    print(f"Texte : {texte}")
    print(f"Catégorie prédite : {categorie}")
    print(f"Scores détaillés :")
    for i, cls in enumerate(model["classes"]):
        print(f"  - {cls}: {scores[i]:.4f}")
    print("-" * 50)