"""
Hugging Face Spaces - Template pour Emotion Detection API
Déployez ce fichier sur Hugging Face Spaces pour avoir une API ML cloud GRATUITE

Installation :
1. Créer un compte HF : https://huggingface.co/join
2. Créer un Space : https://huggingface.co/spaces
3. Sélectionner "Docker" template
4. Copier les fichiers python_ai/ dans le Space
5. Votre API sera accessible à : https://username-emotion-api.hf.space
"""

import os
import json
import base64
import logging
from io import BytesIO

import numpy as np
import cv2
from PIL import Image
import gradio as gr

# Éviter les logs TensorFlow
os.environ["TF_CPP_MIN_LOG_LEVEL"] = "3"

import tensorflow as tf
from tensorflow import keras

logging.basicConfig(level=logging.INFO)
log = logging.getLogger(__name__)

# ─────────────────────────────────────────────
# CONFIG
# ─────────────────────────────────────────────
IMG_SIZE = (48, 48)
MODEL_PATH = "model/emotion_model.keras"
LABELS_PATH = "model/labels.json"
CASCADE_PATH = cv2.data.haarcascades + "haarcascade_frontalface_default.xml"

# ─────────────────────────────────────────────
# CHARGEMENT DU MODÈLE
# ─────────────────────────────────────────────
log.info("Chargement du modèle...")

# Custom BatchNormalization pour compatibilité
class CompatibleBatchNormalization(keras.layers.BatchNormalization):
    def __init__(self, *args, **kwargs):
        kwargs.pop('renorm', None)
        kwargs.pop('renorm_clipping', None)
        kwargs.pop('renorm_momentum', None)
        super().__init__(*args, **kwargs)

custom_objects = {
    'BatchNormalization': CompatibleBatchNormalization,
}

try:
    model = keras.models.load_model(MODEL_PATH, custom_objects=custom_objects, compile=False)
    log.info("✓ Modèle chargé!")
except Exception as e:
    log.error(f"✗ Erreur: {e}")
    model = None

# Charger les métadonnées
with open(LABELS_PATH) as f:
    meta = json.load(f)
EMOTIONS = meta["emotions"]
STRESS_SCORES = meta["stress_scores"]

face_cascade = cv2.CascadeClassifier(CASCADE_PATH)

# ─────────────────────────────────────────────
# FONCTIONS
# ─────────────────────────────────────────────
def compute_stress(probabilities: np.ndarray) -> int:
    """Calculer le score de stress à partir des probabilités."""
    return int(round(
        sum(STRESS_SCORES[EMOTIONS[i]] * float(probabilities[i])
            for i in range(len(EMOTIONS)))
    ))

def detect_emotion(image_pil):
    """Détecter l'émotion à partir d'une image PIL."""
    
    if model is None:
        return {"error": "Modèle non chargé"}
    
    # Convertir PIL → OpenCV
    image_np = np.array(image_pil)
    if len(image_np.shape) == 3:
        image_cv = cv2.cvtColor(image_np, cv2.COLOR_RGB2BGR)
    else:
        image_cv = image_np
    
    # Détection des visages
    gray = cv2.cvtColor(image_cv, cv2.COLOR_BGR2GRAY)
    faces = face_cascade.detectMultiScale(gray, 1.3, 5, minSize=(48, 48))
    
    if len(faces) == 0:
        return {
            "emotion": None,
            "confidence": 0,
            "stress_level": 0,
            "error": "Aucun visage détecté"
        }
    
    results = []
    for (x, y, w, h) in faces:
        # Extraire et redimensionner le visage
        face_roi = gray[y:y+h, x:x+w]
        face_resized = cv2.resize(face_roi, IMG_SIZE)
        face_normalized = face_resized.astype('float32') / 255.0
        face_input = np.expand_dims(np.expand_dims(face_normalized, axis=-1), axis=0)
        
        # Prédiction
        prediction = model.predict(face_input, verbose=0)
        emotion_idx = np.argmax(prediction[0])
        emotion = EMOTIONS[emotion_idx]
        confidence = float(prediction[0][emotion_idx])
        stress = compute_stress(prediction[0])
        
        results.append({
            "emotion": emotion,
            "confidence": round(confidence, 3),
            "stress_level": stress,
            "probabilities": {
                EMOTIONS[i]: round(float(prediction[0][i]), 3)
                for i in range(len(EMOTIONS))
            }
        })
    
    # Retourner le visage avec la confiance la plus élevée
    return max(results, key=lambda x: x["confidence"])

# ─────────────────────────────────────────────
# GRADIO INTERFACE
# ─────────────────────────────────────────────
demo = gr.Interface(
    fn=detect_emotion,
    inputs=gr.Image(type="pil", label="Télécharger une photo"),
    outputs=gr.JSON(label="Résultat"),
    title="FocusKid - Détecteur d'Émotions",
    description="Détecte l'émotion et le niveau de stress à partir d'une image",
    examples=[],
)

if __name__ == "__main__":
    demo.launch(server_name="0.0.0.0", server_port=7860)
