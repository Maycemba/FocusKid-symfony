(function ($) {
    "use strict";

    // Initiate the wowjs
    new WOW().init();

    // Spinner
    var spinner = function () {
        setTimeout(function () {
            if ($('#spinner').length > 0) {
                $('#spinner').removeClass('show');
            }
        }, 1);
    };
    spinner();

    // Sticky Navbar
    $(window).scroll(function () {
        if ($(this).scrollTop() > 300) {
            $('.sticky-top').addClass('shadow-sm').css('top', '0px');
        } else {
            $('.sticky-top').removeClass('shadow-sm').css('top', '-100px');
        }
    });
    
    // Back to top button
    $(window).scroll(function () {
        if ($(this).scrollTop() > 300) {
            $('.back-to-top').fadeIn('slow');
        } else {
            $('.back-to-top').fadeOut('slow');
        }
    });
    $('.back-to-top').click(function () {
        $('html, body').animate({scrollTop: 0}, 1500, 'easeInOutExpo');
        return false;
    });

    // Header carousel
    $(".header-carousel").owlCarousel({
        autoplay: true,
        smartSpeed: 1500,
        items: 1,
        dots: true,
        loop: true,
        nav : true,
        navText : [
            '<i class="bi bi-chevron-left"></i>',
            '<i class="bi bi-chevron-right"></i>'
        ]
    });

    // Testimonials carousel
    $(".testimonial-carousel").owlCarousel({
        autoplay: true,
        smartSpeed: 1000,
        margin: 24,
        dots: false,
        loop: true,
        nav : true,
        navText : [
            '<i class="bi bi-arrow-left"></i>',
            '<i class="bi bi-arrow-right"></i>'
        ],
        responsive: {
            0:{
                items:1
            },
            992:{
                items:2
            }
        }
    });
    
})(jQuery);

// ========== SERVICES DE DÉTECTION POUR LES JEUX ==========
// (À mettre en dehors du jQuery pour être accessible globalement)

// Service de détection faciale
const faceDetectionService = {
    isInitialized: false,
    enfantFaceDescriptor: null,
    detectionActive: false,
    videoElement: null,
    stream: null,
    detectionInterval: null,
    alertCount: 0,
    onViolationCallback: null,
    onConfirmedCallback: null,

    async init() {
        try {
            await faceapi.nets.tinyFaceDetector.loadFromUri('/models/face-api');
            await faceapi.nets.faceLandmark68Net.loadFromUri('/models/face-api');
            await faceapi.nets.faceRecognitionNet.loadFromUri('/models/face-api');
            this.isInitialized = true;
            console.log('✅ Face detection initialized');
            return true;
        } catch (error) {
            console.error('❌ Face detection error:', error);
            return false;
        }
    },

    async registerEnfantFace(videoElement) {
        if (!this.isInitialized) return false;
        
        const detection = await faceapi.detectSingleFace(
            videoElement,
            new faceapi.TinyFaceDetectorOptions()
        ).withFaceLandmarks().withFaceDescriptor();
        
        if (detection && detection.descriptor) {
            this.enfantFaceDescriptor = detection.descriptor;
            localStorage.setItem('enfant_face_descriptor', JSON.stringify(Array.from(detection.descriptor)));
            return true;
        }
        return false;
    },

    loadEnfantFace() {
        const saved = localStorage.getItem('enfant_face_descriptor');
        if (saved) {
            this.enfantFaceDescriptor = new Float32Array(JSON.parse(saved));
            return true;
        }
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
        return distance < 0.6;
    },

    async startVerification(videoElement, onViolation, onConfirmed) {
        this.detectionActive = true;
        this.onViolationCallback = onViolation;
        this.onConfirmedCallback = onConfirmed;
        this.alertCount = 0;
        
        this.detectionInterval = setInterval(async () => {
            if (!this.detectionActive) return;
            
            const isEnfant = await this.verifyFace(videoElement);
            
            if (!isEnfant) {
                this.alertCount++;
                if (this.onViolationCallback) {
                    this.onViolationCallback(this.alertCount);
                }
            } else {
                this.alertCount = 0;
                if (this.onConfirmedCallback) {
                    this.onConfirmedCallback();
                }
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
                video: { width: { ideal: 640 }, height: { ideal: 480 } },
                audio: false
            });
            videoElement.srcObject = this.stream;
            await videoElement.play();
            return true;
        } catch (error) {
            console.error('Camera error:', error);
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

// Service de détection vocale
const voiceDetectionService = {
    audioContext: null,
    sourceNode: null,
    analyser: null,
    isListening: false,
    detectionInterval: null,
    adultVoiceCount: 0,
    onAdultVoiceCallback: null,

    async init() {
        try {
            const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
            
            this.audioContext = new (window.AudioContext || window.webkitAudioContext)();
            this.sourceNode = this.audioContext.createMediaStreamSource(stream);
            this.analyser = this.audioContext.createAnalyser();
            this.analyser.fftSize = 2048;
            
            this.sourceNode.connect(this.analyser);
            this.frequencyData = new Uint8Array(this.analyser.frequencyBinCount);
            
            console.log('✅ Voice detection initialized');
            return true;
        } catch (error) {
            console.error('❌ Voice detection error:', error);
            return false;
        }
    },

    startDetection(onAdultVoiceDetected) {
        this.isListening = true;
        this.onAdultVoiceCallback = onAdultVoiceDetected;
        this.adultVoiceCount = 0;
        
        if (this.audioContext && this.audioContext.state === 'suspended') {
            this.audioContext.resume();
        }
        
        this.detectionInterval = setInterval(() => {
            if (!this.isListening) return;
            
            this.analyser.getByteFrequencyData(this.frequencyData);
            
            let sum = 0;
            for (let i = 0; i < this.frequencyData.length; i++) {
                sum += this.frequencyData[i];
            }
            const average = sum / this.frequencyData.length;
            const intensity = average / 255;
            
            const lowFreqSum = this.frequencyData.slice(0, 20).reduce((a, b) => a + b, 0);
            const lowFreqIntensity = lowFreqSum / (20 * 255);
            
            const highFreqSum = this.frequencyData.slice(20, 80).reduce((a, b) => a + b, 0);
            const highFreqIntensity = highFreqSum / (60 * 255);
            
            if (lowFreqIntensity > 0.12 && intensity > 0.15 && lowFreqIntensity > highFreqIntensity * 1.5) {
                this.adultVoiceCount++;
                if (this.onAdultVoiceCallback) {
                    this.onAdultVoiceCallback(this.adultVoiceCount);
                }
            }
        }, 1000);
    },

    stopDetection() {
        this.isListening = false;
        if (this.detectionInterval) {
            clearInterval(this.detectionInterval);
            this.detectionInterval = null;
        }
        if (this.audioContext) {
            this.audioContext.close();
        }
    }
};

// Export global
window.faceDetectionService = faceDetectionService;
window.voiceDetectionService = voiceDetectionService;