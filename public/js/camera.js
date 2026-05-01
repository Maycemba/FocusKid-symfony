/**
 * public/js/camera.js
 * Gestion webcam + capture frame + envoi AJAX + affichage résultats
 */

(function () {
  "use strict";

  // ───────────────────────────────────────────
  // CONFIG
  // ───────────────────────────────────────────
  const ANALYZE_URL    = "/stress/analyze";
  const STATUS_URL     = "/stress/api-status";
  const HISTORY_URL    = "/stress/history";
  const CLEAR_URL      = "/stress/history/clear";
  const CAPTURE_FPS    = 2;       // captures par seconde
  const STATUS_POLL_MS = 5000;    // vérification statut API

  const EMOTION_ICONS = {
    happy: "😄", sad: "😢", angry: "😠",
    neutral: "😐", fear: "😨", surprise: "😲",
  };
  const EMOTION_COLORS = {
    happy: "#00e5a0", sad: "#60a5fa", angry: "#f87171",
    neutral: "#94a3b8", fear: "#c084fc", surprise: "#fbbf24",
  };
  const STRESS_DESC = [
    [0,  25,  "😌 Très détendu — excellent état de concentration"],
    [25, 50,  "🙂 Légèrement actif — bon équilibre"],
    [50, 70,  "😐 Stress modéré — à surveiller"],
    [70, 85,  "😰 Stress élevé — pause recommandée"],
    [85, 101, "🔴 Stress critique — intervention nécessaire"],
  ];

  // ───────────────────────────────────────────
  // ÉLÉMENTS DOM
  // ───────────────────────────────────────────
  const video         = document.getElementById("video");
  const hiddenCanvas  = document.getElementById("hidden-canvas");
  const overlayCanvas = document.getElementById("overlay-canvas");
  const ctx           = overlayCanvas.getContext("2d");
  const btnStart      = document.getElementById("btn-start");
  const btnStop       = document.getElementById("btn-stop");
  const btnClear      = document.getElementById("btn-clear");
  const apiDot        = document.getElementById("api-dot");
  const apiStatusText = document.getElementById("api-status-text");
  const offlineWarn   = document.getElementById("offline-warning");
  const fpsCounter    = document.getElementById("fps-counter");
  const emotionIcon   = document.getElementById("emotion-icon");
  const emotionLabel  = document.getElementById("emotion-label");
  const emotionConf   = document.getElementById("emotion-conf");
  const stressValue   = document.getElementById("stress-value");
  const stressBar     = document.getElementById("stress-bar");
  const stressDesc    = document.getElementById("stress-desc");
  const probsSection  = document.getElementById("probs-section");
  const historyList   = document.getElementById("history-list");
  const lastUpdate    = document.getElementById("last-update");

  // ───────────────────────────────────────────
  // ÉTAT
  // ───────────────────────────────────────────
  let stream      = null;
  let captureLoop = null;
  let pending     = false;
  let frameCount  = 0;
  let fpsTimer    = Date.now();
  let apiOnline   = false;

  // ───────────────────────────────────────────
  // WEBCAM
  // ───────────────────────────────────────────
  btnStart.addEventListener("click", startCamera);
  btnStop.addEventListener("click",  stopCamera);

  async function startCamera() {
    try {
      stream = await navigator.mediaDevices.getUserMedia({
        video: { width: { ideal: 640 }, height: { ideal: 480 }, facingMode: "user" },
        audio: false,
      });
      video.srcObject = stream;
      await video.play();

      // Synchroniser la taille du canvas overlay
      video.addEventListener("loadedmetadata", syncCanvasSize);
      syncCanvasSize();

      btnStart.disabled = true;
      btnStop.disabled  = false;

      captureLoop = setInterval(captureAndSend, 1000 / CAPTURE_FPS);
    } catch (err) {
      alert("Impossible d'accéder à la webcam : " + err.message);
      console.error(err);
    }
  }

  function stopCamera() {
    clearInterval(captureLoop);
    captureLoop = null;
    if (stream) {
      stream.getTracks().forEach((t) => t.stop());
      stream = null;
    }
    video.srcObject = null;
    btnStart.disabled = false;
    btnStop.disabled  = true;
    ctx.clearRect(0, 0, overlayCanvas.width, overlayCanvas.height);
  }

  function syncCanvasSize() {
    overlayCanvas.width  = video.videoWidth  || 640;
    overlayCanvas.height = video.videoHeight || 480;
    hiddenCanvas.width   = video.videoWidth  || 640;
    hiddenCanvas.height  = video.videoHeight || 480;
  }

  // ───────────────────────────────────────────
  // CAPTURE + ENVOI
  // ───────────────────────────────────────────
  async function captureAndSend() {
    if (pending || !video.videoWidth) return;

    // FPS counter
    frameCount++;
    const now = Date.now();
    if (now - fpsTimer >= 1000) {
      fpsCounter.textContent = frameCount + " fps";
      frameCount = 0;
      fpsTimer   = now;
    }

    // Dessiner frame dans canvas caché
    const hctx = hiddenCanvas.getContext("2d");
    hctx.save();
    hctx.scale(-1, 1);  // annuler le miroir CSS avant envoi
    hctx.drawImage(video, -hiddenCanvas.width, 0, hiddenCanvas.width, hiddenCanvas.height);
    hctx.restore();

    const dataUrl = hiddenCanvas.toDataURL("image/jpeg", 0.85);

    pending = true;
    try {
      const resp = await fetch(ANALYZE_URL, {
        method:  "POST",
        headers: { "Content-Type": "application/json" },
        body:    JSON.stringify({ frame: dataUrl }),
      });
      if (!resp.ok) throw new Error(`HTTP ${resp.status}`);
      const data = await resp.json();
      if (!data.error) {
        updateUI(data);
        addHistoryEntry(data);
      }
    } catch (err) {
      console.warn("Erreur analyse:", err.message);
    } finally {
      pending = false;
    }
  }

  // ───────────────────────────────────────────
  // MISE À JOUR UI
  // ───────────────────────────────────────────
  function updateUI(data) {
    const emotion = data.emotion   || "neutral";
    const stress  = data.stress    ?? 0;
    const probs   = data.probabilities || {};
    const topConf = probs[emotion] ? (probs[emotion] * 100).toFixed(1) + "%" : "–";
    const color   = EMOTION_COLORS[emotion] || "#94a3b8";

    // Émotion
    emotionIcon.textContent      = EMOTION_ICONS[emotion] || "😐";
    emotionLabel.textContent     = emotion;
    emotionLabel.style.color     = color;
    emotionConf.textContent      = "Confiance : " + topConf;

    // Stress gauge
    const pct = Math.min(stress, 100);
    stressValue.textContent      = pct + " / 100";
    stressValue.style.color      = pct > 70 ? "#f87171" : pct > 40 ? "#fbbf24" : "#00e5a0";
    stressBar.style.width        = pct + "%";
    stressBar.style.background   = pct > 70 ? "#f87171" : pct > 40 ? "#fbbf24" : "#00e5a0";

    const desc = STRESS_DESC.find(([lo, hi]) => pct >= lo && pct < hi);
    stressDesc.textContent = desc ? desc[2] : "";

    // Barres de probabilités
    probsSection.innerHTML = Object.entries(probs)
      .sort((a, b) => b[1] - a[1])
      .map(([em, p]) => {
        const c = EMOTION_COLORS[em] || "#94a3b8";
        const pctBar = (p * 100).toFixed(1);
        return `
          <div class="prob-row">
            <span class="prob-name">${em}</span>
            <div class="prob-track">
              <div class="prob-fill" style="width:${pctBar}%;background:${c}"></div>
            </div>
            <span class="prob-pct">${pctBar}%</span>
          </div>`;
      }).join("");

    // Timestamp
    lastUpdate.textContent = new Date().toLocaleTimeString();
  }

  // ───────────────────────────────────────────
  // HISTORIQUE
  // ───────────────────────────────────────────
  function addHistoryEntry(data) {
    const items = historyList.querySelectorAll(".history-item");
    const empty  = historyList.querySelector(".empty-history");
    if (empty) empty.remove();

    // Limiter à 20 entrées
    if (items.length >= 20) items[0].remove();

    const color = EMOTION_COLORS[data.emotion] || "#94a3b8";
    const el    = document.createElement("div");
    el.className = "history-item";
    el.innerHTML = `
      <span class="history-time">${new Date().toLocaleTimeString()}</span>
      <span class="history-emotion" style="color:${color}">${data.emotion}</span>
      <span class="history-stress" style="color:${color}">${data.stress}/100</span>`;
    historyList.appendChild(el);
    historyList.scrollTop = historyList.scrollHeight;
  }

  btnClear.addEventListener("click", async () => {
    await fetch(CLEAR_URL, { method: "POST" });
    historyList.innerHTML = '<div class="empty-history">Aucune mesure encore</div>';
  });

  // ───────────────────────────────────────────
  // STATUT API
  // ───────────────────────────────────────────
  async function checkApiStatus() {
    try {
      const resp = await fetch(STATUS_URL);
      const data = await resp.json();
      apiOnline = data.online === true;
    } catch {
      apiOnline = false;
    }
    apiDot.classList.toggle("online", apiOnline);
    apiStatusText.textContent = apiOnline ? "API connectée" : "API hors ligne";
    offlineWarn.style.display = apiOnline ? "none" : "block";
  }

  checkApiStatus();
  setInterval(checkApiStatus, STATUS_POLL_MS);

})();
