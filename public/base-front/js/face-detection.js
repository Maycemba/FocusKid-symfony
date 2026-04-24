// public/base-front/js/face-detection.js

const FaceDetectionService = {
    isInitialized: false,
    enfantFaceDescriptor: null,
    detectionActive: false,
    videoElement: null,
    stream: null,
    detectionInterval: null,
    alertCount: 0,
    violationCallback: null,
    confirmedCallback: null,

    async init() {
        try {
            // Utiliser unpkg CDN (plus fiable)
            const MODEL_URL = 'https://unpkg.com/@vladmandic/face-api@1.7.12/model/';
            
            console.log('📦 Chargement des modèles...');
            
            await faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URL);
            await faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL);
            await faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL);
            
            this.isInitialized = true;
            console.log('✅ Modèles chargés avec succès');
            return true;
        } catch (error) {
            console.error('❌ Erreur chargement:', error);
            return false;
        }
    },

    async registerFace(videoElement) {
        if (!this.isInitialized) {
            console.log('❌ Service non initialisé');
            return false;
        }
        
        if (!videoElement || !videoElement.videoWidth) {
            console.log('❌ Caméra non prête');
            return false;
        }
        
        console.log('🔍 Recherche visage...');
        
        const detection = await faceapi.detectSingleFace(
            videoElement,
            new faceapi.TinyFaceDetectorOptions()
        ).withFaceLandmarks().withFaceDescriptor();
        
        if (detection && detection.descriptor) {
            console.log('✅ Visage détecté et enregistré');
            this.enfantFaceDescriptor = detection.descriptor;
            localStorage.setItem('enfant_face', JSON.stringify(Array.from(detection.descriptor)));
            return true;
        } else {
            console.log('❌ Aucun visage détecté');
            return false;
        }
    },

    loadSavedFace() {
        const saved = localStorage.getItem('enfant_face');
        if (saved) {
            this.enfantFaceDescriptor = new Float32Array(JSON.parse(saved));
            console.log('✅ Visage chargé depuis localStorage');
            return true;
        }
        console.log('❌ Aucun visage sauvegardé');
        return false;
    },

    async verifyFace(videoElement) {
        if (!this.isInitialized || !this.enfantFaceDescriptor) return false;
        
        const detection = await faceapi.detectSingleFace(
            videoElement,
            new faceapi.TinyFaceDetectorOptions()
        ).withFaceLandmarks().withFaceDescriptor();
        
        if (!detection || !detection.descriptor) return false;
        
        const distance = faceapi.euclideanDistance(this.enfantFaceDescriptor, detection.descriptor);
        console.log('Distance:', distance);
        return distance < 0.6;
    },

    startVerification(videoElement, onViolation, onConfirmed) {
        this.detectionActive = true;
        this.violationCallback = onViolation;
        this.confirmedCallback = onConfirmed;
        this.alertCount = 0;
        
        this.detectionInterval = setInterval(async () => {
            if (!this.detectionActive) return;
            
            try {
                const isEnfant = await this.verifyFace(videoElement);
                
                if (!isEnfant) {
                    this.alertCount++;
                    if (this.violationCallback) {
                        this.violationCallback(this.alertCount);
                    }
                } else {
                    this.alertCount = 0;
                    if (this.confirmedCallback) {
                        this.confirmedCallback();
                    }
                }
            } catch (error) {
                console.error('Erreur vérification:', error);
            }
        }, 2000);
    },

    stopVerification() {
        this.detectionActive = false;
        if (this.detectionInterval) {
            clearInterval(this.detectionInterval);
            this.detectionInterval = null;
        }
        this.stopCamera();
    },

    async startCamera(videoElement) {
        try {
            this.stream = await navigator.mediaDevices.getUserMedia({
                video: { width: { ideal: 640 }, height: { ideal: 480 } }
            });
            videoElement.srcObject = this.stream;
            await videoElement.play();
            console.log('✅ Caméra démarrée');
            return true;
        } catch (error) {
            console.error('❌ Erreur caméra:', error);
            alert('Impossible d\'accéder à la caméra. Vérifiez les permissions.');
            return false;
        }
    },

    stopCamera() {
        if (this.stream) {
            this.stream.getTracks().forEach(track => track.stop());
            this.stream = null;
        }
    }
};

window.FaceDetectionService = FaceDetectionService;