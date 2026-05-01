"""
prepare_dataset_fer2013.py
Télécharge FER2013 depuis Kaggle et organise en train/test
─────────────────────────────────────────────────────────
PRÉREQUIS :
  pip install kaggle
  Placer kaggle.json dans C:/Users/<YOU>/.kaggle/kaggle.json
  (Obtenir sur https://www.kaggle.com/settings → API → Create New Token)
─────────────────────────────────────────────────────────
"""

import os, zipfile, shutil, csv
from pathlib import Path

DATASET_DIR = "dataset"
EMOTIONS_MAP = {
    "0": "angry",
    "1": None,        # disgust → ignoré (trop peu d'échantillons)
    "2": "fear",
    "3": "happy",
    "4": "sad",
    "5": "surprise",
    "6": "neutral",
}

# ─────────────────────────────────────────────
# ÉTAPE 1 : Créer l'arborescence
# ─────────────────────────────────────────────
def create_dirs():
    for split in ["train", "test"]:
        for emotion in ["angry","fear","happy","neutral","sad","surprise"]:
            os.makedirs(f"{DATASET_DIR}/{split}/{emotion}", exist_ok=True)
    print("[OK] Dossiers créés")

# ─────────────────────────────────────────────
# ÉTAPE 2 : Télécharger FER2013 via Kaggle CLI
# ─────────────────────────────────────────────
def download_fer2013():
    if os.path.exists("fer2013.csv"):
        print("[OK] fer2013.csv déjà présent, skip téléchargement")
        return

    print("[...] Téléchargement FER2013 depuis Kaggle...")
    ret = os.system("kaggle datasets download -d msambare/fer2013 --unzip")
    if ret != 0:
        print("""
[ERREUR] Kaggle CLI a échoué. Solutions alternatives :

  OPTION A — Téléchargement manuel :
    1. Aller sur https://www.kaggle.com/datasets/msambare/fer2013
    2. Télécharger le zip
    3. Extraire fer2013.csv dans le dossier python_ai/
    4. Relancer ce script

  OPTION B — Via tensorflow_datasets :
    pip install tensorflow-datasets
    python prepare_dataset.py --mode fer2013
""")
        raise SystemExit(1)

    print("[OK] Téléchargement terminé")

# ─────────────────────────────────────────────
# ÉTAPE 3 : Parser le CSV et sauvegarder images
# ─────────────────────────────────────────────
def parse_and_save():
    import numpy as np
    from PIL import Image

    print("[...] Parsing fer2013.csv...")
    counters = {}
    skipped  = 0

    with open("fer2013.csv", newline="") as f:
        reader = csv.DictReader(f)
        for row in reader:
            label_idx = row["emotion"]
            emotion   = EMOTIONS_MAP.get(label_idx)
            if emotion is None:
                skipped += 1
                continue  # ignorer 'disgust'

            # Usage = Training ou PublicTest/PrivateTest
            usage = row["Usage"]
            split = "train" if usage == "Training" else "test"

            # Pixels : chaîne "128 135 ..."  → image 48x48 niveaux de gris
            pixels = np.array(row["pixels"].split(), dtype=np.uint8).reshape(48, 48)
            img    = Image.fromarray(pixels, mode="L").convert("RGB")

            key = f"{split}_{emotion}"
            counters[key] = counters.get(key, 0) + 1
            idx  = counters[key]
            path = f"{DATASET_DIR}/{split}/{emotion}/{emotion}_{idx:05d}.jpg"
            img.save(path, quality=95)

    print(f"\n[OK] Dataset créé. {skipped} images 'disgust' ignorées.")
    print("\nStatistiques :")
    for split in ["train", "test"]:
        print(f"\n  [{split}]")
        for emotion in ["angry","fear","happy","neutral","sad","surprise"]:
            key = f"{split}_{emotion}"
            n   = counters.get(key, 0)
            bar = "█" * (n // 100)
            print(f"    {emotion:<10} : {n:>5}  {bar}")

# ─────────────────────────────────────────────
# ÉTAPE 4 : Nettoyage fichiers temporaires
# ─────────────────────────────────────────────
def cleanup():
    for f in ["fer2013.csv", "fer2013.zip"]:
        if os.path.exists(f):
            os.remove(f)
            print(f"[OK] Supprimé : {f}")

# ─────────────────────────────────────────────
# MAIN
# ─────────────────────────────────────────────
if __name__ == "__main__":
    create_dirs()
    download_fer2013()
    parse_and_save()
    cleanup()
    print("\n[DONE] Dataset FER2013 prêt dans ./dataset/")
    print("       Lance maintenant : python train_model.py")
