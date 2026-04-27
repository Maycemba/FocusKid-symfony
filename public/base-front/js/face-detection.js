// public/base-front/js/face-detection.js

const FaceDetectionService = {
    videoStream: null,
    checkInterval: null,
    alertCount: 0,
    onViolation: null,
    onConfirmed: null,
    enfantReferenceSize: null,
    isCalibrated: false,
    
    ADULT_THRESHOLD: 1.15,
    CHILD_THRESHOLD: 0.85,

    async init() {
        console.log('✅ Service prêt');
        const saved = localStorage.getItem('enfant_face_size');
        if (saved) {
            this.enfantReferenceSize = parseFloat(saved);
            this.isCalibrated = true;
            console.log('✅ Calibration chargée:', this.enfantReferenceSize);
        }
        return true;
    },

    async calibrate(videoElement) {
        console.log('📸 Début calibration...');
        
        // Prendre plusieurs mesures
        let sizes = [];
        for (let i = 0; i < 5; i++) {
            const size = await this.getFaceSize(videoElement);
            if (size > 5000) {
                sizes.push(size);
            }
            await new Promise(r => setTimeout(r, 500));
        }
        
        if (sizes.length > 0) {
            const avgSize = sizes.reduce((a, b) => a + b, 0) / sizes.length;
            this.enfantReferenceSize = avgSize;
            this.isCalibrated = true;
            localStorage.setItem('enfant_face_size', avgSize);
            console.log('✅ Calibration réussie, taille:', avgSize);
            return true;
        }
        console.log('❌ Calibration échouée');
        return false;
    },

    async getFaceSize(videoElement) {
        if (!videoElement || videoElement.videoWidth === 0) return 0;
        
        return new Promise((resolve) => {
            const canvas = document.createElement('canvas');
            const ctx = canvas.getContext('2d', { willReadFrequently: true });
            
            canvas.width = videoElement.videoWidth;
            canvas.height = videoElement.videoHeight;
            ctx.drawImage(videoElement, 0, 0, canvas.width, canvas.height);
            
            const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
            const skinPixels = [];
            const data = imageData.data;
            
            for (let y = 0; y < canvas.height; y += 4) {
                for (let x = 0; x < canvas.width; x += 4) {
                    const idx = (y * canvas.width + x) * 4;
                    const r = data[idx];
                    const g = data[idx + 1];
                    const b = data[idx + 2];
                    
                    if (r > 60 && g > 40 && b > 20 && r > g && r > b && (r - g) > 15) {
                        skinPixels.push({ x: x, y: y });
                    }
                }
            }
            
            if (skinPixels.length > 20) {
                let minX = canvas.width, maxX = 0, minY = canvas.height, maxY = 0;
                for (const pixel of skinPixels) {
                    minX = Math.min(minX, pixel.x);
                    maxX = Math.max(maxX, pixel.x);
                    minY = Math.min(minY, pixel.y);
                    maxY = Math.max(maxY, pixel.y);
                }
                const width = maxX - minX;
                const height = maxY - minY;
                resolve(width * height);
            } else {
                resolve(0);
            }
        });
    },

    async startCamera(videoElement) {
        try {
            this.videoStream = await navigator.mediaDevices.getUserMedia({
                video: { width: 640, height: 480 }
            });
            videoElement.srcObject = this.videoStream;
            await videoElement.play();
            console.log('✅ Caméra active');
            return true;
        } catch (error) {
            console.error('❌ Erreur caméra:', error);
            return false;
        }
    },

    stopCamera() {
        if (this.videoStream) {
            this.videoStream.getTracks().forEach(t => t.stop());
            this.videoStream = null;
        }
        if (this.checkInterval) {
            clearInterval(this.checkInterval);
            this.checkInterval = null;
        }
    },

    startVerification(videoElement, onAdult, onChild) {
    this.alertCount = 0;

    this.checkInterval = setInterval(async () => {
        if (!this.isCalibrated || !this.enfantReferenceSize) return;

        const currentSize = await this.getFaceSize(videoElement);
        if (currentSize === 0) return;

        const ratio = currentSize / this.enfantReferenceSize;
        console.log('Ratio:', ratio.toFixed(2));

        // adulte = proche de la calibration
        if (ratio >= 0.90) {
            this.alertCount++;

            if (onAdult) {
                onAdult(this.alertCount);
            }
        }

        // enfant = beaucoup plus petit
        else if (ratio < 0.75) {
            this.alertCount = 0;

            if (onChild) {
                onChild();
            }
        }

        // zone grise = on ne décide pas
        else {
            console.log("⚠️ Zone incertaine");
        }

        if (this.alertCount >= 3) {
            clearInterval(this.checkInterval);
        }
    }, 2000);
}
};

window.FaceDetectionService = FaceDetectionService;