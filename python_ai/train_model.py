"""
PARTIE 2 : ENTRAÎNEMENT DU MODÈLE CNN
CORRIGÉ : Utilise le grayscale comme dataset FER2013
Architecture : CNN simple + grayscale 48x48x1
Sortie : model/emotion_model.keras + model/labels.json
"""

import os, json, warnings
os.environ["TF_CPP_MIN_LOG_LEVEL"] = "2"
warnings.filterwarnings("ignore")

import numpy as np
import tensorflow as tf
from tensorflow import keras
from tensorflow.keras import layers
from tensorflow.keras.preprocessing.image import ImageDataGenerator
from tensorflow.keras.callbacks import ModelCheckpoint, EarlyStopping, ReduceLROnPlateau
import matplotlib
matplotlib.use("Agg")
import matplotlib.pyplot as plt

DATASET_DIR  = "dataset"
MODEL_DIR    = "model"
IMG_SIZE     = (48, 48)
BATCH_SIZE   = 32
EPOCHS       = 50
EMOTIONS     = ["angry", "fear", "happy", "neutral", "sad", "surprise"]
NUM_CLASSES  = len(EMOTIONS)

os.makedirs(MODEL_DIR, exist_ok=True)

STRESS_SCORES = {
    "angry": 90, "fear": 85, "sad": 65,
    "surprise": 50, "neutral": 20, "happy": 5,
}

def compute_stress(emotion, probabilities=None):
    if probabilities is not None and len(probabilities) == NUM_CLASSES:
        return int(round(sum(
            STRESS_SCORES[EMOTIONS[i]] * float(probabilities[i])
            for i in range(NUM_CLASSES)
        )))
    return STRESS_SCORES.get(emotion, 20)

def build_generators():
    """Générateurs de données en GRAYSCALE (comme FER2013)"""
    train_datagen = ImageDataGenerator(
        rescale=1./255,
        rotation_range=15,
        width_shift_range=0.1,
        height_shift_range=0.1,
        shear_range=0.1,
        zoom_range=0.1,
        horizontal_flip=True,
        fill_mode="nearest"
    )
    test_datagen = ImageDataGenerator(rescale=1./255)

    # CRUCIAL: color_mode='grayscale' pour correspondre au dataset FER2013
    train_gen = train_datagen.flow_from_directory(
        os.path.join(DATASET_DIR, "train"),
        target_size=IMG_SIZE,
        color_mode='grayscale',   # ← CHANGEMENT IMPORTANT
        batch_size=BATCH_SIZE,
        class_mode='categorical',
        classes=EMOTIONS,
        shuffle=True
    )
    test_gen = test_datagen.flow_from_directory(
        os.path.join(DATASET_DIR, "test"),
        target_size=IMG_SIZE,
        color_mode='grayscale',   # ← CHANGEMENT IMPORTANT
        batch_size=BATCH_SIZE,
        class_mode='categorical',
        classes=EMOTIONS,
        shuffle=False
    )
    print(f"[OK] Train: {train_gen.samples} | Test: {test_gen.samples}")
    print(f"[OK] Format image: {train_gen.image_shape}")  # Devrait être (48,48,1)
    return train_gen, test_gen

def build_model():
    """
    Architecture CNN adaptée au GRAYSCALE (48x48x1)
    Plus simple et plus rapide que MobileNetV2 pour les petits formats
    """
    inputs = keras.Input(shape=(48, 48, 1), name="input_image")
    
    # Bloc 1
    x = layers.Conv2D(32, 3, activation='relu', padding='same')(inputs)
    x = layers.BatchNormalization()(x)
    x = layers.Conv2D(32, 3, activation='relu', padding='same')(x)
    x = layers.BatchNormalization()(x)
    x = layers.MaxPooling2D(2)(x)
    x = layers.Dropout(0.25)(x)
    
    # Bloc 2
    x = layers.Conv2D(64, 3, activation='relu', padding='same')(x)
    x = layers.BatchNormalization()(x)
    x = layers.Conv2D(64, 3, activation='relu', padding='same')(x)
    x = layers.BatchNormalization()(x)
    x = layers.MaxPooling2D(2)(x)
    x = layers.Dropout(0.25)(x)
    
    # Bloc 3
    x = layers.Conv2D(128, 3, activation='relu', padding='same')(x)
    x = layers.BatchNormalization()(x)
    x = layers.Conv2D(128, 3, activation='relu', padding='same')(x)
    x = layers.BatchNormalization()(x)
    x = layers.MaxPooling2D(2)(x)
    x = layers.Dropout(0.25)(x)
    
    # Tête dense
    x = layers.GlobalAveragePooling2D()(x)
    x = layers.Dense(256, activation='relu')(x)
    x = layers.BatchNormalization()(x)
    x = layers.Dropout(0.5)(x)
    x = layers.Dense(128, activation='relu')(x)
    x = layers.BatchNormalization()(x)
    x = layers.Dropout(0.3)(x)
    outputs = layers.Dense(NUM_CLASSES, activation='softmax', name="predictions")(x)
    
    model = keras.Model(inputs, outputs, name="emotion_detector_grayscale")
    model.compile(
        optimizer=keras.optimizers.Adam(learning_rate=1e-3),
        loss="categorical_crossentropy",
        metrics=["accuracy"]
    )
    model.summary()
    return model

