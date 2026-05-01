"""
convert_model.py
Re-sauvegarde le modèle existant emotion_model.h5 au format .keras natif
(compatible Keras 3 / TF 2.21+)
Lancer UNE SEULE FOIS : python convert_model.py
"""
import os
os.environ["TF_CPP_MIN_LOG_LEVEL"] = "2"

import tensorflow as tf
from tensorflow import keras

H5_PATH    = "model/emotion_model.h5"
KERAS_PATH = "model/emotion_model.keras"

print(f"[…] Chargement de {H5_PATH} …")
# On charge avec compile=False pour éviter les erreurs d'optimizer
model = keras.models.load_model(H5_PATH, compile=False)
print("[✓] Modèle chargé")

print(f"[…] Sauvegarde au format .keras → {KERAS_PATH}")
model.save(KERAS_PATH)
print(f"[✓] Terminé. Utilisez désormais : {KERAS_PATH}")
