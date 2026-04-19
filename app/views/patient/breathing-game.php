<?php $title = 'Jogo da respiração'; include __DIR__ . '/../partials/header.php'; include __DIR__ . '/../partials/nav.php'; ?>
<div class="container page-wrap breathing-game-shell">
  <?php include __DIR__ . '/../partials/flash-alert.php'; ?>

  <section class="card border-0 shadow-sm breathing-game-card">
    <div class="card-body p-3 p-md-4">
      <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3 breathing-game-header">
        <h4 class="mb-0">Jogo da respiração</h4>
        <span id="breathing-cycle-counter" class="badge text-bg-primary">Ciclo 0/3</span>
      </div>

      <div class="breathing-stage" role="application" aria-label="Jogo de respiração guiada">
        <div class="breathing-phone">
          <div class="breathing-screen breathing-screen--intro is-active" id="breathing-intro-screen">
            <div class="breathing-intro-copy">
              <p>
                A respiração é fundamental não apenas para manter a vida, mas também para regular o corpo e a mente.
                Respirar corretamnte ajuda a reduzir o estresse, melhorar o sono, fortalecer os pulmões e otimizar a
                produção de energia. Respire comigo!
              </p>
            </div>

            <button class="breathing-start-button" id="breathing-start-button" type="button">
              Iniciar
            </button>
          </div>

          <div class="breathing-screen breathing-screen--game" id="breathing-game-screen" aria-hidden="true">
            <div class="breathing-track" aria-hidden="true"></div>

            <div class="breathing-modules">
              <div class="breathing-module">
                <small>Fase</small>
                <strong id="breathing-phase-label">Preparar</strong>
              </div>
              <div class="breathing-module">
                <small>Tempo</small>
                <strong id="breathing-time-label">0s</strong>
              </div>
            </div>

            <div class="breathing-message" id="breathing-message">Inspire por 5 segundos</div>
            <div class="breathing-timer" id="breathing-timer" aria-live="polite">3</div>
            <div class="breathing-ball breathing-ball--bottom" id="breathing-ball" aria-hidden="true"></div>
          </div>
        </div>
      </div>
    </div>
  </section>
</div>

