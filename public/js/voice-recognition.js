
        // Attendre que la page soit chargée
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Page vocale chargée');
            
            const microphoneBtn = document.getElementById('microphoneBtn');
            const statusDetail = document.getElementById('statusDetail');
            const commandRecognized = document.getElementById('commandRecognized');
            const responseBox = document.getElementById('responseBox');
            const responseMessage = document.getElementById('responseMessage');
            const statusText = document.getElementById('statusText');
            const redirectBox = document.getElementById('redirectBox');
            const redirectMessage = document.getElementById('redirectMessage');
            const countdownSpan = document.getElementById('countdown');
            const cancelRedirectBtn = document.getElementById('cancelRedirect');
            
            let redirectTimer = null;
            let pendingRedirect = null;
            
            // Annuler la redirection
            cancelRedirectBtn.onclick = function() {
                if (redirectTimer) {
                    clearTimeout(redirectTimer);
                    redirectTimer = null;
                }
                pendingRedirect = null;
                redirectBox.style.display = 'none';
                statusDetail.innerHTML = '✅ Redirection annulée';
                statusText.innerHTML = 'Cliquez sur le microphone pour parler';
                setTimeout(() => {
                    if (statusDetail.innerHTML === '✅ Redirection annulée') {
                        statusDetail.innerHTML = 'Prêt';
                    }
                }, 2000);
            };
            
            // Fonction pour rediriger avec compte à rebours
            function redirectWithCountdown(url, activityName) {
                pendingRedirect = url;
                redirectMessage.innerHTML = `Redirection vers l'activité ${activityName} dans...`;
                redirectBox.style.display = 'block';
                
                let countdown = 3;
                countdownSpan.innerHTML = countdown;
                
                if (redirectTimer) clearTimeout(redirectTimer);
                
                redirectTimer = setInterval(() => {
                    countdown--;
                    countdownSpan.innerHTML = countdown;
                    
                    if (countdown <= 0) {
                        clearInterval(redirectTimer);
                        redirectTimer = null;
                        window.location.href = url;
                    }
                }, 1000);
            }
            
            // Vérifier le support
            const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
            
            if (!SpeechRecognition) {
                statusDetail.innerHTML = '❌ Navigation non supportée';
                statusText.innerHTML = 'Utilisez Chrome, Edge ou Safari';
                microphoneBtn.style.opacity = '0.5';
                microphoneBtn.style.cursor = 'not-allowed';
                return;
            }
            
            let recognition = null;
            let isListening = false;
            
            // Fonction pour afficher la réponse
            function showResponse(message, isError = false) {
                responseMessage.innerHTML = message;
                responseBox.style.display = 'block';
                if (isError) {
                    responseBox.style.background = '#f8d7da';
                    responseBox.style.color = '#721c24';
                } else {
                    responseBox.style.background = '#d4edda';
                    responseBox.style.color = '#155724';
                }
                
                // Synthèse vocale
                if (!isError && 'speechSynthesis' in window) {
                    try {
                        const utterance = new SpeechSynthesisUtterance(message);
                        utterance.lang = 'fr-FR';
                        utterance.rate = 0.9;
                        window.speechSynthesis.cancel();
                        window.speechSynthesis.speak(utterance);
                    } catch(e) {
                        console.error('Erreur synthèse:', e);
                    }
                }
                
                // Masquer après 5 secondes
                setTimeout(() => {
                    if (responseBox.style.display === 'block') {
                        responseBox.style.display = 'none';
                    }
                }, 5000);
            }
            
            // Démarrer la reconnaissance
            function startListening() {
                navigator.mediaDevices.getUserMedia({ audio: true })
                    .then(function(stream) {
                        stream.getTracks().forEach(track => track.stop());
                        
                        recognition = new SpeechRecognition();
                        recognition.lang = 'fr-FR';
                        recognition.continuous = false;
                        recognition.interimResults = false;
                        
                        recognition.onstart = function() {
                            isListening = true;
                            microphoneBtn.classList.add('listening');
                            statusDetail.innerHTML = '🎤 Écoute en cours...';
                            statusText.innerHTML = 'Parlez maintenant !';
                            commandRecognized.innerHTML = '...';
                        };
                        
                        recognition.onresult = function(event) {
                            const commande = event.results[0][0].transcript;
                            const confidence = event.results[0][0].confidence;
                            console.log('Commande:', commande);
                            
                            commandRecognized.innerHTML = `"${commande}" (${Math.round(confidence * 100)}%)`;
                            statusDetail.innerHTML = '✅ Commande reçue, traitement...';
                            
                            // Envoyer au backend
                            fetch('/api/process-voice-command', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                },
                                body: JSON.stringify({ commande: commande })
                            })
                            .then(response => response.json())
                            .then(data => {
                                console.log('Réponse:', data);
                                if (data.success) {
                                    showResponse(data.message);
                                    
                                    // Si redirection demandée
                                    if (data.redirect) {
                                        let activityName = '';
                                        switch(data.action) {
                                            case 'coloriage': activityName = 'coloriage'; break;
                                            case 'histoire': activityName = 'histoire'; break;
                                            case 'respiration': activityName = 'respiration'; break;
                                            case 'musique': activityName = 'musique'; break;
                                        }
                                        redirectWithCountdown(data.redirect, activityName);
                                        statusDetail.innerHTML = `🔄 Redirection vers ${activityName}...`;
                                    } else {
                                        statusDetail.innerHTML = '✅ Commande traitée';
                                    }
                                } else {
                                    showResponse(data.message, true);
                                    statusDetail.innerHTML = '❌ Commande non reconnue';
                                }
                            })
                            .catch(error => {
                                console.error('Erreur:', error);
                                showResponse('Erreur de communication avec le serveur', true);
                                statusDetail.innerHTML = '❌ Erreur serveur';
                            });
                        };
                        
                        recognition.onerror = function(event) {
                            console.error('Erreur:', event.error);
                            let msg = '';
                            switch(event.error) {
                                case 'no-speech': msg = 'Aucune parole détectée'; break;
                                case 'audio-capture': msg = 'Microphone non trouvé'; break;
                                case 'not-allowed': msg = 'Permission microphone refusée'; break;
                                default: msg = event.error;
                            }
                            statusDetail.innerHTML = `❌ ${msg}`;
                            statusText.innerHTML = 'Cliquez pour réessayer';
                            stopListening();
                        };
                        
                        recognition.onend = function() {
                            stopListening();
                        };
                        
                        recognition.start();
                    })
                    .catch(function(err) {
                        console.error('Erreur microphone:', err);
                        let msg = 'Impossible d\'accéder au microphone. ';
                        if (err.name === 'NotAllowedError') {
                            msg += 'Autorisez l\'accès dans les paramètres.';
                        } else if (err.name === 'NotFoundError') {
                            msg += 'Aucun microphone détecté.';
                        }
                        statusDetail.innerHTML = `❌ ${msg}`;
                        statusText.innerHTML = msg;
                    });
            }
            
            function stopListening() {
                if (recognition) {
                    try {
                        recognition.stop();
                    } catch(e) {}
                    recognition = null;
                }
                isListening = false;
                microphoneBtn.classList.remove('listening');
                if (statusDetail.innerHTML !== '✅ Commande traitée' && statusDetail.innerHTML !== '🔄 Redirection en cours...') {
                    statusDetail.innerHTML = 'Prêt';
                    statusText.innerHTML = 'Cliquez sur le microphone pour parler';
                }
            }
            
            microphoneBtn.onclick = function() {
                if (isListening) {
                    stopListening();
                } else {
                    startListening();
                }
            };
            
            // Test backend
            fetch('/api/process-voice-command', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ commande: 'ping' })
            })
            .then(() => {
                console.log('✅ Backend OK');
                statusDetail.innerHTML = '✅ Prêt';
            })
            .catch(error => {
                console.error('⚠️ Backend indisponible');
                statusDetail.innerHTML = '⚠️ Backend inaccessible';
            });
        });
   