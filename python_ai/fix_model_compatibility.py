"""
Script to fix Keras model compatibility issue.
The saved model contains BatchNormalization layers with deprecated parameters
(renorm, renorm_clipping, renorm_momentum) that are not supported in newer Keras versions.
This script loads the model with a custom object handler and re-saves it in a compatible format.
"""

import os
import json
import logging
import warnings

os.environ["TF_CPP_MIN_LOG_LEVEL"] = "2"
warnings.filterwarnings("ignore")

import tensorflow as tf
from tensorflow import keras
from tensorflow.keras import layers
import numpy as np

logging.basicConfig(level=logging.INFO, format="%(asctime)s [%(levelname)s] %(message)s")
log = logging.getLogger("model-fixer")

MODEL_DIR = "model"
OLD_MODEL_PATH = os.path.join(MODEL_DIR, "emotion_model.keras")
BACKUP_MODEL_PATH = os.path.join(MODEL_DIR, "emotion_model.keras.backup")
LABELS_PATH = os.path.join(MODEL_DIR, "labels.json")

def fix_model():
    """Fix the model by loading and re-saving it."""
    
    if not os.path.exists(OLD_MODEL_PATH):
        log.error(f"Model not found at {OLD_MODEL_PATH}")
        return False
    
    # Backup original model
    if not os.path.exists(BACKUP_MODEL_PATH):
        log.info(f"Creating backup of original model...")
        os.rename(OLD_MODEL_PATH, BACKUP_MODEL_PATH)
    
    try:
        log.info("Loading model from backup...")
        
        # Custom loader that ignores the deprecated parameters
        @keras.saving.register_keras_serializable(package='Custom')
        class CompatibleBatchNormalization(layers.BatchNormalization):
            def __init__(self, *args, **kwargs):
                # Remove deprecated parameters if present
                kwargs.pop('renorm', None)
                kwargs.pop('renorm_clipping', None)
                kwargs.pop('renorm_momentum', None)
                super().__init__(*args, **kwargs)
            
            def get_config(self):
                config = super().get_config()
                # Remove deprecated fields from config
                config.pop('renorm', None)
                config.pop('renorm_clipping', None)
                config.pop('renorm_momentum', None)
                return config
        
        # Try loading with custom objects
        custom_objects = {
            'BatchNormalization': layers.BatchNormalization,
        }
        
        # Load the model directly - keras 3.0+ should handle this
        model = keras.models.load_model(BACKUP_MODEL_PATH, compile=False)
        
        log.info("Model loaded successfully!")
        log.info(f"Model summary: {model.summary()}")
        
        # Save in new format
        log.info("Saving model in compatible format...")
        model.save(OLD_MODEL_PATH, save_format='keras')
        log.info(f"✓ Model saved to {OLD_MODEL_PATH}")
        
        # Test loading the new model
        log.info("Testing new model...")
        test_model = keras.models.load_model(OLD_MODEL_PATH, compile=False)
        
        # Warm-up
        test_model.predict(np.zeros((1, 48, 48, 1), dtype=np.float32), verbose=0)
        log.info("✓ Model is compatible and working!")
        
        return True
        
    except Exception as e:
        log.error(f"Error loading/saving model: {e}")
        # Restore backup
        if os.path.exists(BACKUP_MODEL_PATH):
            os.rename(BACKUP_MODEL_PATH, OLD_MODEL_PATH)
            log.info("Restored original model from backup")
        return False

if __name__ == "__main__":
    log.info("=" * 60)
    log.info("Keras Model Compatibility Fixer")
    log.info("=" * 60)
    
    success = fix_model()
    
    if success:
        log.info("=" * 60)
        log.info("✓ Model fixed successfully!")
        log.info("=" * 60)
    else:
        log.error("Failed to fix model")
        exit(1)