<style>
  .breathing-game-shell {
    height: calc(100dvh - 48px);
    padding-bottom: 10px;
  }

  .breathing-game-card {
    height: 100%;
  }

  .breathing-game-card .card-body {
    height: 100%;
    display: flex;
    flex-direction: column;
  }

  .breathing-stage {
    display: flex;
    flex: 1;
    min-height: 0;
    align-items: center;
    justify-content: center;
    padding: 0.5rem 0;
  }

  .breathing-phone {
    position: relative;
    width: min(88vw, 370px);
    height: min(78dvh, 740px);
    border-radius: 38px;
    border: 10px solid #0e2333;
    background: linear-gradient(180deg, #ffffff 0%, #5ea9ff 100%);
    box-shadow: 0 22px 48px rgba(15, 53, 84, 0.25);
    overflow: hidden;
  }

  .breathing-screen {
    position: absolute;
    inset: 0;
    opacity: 0;
    pointer-events: none;
    transition: opacity 0.5s ease;
  }

  .breathing-screen.is-active {
    opacity: 1;
    pointer-events: auto;
  }

  .breathing-screen.is-leaving {
    opacity: 0;
  }

  .breathing-screen--intro {
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    gap: 1.5rem;
    padding: 10% 9%;
    text-align: center;
  }

  .breathing-intro-copy {
    max-width: 28rem;
    padding: 1.4rem 1.2rem;
    border-radius: 24px;
    background: rgba(255, 255, 255, 0.42);
    backdrop-filter: blur(6px);
    box-shadow: 0 18px 40px rgba(18, 66, 103, 0.12);
  }

  .breathing-intro-copy p {
    margin: 0;
    color: #114163;
    font-size: clamp(1rem, 2.5vw, 1.1rem);
    font-weight: 600;
    line-height: 1.75;
  }

  .breathing-start-button {
    border: 1px solid rgba(44, 131, 212, 0.44);
    border-radius: 999px;
    background: rgba(74, 156, 231, 0.18);
    color: #0f4f7d;
    font-size: 1rem;
    font-weight: 700;
    padding: 0.9rem 2.6rem;
    box-shadow: 0 14px 30px rgba(18, 96, 154, 0.16);
    backdrop-filter: blur(4px);
    transition: transform 0.2s ease, background-color 0.2s ease, border-color 0.2s ease;
  }

  .breathing-start-button:hover,
  .breathing-start-button:focus-visible {
    transform: translateY(-1px);
    background: rgba(74, 156, 231, 0.3);
    border-color: rgba(15, 79, 125, 0.4);
    outline: none;
  }

  .breathing-screen--game {
    display: block;
  }

  .breathing-modules {
    position: absolute;
    inset: 5% 6% auto 6%;
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0.6rem;
    z-index: 3;
  }

  .breathing-module {
    border-radius: 14px;
    background: rgba(255, 255, 255, 0.52);
    backdrop-filter: blur(4px);
    padding: 0.5rem 0.65rem;
    box-shadow: 0 6px 14px rgba(16, 71, 112, 0.15);
    text-align: center;
  }

  .breathing-module small {
    display: block;
    font-size: 0.72rem;
    color: #2f5f84;
    text-transform: uppercase;
    letter-spacing: 0.03em;
    font-weight: 700;
  }

  .breathing-module strong {
    display: block;
    color: #0d3654;
    font-size: 0.98rem;
    line-height: 1.2;
  }

  .breathing-track {
    position: absolute;
    left: 50%;
    top: 13%;
    width: 2px;
    height: 74%;
    transform: translateX(-50%);
    background: linear-gradient(180deg, rgba(255, 255, 255, 0.75), rgba(255, 255, 255, 0.2));
  }

  .breathing-message {
    position: absolute;
    inset: 22% 8% auto 8%;
    text-align: center;
    color: #0c2f48;
    font-size: clamp(1rem, 2.5vw, 1.1rem);
    font-weight: 700;
    line-height: 1.4;
    opacity: 1;
    transition: opacity 0.35s ease;
    text-shadow: 0 1px 0 rgba(255, 255, 255, 0.75);
    z-index: 3;
  }

  .breathing-message.fade {
    opacity: 0;
  }

  .breathing-timer {
    position: absolute;
    left: 50%;
    top: 52%;
    transform: translate(-50%, -50%);
    width: min(28vw, 108px);
    height: min(28vw, 108px);
    border-radius: 999px;
    border: 4px solid rgba(255, 255, 255, 0.75);
    background: rgba(10, 60, 97, 0.12);
    color: #0d3554;
    font-size: clamp(1.7rem, 5vw, 2.2rem);
    font-weight: 800;
    display: grid;
    place-items: center;
    box-shadow: 0 10px 22px rgba(9, 53, 84, 0.18);
    z-index: 3;
  }

  .breathing-ball {
    position: absolute;
    left: 50%;
    width: min(18vw, 72px);
    height: min(18vw, 72px);
    border-radius: 999px;
    transform: translateX(-50%);
    background: #ffffff;
    box-shadow: 0 12px 24px rgba(30, 77, 114, 0.3);
    z-index: 2;
  }

  .breathing-ball--bottom {
    bottom: 4%;
  }

  .breathing-ball--top {
    bottom: 78%;
    background: #bde5ff;
  }

  .breathing-ball.rise {
    transition: bottom 5s linear, background-color 5s linear;
  }

  .breathing-ball.fall {
    transition: bottom 7s linear, background-color 7s linear;
  }

  @media (min-width: 1200px) {
    .breathing-game-shell {
      height: calc(100dvh - 56px);
    }

    .breathing-phone {
      width: min(64vw, 390px);
      height: min(80dvh, 760px);
    }
  }

  @media (max-width: 820px) {
    body.breathing-game-mobile .app-sidebar,
    body.breathing-game-mobile .sidebar-overlay,
    body.breathing-game-mobile .mobile-topbar,
    body.breathing-game-mobile .preview-mode-banner,
    body.breathing-game-mobile .free-tier-notice {
      display: none !important;
    }

    body.breathing-game-mobile .app-layout,
    body.breathing-game-mobile .app-content {
      height: 100dvh !important;
      min-height: 100dvh !important;
    }

    body.breathing-game-mobile .app-content {
      padding: 0 !important;
      overflow: hidden !important;
    }

    body.breathing-game-mobile .breathing-game-shell {
      width: 100vw;
      max-width: none;
      margin: 0;
      padding: 0;
      height: 100dvh;
    }

    body.breathing-game-mobile .breathing-game-card {
      border-radius: 0;
      border: 0;
      box-shadow: none !important;
      height: 100dvh;
    }

    body.breathing-game-mobile .breathing-game-card .card-body {
      padding: 0 !important;
    }

    body.breathing-game-mobile .breathing-game-header,
    body.breathing-game-mobile .alert {
      display: none !important;
    }

    body.breathing-game-mobile .breathing-stage {
      padding: 0;
    }

    body.breathing-game-mobile .breathing-phone {
      width: 100vw;
      height: 100dvh;
      border: 0;
      border-radius: 0;
      box-shadow: none;
    }
  }

  @media (max-width: 576px) {
    .breathing-phone {
      border-width: 8px;
      border-radius: 30px;
    }

    .breathing-intro-copy {
      padding: 1.15rem 1rem;
    }

    .breathing-start-button {
      width: 100%;
      max-width: 18rem;
    }
  }
</style>

<script>
  (function () {
    const TOTAL_CYCLES = 3;
    const PREPARE_DELAY = 300;

    const introScreen = document.getElementById('breathing-intro-screen');
    const gameScreen = document.getElementById('breathing-game-screen');
    const startButton = document.getElementById('breathing-start-button');
    const ball = document.getElementById('breathing-ball');
    const message = document.getElementById('breathing-message');
    const cycleCounter = document.getElementById('breathing-cycle-counter');
    const phaseLabel = document.getElementById('breathing-phase-label');
    const timeLabel = document.getElementById('breathing-time-label');
    const centerTimer = document.getElementById('breathing-timer');

    let sessionToken = 0;
    const timeoutIds = [];

    function later(fn, delay) {
      const id = window.setTimeout(fn, delay);
      timeoutIds.push(id);
      return id;
    }

    function clearTimers() {
      while (timeoutIds.length) {
        window.clearTimeout(timeoutIds.pop());
      }
    }

    function syncViewportMode() {
      document.body.classList.add('breathing-game-page');
      document.body.classList.toggle('breathing-game-mobile', window.innerWidth <= 820);
    }

    function setCycle(value) {
      cycleCounter.textContent = `Ciclo ${value}/${TOTAL_CYCLES}`;
    }

    function setPhase(value) {
      phaseLabel.textContent = value;
    }

    function setTimer(value) {
      timeLabel.textContent = `${value}s`;
      centerTimer.textContent = String(value);
    }

    function showMessage(text) {
      message.classList.remove('fade');
      message.textContent = text;
    }

    function hideMessage() {
      message.classList.add('fade');
    }

    function resetBall() {
      ball.classList.remove('rise', 'fall', 'breathing-ball--top');
      ball.classList.add('breathing-ball--bottom');
      ball.style.bottom = '';
      ball.style.backgroundColor = '';
    }

    function moveBallToTop() {
      ball.classList.remove('fall', 'breathing-ball--bottom');
      void ball.offsetWidth;
      ball.classList.add('rise');
      ball.style.bottom = '78%';
      ball.style.backgroundColor = '#bde5ff';
    }

    function pinBallTop() {
      ball.classList.remove('rise', 'fall', 'breathing-ball--bottom');
      ball.classList.add('breathing-ball--top');
      ball.style.bottom = '78%';
      ball.style.backgroundColor = '#bde5ff';
    }

    function moveBallToBottom() {
      ball.classList.remove('rise', 'breathing-ball--top');
      void ball.offsetWidth;
      ball.classList.add('fall');
      ball.style.bottom = '4%';
      ball.style.backgroundColor = '#ffffff';
    }

    function pinBallBottom() {
      ball.classList.remove('rise', 'fall', 'breathing-ball--top');
      ball.classList.add('breathing-ball--bottom');
      ball.style.bottom = '4%';
      ball.style.backgroundColor = '#ffffff';
    }

    function switchScreen(showIntro) {
      const activate = showIntro ? introScreen : gameScreen;
      const deactivate = showIntro ? gameScreen : introScreen;

      deactivate.classList.remove('is-active');
      deactivate.classList.add('is-leaving');
      deactivate.setAttribute('aria-hidden', 'true');

      later(() => {
        deactivate.classList.remove('is-leaving');
        activate.classList.add('is-active');
        activate.setAttribute('aria-hidden', 'false');
      }, 220);
    }

    async function tickSequence(values, token, onTick) {
      for (const value of values) {
        if (token !== sessionToken) {
          return false;
        }

        setTimer(value);
        if (typeof onTick === 'function') {
          onTick(value);
        }

        await new Promise((resolve) => later(resolve, 1000));
      }

      return token === sessionToken;
    }

    async function runCycle(cycleIndex, token) {
      setCycle(cycleIndex);

      setPhase('Preparar');
      showMessage('Inspire por 5 segundos');
      pinBallBottom();
      let ok = await tickSequence([3, 2, 1, 0], token);
      if (!ok) {
        return false;
      }

      setPhase('Inspirar');
      showMessage('Inspire por 5 segundos');
      moveBallToTop();
      ok = await tickSequence([1, 2, 3, 4, 5], token, (value) => {
        if (value === 3) {
          showMessage('Segure por 4 segundos');
        }
      });
      if (!ok) {
        return false;
      }

      setPhase('Segure no topo');
      pinBallTop();
      ok = await tickSequence([1, 2, 3, 4], token, (value) => {
        if (value === 2) {
          showMessage('Espire por 7 segundos');
        }
      });
      if (!ok) {
        return false;
      }

      setPhase('Expirar');
      showMessage('Espire por 7 segundos');
      moveBallToBottom();
      ok = await tickSequence([7, 6, 5, 4, 3, 2, 1, 0], token, (value) => {
        if (value === 5) {
          showMessage('Segure por 4 segundos');
        }
      });
      if (!ok) {
        return false;
      }

      setPhase('Segure embaixo');
      pinBallBottom();
      ok = await tickSequence([4, 3, 2, 1, 0], token, (value) => {
        if (value === 2) {
          showMessage('Inspire por 5 segundos');
        }
      });

      return ok;
    }

    async function startSession() {
      sessionToken += 1;
      const token = sessionToken;

      startButton.disabled = true;
      clearTimers();
      setCycle(0);
      setPhase('Preparar');
      setTimer(0);
      resetBall();
      hideMessage();
      switchScreen(false);

      await new Promise((resolve) => later(resolve, PREPARE_DELAY));
      if (token !== sessionToken) {
        return;
      }

      showMessage('Inspire por 5 segundos');

      for (let cycleIndex = 1; cycleIndex <= TOTAL_CYCLES; cycleIndex += 1) {
        const ok = await runCycle(cycleIndex, token);
        if (!ok) {
          return;
        }
      }

      setCycle(0);
      setPhase('Preparar');
      setTimer(0);
      resetBall();
      switchScreen(true);
      startButton.disabled = false;
    }

    syncViewportMode();
    resetBall();
    setCycle(0);
    setPhase('Preparar');
    setTimer(0);

    startButton.addEventListener('click', startSession);
    window.addEventListener('resize', syncViewportMode);
    window.addEventListener('beforeunload', () => {
      clearTimers();
      document.body.classList.remove('breathing-game-mobile', 'breathing-game-page');
    });
  })();
</script>

<?php include __DIR__ . '/../partials/footer.php'; ?>
