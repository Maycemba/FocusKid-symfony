// public/base-front/js/exercice-guardian.js

const ExerciceGuardian = {
    verificationComplete: false,
    isChildVerified: false,
    DEMO_MODE: false,  // ← METTRE À true POUR ACTIVER LE MODE DÉMO
    
    async init() {
        console.log('🔧 ExerciceGuardian initialisation...');
        
        if (this.DEMO_MODE) {
            console.log('🎮 MODE DÉMO - Vérification désactivée');
            FaceDetectionService.isModelLoaded = true;
            FaceDetectionService.isCalibrated = true;
            return true;
        }
        
        const success = await FaceDetectionService.init();
        console.log('FaceDetectionService init:', success);
        return success;
    },
    
    async start(videoElement, onStop, onViolation) {
        if (this.DEMO_MODE) {
            console.log('🎮 MODE DÉMO - Démarrage immédiat du jeu');
            
            const statusSpan = document.getElementById('faceStatus');
            if (statusSpan) {
                statusSpan.innerHTML = '🎮 MODE DÉMO (caméra désactivée)';
                statusSpan.style.background = '#FF9800';
            }
            
            // Cacher la caméra en mode démo
            const cameraContainer = document.getElementById('cameraContainer');
            if (cameraContainer) {
                cameraContainer.style.display = 'none';
            }
            
            // Simuler une vérification enfant après 1 seconde
            setTimeout(() => {
                console.log('✅ Simulation: enfant vérifié');
                if (onViolation) onViolation(0, { age: 8, isChild: true });
            }, 1000);
            
            return;
        }
        
        // Code normal si pas en mode démo
        console.log('🎮 Démarrage ExerciceGuardian.start...');
        
        const cameraStarted = await FaceDetectionService.startCamera(videoElement);
        if (!cameraStarted) {
            if (onStop) onStop('❌ Impossible d\'accéder à la caméra');
            return;
        }
        
        let waitCount = 0;
        while (!FaceDetectionService.isModelLoaded && waitCount < 30) {
            await new Promise(r => setTimeout(r, 500));
            waitCount++;
        }
        
        if (!FaceDetectionService.isModelLoaded) {
            if (onStop) onStop('❌ Modèles non chargés');
            return;
        }
        
        console.log('✅ Modèles chargés, caméra active');
        
        if (!FaceDetectionService.isCalibrated) {
            console.log('📸 Calibration automatique...');
            const statusSpan = document.getElementById('faceStatus');
            if (statusSpan) {
                statusSpan.innerHTML = '📸 Calibration...';
                statusSpan.style.background = '#FF9800';
            }
            
            const calibrated = await FaceDetectionService.calibrate(videoElement);
            if (!calibrated) {
                if (onStop) onStop('❌ Calibration échouée');
                return;
            }
        }
        
        console.log('🔍 Début vérification enfant...');
        
        FaceDetectionService.startVerification(
            videoElement,
            (count, result) => {
                if (onViolation) onViolation(count, result);
                if (count >= 3 && onStop) {
                    onStop('⚠️ Adulte détecté !');
                }
            },
            (result) => {
                if (onViolation) onViolation(0, result);
            }
        );
    },
    
    stop() {
        if (!this.DEMO_MODE) {
            FaceDetectionService.stopCamera();
        }
    }
};

window.ExerciceGuardian = ExerciceGuardian;