import json
import re
from collections import Counter
from math import log
from pathlib import Path


BASE_DIR = Path(__file__).resolve().parent
DATA_PATH = BASE_DIR / "var" / "ai" / "train_data.json"
MODEL_PATH = BASE_DIR / "var" / "ai" / "model.json"


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


def main() -> None:
    if not DATA_PATH.exists():
        raise FileNotFoundError(f"Fichier introuvable: {DATA_PATH}")

    with DATA_PATH.open("r", encoding="utf-8") as f:
        data = json.load(f)

    texts = [item["texte"] for item in data]
    labels = [item["categorie"] for item in data]
    classes = sorted(set(labels))

    docs_ngrams = [extraire_ngrams(text) for text in texts]

    vocab_set = set()
    for grams in docs_ngrams:
        vocab_set.update(grams)
    vocabulary = {gram: idx for idx, gram in enumerate(sorted(vocab_set))}

    doc_freq = Counter()
    for grams in docs_ngrams:
        for gram in set(grams):
            doc_freq[gram] += 1

    n_docs = len(texts)
    idf = [0.0] * len(vocabulary)
    for gram, idx in vocabulary.items():
        idf[idx] = log((n_docs + 1) / (doc_freq[gram] + 1)) + 1.0

    vectors = []
    for grams in docs_ngrams:
        tf = Counter(grams)
        vec = [0.0] * len(vocabulary)
        for gram, freq in tf.items():
            if gram not in vocabulary:
                continue
            idx = vocabulary[gram]
            vec[idx] = (1.0 + log(freq)) * idf[idx]

        norm = sum(v * v for v in vec) ** 0.5
        if norm > 0:
            vec = [v / norm for v in vec]
        vectors.append(vec)

    centroids = []
    for cls in classes:
        cls_vectors = [vectors[i] for i, label in enumerate(labels) if label == cls]
        centroid = [0.0] * len(vocabulary)
        if cls_vectors:
            for vec in cls_vectors:
                for i, value in enumerate(vec):
                    centroid[i] += value
            centroid = [value / len(cls_vectors) for value in centroid]
        centroids.append(centroid)

    model = {
        "classes": classes,
        "vocabulary": vocabulary,
        "idf": idf,
        "intercept": [0.0 for _ in classes],
        "coef": centroids,
    }

    MODEL_PATH.parent.mkdir(parents=True, exist_ok=True)
    with MODEL_PATH.open("w", encoding="utf-8") as f:
        json.dump(model, f, ensure_ascii=False, indent=2)

    print(f"Modèle généré: {MODEL_PATH}")


if __name__ == "__main__":
    main()
