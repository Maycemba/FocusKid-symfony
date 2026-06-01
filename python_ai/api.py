"""
 
"""

import os, json, base64, io, logging, time
os.environ["TF_CPP_MIN_LOG_LEVEL"] = "2"

import numpy as np
import cv2
from flask import Flask, request, jsonify
from flask_cors import CORS
from PIL import Image
import tensorflow as tf
from tensorflow import keras

# ─────────────────────────────────────────────
# CONFIG
# ─────────────────────────────────────────────
MODEL_PATH   = "model/emotion_model.keras"
LABELS_PATH  = "model/labels.json"
CASCADE_PATH = cv2.data.haarcascades + "haarcascade_frontalface_default.xml"
HOST         = "127.0.0.1"
PORT         = 5001
IMG_SIZE     = (48, 48)

logging.basicConfig(level=logging.INFO,
                    format="%(asctime)s [%(levelname)s] %(message)s")
log = logging.getLogger("focuskid-api")

# ─────────────────────────────────────────────
# CHARGEMENT GLOBAL (1 seule fois au démarrage)
# ─────────────────────────────────────────────
log.info("Chargement du modèle…")
model = keras.models.load_model(MODEL_PATH, compile=False)

# Warm-up avec la bonne forme
model.predict(np.zeros((1, 48, 48, 1), dtype=np.float32), verbose=0)

with open(LABELS_PATH) as f:
    meta = json.load(f)
EMOTIONS      = meta["emotions"]
STRESS_SCORES = meta["stress_scores"]
face_cascade  = cv2.CascadeClassifier(CASCADE_PATH)
log.info(f"Modèle prêt | Classes : {EMOTIONS}")

# ─────────────────────────────────────────────
# APP FLASK
# ─────────────────────────────────────────────
app = Flask(__name__)
CORS(app, origins=["http://localhost:8000", "http://127.0.0.1:8000",
                   "http://localhost:80",   "http://127.0.0.1:80"])

# ─────────────────────────────────────────────
# UTILS
# ─────────────────────────────────────────────
def compute_stress(probabilities: np.ndarray) -> int:
    return int(round(
        sum(STRESS_SCORES[EMOTIONS[i]] * float(probabilities[i])
            for i in range(len(EMOTIONS)))
    ))

def decode_image(data) -> np.ndarray:
    """Accepte : fichier multipart OU JSON base64."""
    if data is None:
        raise ValueError("Aucune image reçue")
    if isinstance(data, (bytes, bytearray)):
        img_array = np.frombuffer(data, dtype=np.uint8)
        img = cv2.imdecode(img_array, cv2.IMREAD_COLOR)
    else:
        # base64 string (data:image/jpeg;base64,XXXX  ou XXXX seul)
        if "," in data:
            data = data.split(",", 1)[1]
        raw = base64.b64decode(data)
        img_array = np.frombuffer(raw, dtype=np.uint8)
        img = cv2.imdecode(img_array, cv2.IMREAD_COLOR)
    if img is None:
        raise ValueError("Impossible de décoder l'image")
    return img

def detect_and_crop_face(img: np.ndarray):
    """Retourne le visage rogné (BGR), ou l'image entière redimensionnée en fallback."""
    gray  = cv2.cvtColor(img, cv2.COLOR_BGR2GRAY)
    faces = face_cascade.detectMultiScale(gray, 1.1, 5, minSize=(30,30))
    if len(faces) > 0:
        x, y, w, h = sorted(faces, key=lambda f: f[2]*f[3], reverse=True)[0]
        return img[y:y+h, x:x+w], True
    return img, False  # pas de visage : envoyer l'image entière

def preprocess_face(face_bgr: np.ndarray) -> np.ndarray:
    """
    Prétraite un visage pour le modèle :
    - Conversion en grayscale
    - Redimensionnement à 48x48
    - Normalisation [0,1]
    """
    # Convertir en grayscale
    face_gray = cv2.cvtColor(face_bgr, cv2.COLOR_BGR2GRAY)
    
    # Redimensionner
    face_resized = cv2.resize(face_gray, IMG_SIZE)
    
    # Normaliser et ajouter les dimensions
    arr = face_resized.astype(np.float32) / 255.0
    arr = np.expand_dims(arr, axis=-1)  # canal
    return np.expand_dims(arr, axis=0)  # batch

def predict(face_bgr: np.ndarray):
    """Prédiction à partir d'un visage BGR"""
    inp = preprocess_face(face_bgr)
    probs = model.predict(inp, verbose=0)[0]
    emotion = EMOTIONS[int(np.argmax(probs))]
    stress = compute_stress(probs)
    return emotion, stress, probs

# ─────────────────────────────────────────────
# ROUTES
# ─────────────────────────────────────────────
@app.route("/health", methods=["GET"])
def health():
    return jsonify({"status": "ok", "model": MODEL_PATH, "emotions": EMOTIONS})

@app.route("/predict", methods=["POST"])
def predict_endpoint():
    t0 = time.time()
    try:
        # ── Récupérer l'image (multipart ou JSON base64)
        if request.content_type and "multipart" in request.content_type:
            file = request.files.get("image")
            if file is None:
                return jsonify({"error": "Champ 'image' manquant"}), 400
            img_bytes = file.read()
            img = decode_image(img_bytes)
        else:
            payload = request.get_json(force=True, silent=True) or {}
            b64 = payload.get("image") or payload.get("frame")
            if b64 is None:
                return jsonify({"error": "Champ 'image' ou 'frame' manquant"}), 400
            img = decode_image(b64)

        # ── Détecter visage + prédire
        face, face_found = detect_and_crop_face(img)
        emotion, stress, probs = predict(face)

        ms = int((time.time() - t0) * 1000)
        log.info(f"→ {emotion} | stress={stress} | face={face_found} | {ms}ms")

        return jsonify({
            "emotion":      emotion,
            "stress":       stress,
            "face_detected": face_found,
            "probabilities": {EMOTIONS[i]: round(float(probs[i]), 4)
                              for i in range(len(EMOTIONS))},
            "inference_ms": ms
        })

    except Exception as e:
        log.error(f"Erreur predict: {e}", exc_info=True)
        return jsonify({"error": str(e)}), 500

@app.route("/predict/batch", methods=["POST"])
def predict_batch():
    """Traitement en lot (plusieurs frames)."""
    payload = request.get_json(force=True, silent=True) or {}
    frames = payload.get("frames", [])
    if not frames:
        return jsonify({"error": "Tableau 'frames' vide"}), 400

    results = []
    for b64 in frames[:10]:   # max 10 frames par appel
        try:
            img = decode_image(b64)
            face, found = detect_and_crop_face(img)
            emotion, stress, probs = predict(face)
            results.append({"emotion": emotion, "stress": stress, "face_detected": found})
        except Exception as e:
            results.append({"error": str(e)})

    # Score moyen
    valid = [r for r in results if "stress" in r]
    avg_stress = int(round(sum(r["stress"] for r in valid) / len(valid))) if valid else 0

    return jsonify({"frames": results, "average_stress": avg_stress})

# ─────────────────────────────────────────────
# MAIN
# ─────────────────────────────────────────────
if __name__ == "__main__":
    import argparse
    parser = argparse.ArgumentParser()
    parser.add_argument("--host", default=HOST)
    parser.add_argument("--port", type=int, default=PORT)
    parser.add_argument("--debug", action="store_true")
    args = parser.parse_args()

    log.info(f"API démarrée sur http://{args.host}:{args.port}")
    log.info("Routes : GET /health  |  POST /predict  |  POST /predict/batch")
    app.run(host=args.host, port=args.port, debug=args.debug)