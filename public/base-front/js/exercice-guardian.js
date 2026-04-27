// public/base-front/js/exercice-guardian.js

const ExerciceGuardian = {
    async init() {
        await FaceDetectionService.init();
        return { success: true, faceLoaded: FaceDetectionService.isCalibrated };
    },

    async calibrateAuto(videoElement) {
        console.log('📸 Calibration automatique dans 3 secondes...');
        // Compter à rebours visuel
        for (let i = 3; i > 0; i--) {
            console.log(`${i}...`);
            await new Promise(r => setTimeout(r, 1000));
        }
        return await FaceDetectionService.calibrate(videoElement);
    },

    start(videoElement, onStop, onViolation) {
        FaceDetectionService.startCamera(videoElement).then(async (success) => {
            if (!success) {
                if (onStop) onStop('❌ Impossible d\'accéder à la caméra');
                return;
            }
            
            // Si pas calibré, calibration AUTO
            if (!FaceDetectionService.isCalibrated) {
                console.log('🔧 Première utilisation - Calibration automatique...');
                const calibrated = await this.calibrateAuto(videoElement);
                if (!calibrated) {
                    if (onStop) onStop('❌ Calibration échouée. Placez-vous face à la caméra.');
                    return;
                }
                console.log('✅ Calibration terminée, démarrage du jeu...');
            }
            
            // Démarrer la vérification
            FaceDetectionService.startVerification(
                videoElement,
                (count) => {
                    if (onViolation) onViolation(count);
                    if (count >= 3) {
                        this.stop();
                        if (onStop) onStop('⚠️ Adulte détecté ! Exercice arrêté.');
                    }
                },
                () => {
                    if (onViolation) onViolation(0);
                }
            );
        });
    },

    stop() {
        FaceDetectionService.stopCamera();
    }
};

window.ExerciceGuardian = ExerciceGuardian;