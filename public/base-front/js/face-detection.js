// public/base-front/js/face-detection.js

const FaceDetectionService = {
    videoStream: null,
    checkInterval: null,
    adultAlertCount: 0,
    isModelLoaded: false,
    isCalibrated: false,
    ageThreshold: 12,
    
    async init() {
        console.log('🚀 Initialisation...');
        
        let waitCount = 0;
        while (typeof faceapi === 'undefined' && waitCount < 50) {
            await new Promise(r => setTimeout(r, 100));
            waitCount++;
        }
        
        if (typeof faceapi === 'undefined') {
            console.error('❌ faceapi.js non chargé');
            return false;
        }
        
        console.log('✅ faceapi.js chargé');
        
        // Chargement exactement comme le test
        const statusSpan = document.getElementById('faceStatus');
        if (statusSpan) statusSpan.innerHTML = '📥 Chargement...';
        
        // Utiliser le CDN (comme le test qui fonctionne)
        const modelUrl = 'https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/weights/';
        
        try {
            await faceapi.nets.tinyFaceDetector.loadFromUri(modelUrl);
            console.log('✅ tinyFaceDetector chargé');
            
            await faceapi.nets.ageGenderNet.loadFromUri(modelUrl);
            console.log('✅ ageGenderNet chargé');
            
            this.isModelLoaded = true;
            console.log('✅ MODÈLES CHARGÉS!');
            
            if (statusSpan) {
                statusSpan.innerHTML = '✅ Prêt';
                statusSpan.style.background = '#00b894';
            }
            
            // Vérifier calibration existante
            const savedAge = localStorage.getItem('enfant_age');
            if (savedAge) {
                this.isCalibrated = true;
                console.log(`✅ Calibration: ${savedAge} ans`);
            }
            
            return true;
        } catch (error) {
            console.error('❌ Erreur:', error);
            if (statusSpan) {
                statusSpan.innerHTML = '❌ Erreur chargement';
                statusSpan.style.background = '#f44336';
            }
            return false;
        }
    },
    
    async detectFace(videoElement) {
        if (!videoElement || videoElement.videoWidth === 0) {
            return null;
        }
        
        if (!this.isModelLoaded) {
            return null;
        }
        
        try {
            const detection = await faceapi
                .detectSingleFace(videoElement, new faceapi.TinyFaceDetectorOptions())
                .withAgeAndGender();
            
            if (detection) {
                console.log(`✅ Âge: ${Math.round(detection.age)} ans`);
            }
            
            return detection;
        } catch (error) {
            console.error('Erreur détection:', error);
            return null;
        }
    },
    
    async calibrate(videoElement) {
        console.log('📸 Calibration...');
        
        const statusSpan = document.getElementById('faceStatus');
        const messageDiv = document.getElementById('message');
        
        // Attendre que la vidéo soit prête
        let waitVideo = 0;
        while ((videoElement.videoWidth === 0) && waitVideo < 20) {
            await new Promise(r => setTimeout(r, 500));
            waitVideo++;
        }
        
        console.log(`Vidéo: ${videoElement.videoWidth}x${videoElement.videoHeight}`);
        
        if (videoElement.videoWidth === 0) {
            if (messageDiv) {
                messageDiv.innerHTML = '❌ Caméra non disponible';
                messageDiv.className = 'message-error';
            }
            return false;
        }
        
        if (messageDiv) {
            messageDiv.innerHTML = '🔍 Placez l\'enfant face à la caméra...';
            messageDiv.className = 'message-info';
        }
        
        let attempts = 0;
        const maxAttempts = 30;
        
        while (attempts < maxAttempts) {
            if (statusSpan) {
                statusSpan.innerHTML = `🔍 Scan: ${attempts + 1}/${maxAttempts}`;
            }
            
            const detection = await this.detectFace(videoElement);
            
            if (detection && detection.age < this.ageThreshold) {
                this.isCalibrated = true;
                localStorage.setItem('enfant_age', detection.age);
                
                console.log(`✅ Calibré! Âge: ${Math.round(detection.age)} ans`);
                
                if (statusSpan) {
                    statusSpan.innerHTML = `✅ Calibré (${Math.round(detection.age)} ans)`;
                    statusSpan.style.background = '#00b894';
                }
                
                if (messageDiv) {
                    messageDiv.innerHTML = `✅ Calibration réussie! (${Math.round(detection.age)} ans)`;
                    messageDiv.className = 'message-success';
                }
                
                return true;
            }
            
            attempts++;
            await new Promise(r => setTimeout(r, 1000));
        }
        
        console.log('❌ Calibration échouée');
        return false;
    },
    
    async verifyChild(videoElement) {
        if (!this.isCalibrated) {
            return { isChild: false, age: null };
        }
        
        const detection = await this.detectFace(videoElement);
        
        if (!detection) {
            return { isChild: false, age: null };
        }
        
        return {
            isChild: detection.age < this.ageThreshold,
            age: Math.round(detection.age)
        };
    },
    
    async startCamera(videoElement) {
        console.log('🎥 Démarrage caméra...');
        
        try {
            if (this.videoStream) {
                this.stopCamera();
            }
            
            const stream = await navigator.mediaDevices.getUserMedia({ video: true });
            
            videoElement.srcObject = stream;
            this.videoStream = stream;
            
            await new Promise((resolve) => {
                videoElement.onloadedmetadata = () => {
                    videoElement.play();
                    resolve();
                };
                setTimeout(resolve, 2000);
            });
            
            console.log(`✅ Caméra active - ${videoElement.videoWidth}x${videoElement.videoHeight}`);
            return true;
            
        } catch (error) {
            console.error('❌ Erreur caméra:', error);
            return false;
        }
    },
    
    stopCamera() {
        if (this.videoStream) {
            this.videoStream.getTracks().forEach(track => track.stop());
            this.videoStream = null;
        }
        if (this.checkInterval) {
            clearInterval(this.checkInterval);
            this.checkInterval = null;
        }
    },
    
    startVerification(videoElement, onAdult, onChild) {
        this.adultAlertCount = 0;
        
        if (this.checkInterval) clearInterval(this.checkInterval);
        
        this.checkInterval = setInterval(async () => {
            const result = await this.verifyChild(videoElement);
            
            const statusSpan = document.getElementById('faceStatus');
            
            if (result.isChild) {
                this.adultAlertCount = 0;
                if (statusSpan) {
                    statusSpan.innerHTML = `✅ Enfant: ${result.age} ans`;
                    statusSpan.style.background = '#00b894';
                }
                if (onChild) onChild(result);
            } else if (result.age !== null) {
                this.adultAlertCount++;
                if (statusSpan) {
                    statusSpan.innerHTML = `⚠️ ADULTE: ${result.age} ans (${this.adultAlertCount}/3)`;
                    statusSpan.style.background = '#d63031';
                }
                if (onAdult) onAdult(this.adultAlertCount, result);
            }
        }, 2000);
    }
};

window.FaceDetectionService = FaceDetectionService;