def get_callbacks():
    return [
        ModelCheckpoint(
            os.path.join(MODEL_DIR, "best_emotion_model.keras"),
            monitor="val_accuracy", 
            save_best_only=True, 
            verbose=1
        ),
        EarlyStopping(monitor="val_accuracy", patience=10, restore_best_weights=True),
        ReduceLROnPlateau(monitor="val_loss", factor=0.5, patience=5, min_lr=1e-7, verbose=1),
    ]

def train():
    train_gen, test_gen = build_generators()
    model = build_model()
    
    print("\n=== Entraînement du modèle en grayscale ===")
    history = model.fit(
        train_gen, 
        epochs=EPOCHS, 
        validation_data=test_gen,
        callbacks=get_callbacks(), 
        verbose=1
    )
    
    # Sauvegarde finale
    final_path = os.path.join(MODEL_DIR, "emotion_model.keras")
    model.save(final_path)
    print(f"\n[OK] Modèle sauvegardé -> {final_path}")
    
    # Sauvegarde des labels
    with open(os.path.join(MODEL_DIR, "labels.json"), "w") as f:
        json.dump({"emotions": EMOTIONS, "stress_scores": STRESS_SCORES}, f, indent=2)
    
    # Courbes d'entraînement
    fig, axes = plt.subplots(1, 2, figsize=(12, 4))
    
    # Accuracy
    axes[0].plot(history.history['accuracy'], label='Train')
    axes[0].plot(history.history['val_accuracy'], label='Validation')
    axes[0].set_title('Accuracy')
    axes[0].set_xlabel('Epoch')
    axes[0].set_ylabel('Accuracy')
    axes[0].legend()
    axes[0].grid(True)
    
    # Loss
    axes[1].plot(history.history['loss'], label='Train')
    axes[1].plot(history.history['val_loss'], label='Validation')
    axes[1].set_title('Loss')
    axes[1].set_xlabel('Epoch')
    axes[1].set_ylabel('Loss')
    axes[1].legend()
    axes[1].grid(True)
    
    plt.tight_layout()
    plt.savefig(os.path.join(MODEL_DIR, "training_curves.png"), dpi=150)
    print(f"[OK] Courbes sauvegardées -> {MODEL_DIR}/training_curves.png")
    
    # Évaluation finale
    loss, acc = model.evaluate(test_gen, verbose=0)
    print(f"\n[OK] Précision finale sur test : {acc*100:.2f}%")
    return model

def test_image(img_path, model_path=None):
    """Test sur une image externe (sera convertie en grayscale)"""
    if model_path is None:
        model_path = os.path.join(MODEL_DIR, "emotion_model.keras")
    model = keras.models.load_model(model_path, compile=False)
    
    # Charger l'image et la convertir en grayscale
    img = keras.utils.load_img(img_path, target_size=IMG_SIZE, color_mode='grayscale')
    arr = np.expand_dims(np.array(img) / 255.0, axis=0)
    arr = np.expand_dims(arr, axis=-1)  # Ajouter canal si nécessaire
    
    preds = model.predict(arr, verbose=0)[0]
    emotion = EMOTIONS[np.argmax(preds)]
    stress = compute_stress(emotion, preds)
    
    print(f"\n📸 Résultat pour {img_path}:")
    print(f"   Émotion: {emotion.upper()}  |  Stress: {stress}/100")
    print("\n   Probabilités:")
    for i, e in enumerate(EMOTIONS):
        bar = '█' * int(preds[i] * 30)
        print(f"   {e:<10}: {preds[i]*100:5.1f}%  {bar}")
    return emotion, stress

if __name__ == "__main__":
    import argparse
    parser = argparse.ArgumentParser()
    parser.add_argument("--test", type=str, default=None, help="Tester sur une image")
    args = parser.parse_args()
    
    if args.test:
        test_image(args.test)
    else:
        train()