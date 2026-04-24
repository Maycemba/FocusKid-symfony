// public/base-front/js/exercice-guardian.js

const ExerciceGuardian = {
    isActive: false,
    violationCount: 0,
    maxViolations: 3,
    onStopCallback: null,
    onViolationCallback: null,

    async init() {
        console.log('🔧 Initialisation du guardian...');
        const success = await FaceDetectionService.init();
        const faceLoaded = FaceDetectionService.loadSavedFace();
        console.log('Face chargée:', faceLoaded);
        return { success, faceLoaded };
    },

    async registerFirstFace(videoElement) {
        console.log('📸 Tentative d\'enregistrement...');
        // Attendre que la caméra soit prête
        await new Promise(resolve => setTimeout(resolve, 1000));
        return await FaceDetectionService.registerFace(videoElement);
    },

    start(videoElement, onStop, onViolation) {
        if (!FaceDetectionService.loadSavedFace()) {
            console.warn('Aucun visage enregistré');
            return false;
        }
        
        console.log('🚀 Démarrage surveillance');
        this.isActive = true;
        this.violationCount = 0;
        this.onStopCallback = onStop;
        this.onViolationCallback = onViolation;
        
        FaceDetectionService.startCamera(videoElement);
        
        FaceDetectionService.startVerification(
            videoElement,
            (count) => {
                console.log('Violation count:', count);
                this.violationCount = count;
                if (onViolation) onViolation(count);
                
                if (count >= this.maxViolations) {
                    this.stop('⚠️ Personne non autorisée détectée !');
                }
            },
            () => {
                this.violationCount = 0;
                if (onViolation) onViolation(0);
            }
        );
        
        return true;
    },

    stop(reason) {
        if (!this.isActive) return;
        
        console.log('🛑 Arrêt surveillance:', reason);
        this.isActive = false;
        FaceDetectionService.stopVerification();
        
        if (this.onStopCallback) {
            this.onStopCallback(reason);
        }
    }
};

window.ExerciceGuardian = ExerciceGuardian;