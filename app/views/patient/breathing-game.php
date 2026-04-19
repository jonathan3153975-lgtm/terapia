<?php $title = 'Jogo da respiração'; include __DIR__ . '/../partials/header.php'; include __DIR__ . '/../partials/nav.php'; ?>
<div class="container page-wrap">
  <?php include __DIR__ . '/../partials/flash-alert.php'; ?>

  <section class="card border-0 shadow-sm">
    <div class="card-body p-3 p-md-4">
      <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <h4 class="mb-0">Jogo da respiração</h4>
        <span id="breathing-cycle-counter" class="badge text-bg-primary">Ciclo 0/3</span>
      </div>

      <div class="breathing-stage" id="breathing-stage" role="application" aria-label="Jogo de respiração guiada">
        <div class="breathing-phone">
          <div class="breathing-track" aria-hidden="true"></div>

          <div class="breathing-modules">
            <div class="breathing-module">
              <small>Fase</small>
              <strong id="breathing-phase-label">Preparar</strong>
            </div>
            <div class="breathing-module">
              <small>Tempo</small>
              <strong id="breathing-time-label">3s</strong>
            </div>
          </div>

          <div class="breathing-image" id="breathing-image" aria-hidden="true">
            <svg viewBox="0 0 180 120" xmlns="http://www.w3.org/2000/svg" focusable="false">
              <defs>
                <linearGradient id="lungsGradient" x1="0" y1="0" x2="0" y2="1">
                  <stop offset="0%" stop-color="#e9f6ff"/>
                  <stop offset="100%" stop-color="#b3deff"/>
                </linearGradient>
              </defs>
              <path d="M90 62 L90 18" stroke="#7dc7ff" stroke-width="8" stroke-linecap="round" fill="none"/>
              <path d="M86 28 C70 20, 45 30, 40 52 C34 76, 49 98, 69 102 C83 104, 90 91, 90 78 Z" fill="url(#lungsGradient)" stroke="#7dc7ff" stroke-width="4"/>
              <path d="M94 28 C110 20, 135 30, 140 52 C146 76, 131 98, 111 102 C97 104, 90 91, 90 78 Z" fill="url(#lungsGradient)" stroke="#7dc7ff" stroke-width="4"/>
            </svg>
          </div>

          <div class="breathing-message" id="breathing-message">Inspire profundamente por 5 segundos...</div>
          <div class="breathing-timer" id="breathing-timer" aria-live="polite">3</div>
          <div class="breathing-ball" id="breathing-ball" aria-hidden="true"></div>
          <div class="breathing-end" id="breathing-end" aria-live="polite"></div>
        </div>
      </div>
    </div>
  </section>
</div>

<style>
  .breathing-stage {
    display: flex;
    justify-content: center;
    padding: 0.5rem 0;
  }

  .breathing-phone {
    position: relative;
    width: min(92vw, 390px);
    aspect-ratio: 9 / 18;
    border-radius: 38px;
    border: 10px solid #0e2333;
    background: linear-gradient(180deg, #ffffff 0%, #5ea9ff 100%);
    box-shadow: 0 22px 48px rgba(15, 53, 84, 0.25);
    overflow: hidden;
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
    font-size: clamp(1rem, 2.4vw, 1.1rem);
    font-weight: 700;
    line-height: 1.4;
    opacity: 1;
    transition: opacity 0.8s ease;
    text-shadow: 0 1px 0 rgba(255, 255, 255, 0.75);
  }

  .breathing-image {
    position: absolute;
    left: 50%;
    top: 32%;
    width: min(44vw, 176px);
    transform: translateX(-50%);
    opacity: 0;
    transition: opacity 1s ease;
    filter: drop-shadow(0 10px 24px rgba(18, 93, 148, 0.22));
    z-index: 2;
  }

  .breathing-image.show {
    opacity: 1;
  }

  .breathing-ball {
    position: absolute;
    left: 50%;
    width: min(18vw, 72px);
    height: min(18vw, 72px);
    border-radius: 999px;
    transform: translateX(-50%);
    bottom: 8%;
    background: #ffffff;
    box-shadow: 0 12px 24px rgba(30, 77, 114, 0.3);
    z-index: 2;
    transition: bottom 5s linear, background-color 5s linear;
  }

  .breathing-ball.rise {
    bottom: 78%;
    background: #bde5ff;
    transition: bottom 5s linear, background-color 5s linear;
  }

  .breathing-ball.fall {
    bottom: 8%;
    background: #ffffff;
    transition: bottom 7s linear, background-color 7s linear;
  }

  .breathing-ball.hold {
    bottom: 78%;
    background: #bde5ff;
    transition: none;
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

  .breathing-message.fade {
    opacity: 0;
    transition-duration: 0.35s;
  }

  .breathing-end {
    position: absolute;
    inset: auto 8% 7% 8%;
    text-align: center;
    color: #06345a;
    font-weight: 700;
    opacity: 0;
    transition: opacity 0.7s ease;
  }

  .breathing-end.show {
    opacity: 1;
  }

  @media (max-width: 576px) {
    .breathing-phone {
      border-width: 8px;
      border-radius: 30px;
    }
  }
</style>

<script>
  (function () {
    const TOTAL_CYCLES = 3;
    const PRE_START_SECONDS = 3;
    const INHALE_SECONDS = 5;
    const HOLD_SECONDS = 4;
    const EXHALE_SECONDS = 7;

    const ball = document.getElementById('breathing-ball');
    const message = document.getElementById('breathing-message');
    const image = document.getElementById('breathing-image');
    const endMessage = document.getElementById('breathing-end');
    const cycleCounter = document.getElementById('breathing-cycle-counter');
    const phaseLabel = document.getElementById('breathing-phase-label');
    const timeLabel = document.getElementById('breathing-time-label');
    const centerTimer = document.getElementById('breathing-timer');

    let cycle = 0;
    let stopped = false;

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

    function setCounter(value) {
      cycleCounter.textContent = `Ciclo ${value}/${TOTAL_CYCLES}`;
    }

    function setPhase(value) {
      phaseLabel.textContent = value;
    }

    function setTime(seconds) {
      timeLabel.textContent = `${seconds}s`;
      centerTimer.textContent = String(seconds);
    }

    function showMessage(text) {
      message.classList.remove('fade');
      message.textContent = text;
    }

    function hideMessage() {
      message.classList.add('fade');
    }

    function holdBallTop() {
      void ball.offsetWidth;
      ball.classList.remove('rise', 'fall');
      ball.classList.add('hold');
    }

    function moveBallUp() {
      void ball.offsetWidth;
      ball.classList.remove('hold', 'fall');
      ball.classList.add('rise');
    }

    function moveBallDown() {
      void ball.offsetWidth;
      ball.classList.remove('hold', 'rise');
      ball.classList.add('fall');
    }

    async function countdown(seconds, onTick) {
      for (let remaining = seconds; remaining >= 1; remaining -= 1) {
        if (stopped) {
          return;
        }

        setTime(remaining);
        if (typeof onTick === 'function') {
          onTick(remaining);
        }

        await new Promise((resolve) => later(resolve, 1000));
      }
    }

    async function runCycle(currentCycle) {
      setPhase('Inspiração');
      showMessage('Inspire profundamente por 5 segundos...');
      image.classList.remove('show');
      moveBallUp();

      await countdown(INHALE_SECONDS, (remaining) => {
        if (remaining === 3) {
          hideMessage();
          later(() => showMessage('Continue inspirando...'), 220);
        }
      });

      if (stopped) {
        return;
      }

      setPhase('Segure');
      holdBallTop();
      showMessage('Segure por 4 segundos...');

      await countdown(HOLD_SECONDS, (remaining) => {
        if (remaining === 2) {
          image.classList.add('show');
          showMessage('Espire por 7 segundos...');
        }
      });

      if (stopped) {
        return;
      }

      setPhase('Expiração');
      moveBallDown();
      showMessage('Espire por 7 segundos...');

      await countdown(EXHALE_SECONDS, (remaining) => {
        if (remaining === 2 && currentCycle < TOTAL_CYCLES) {
          hideMessage();
          later(() => showMessage('Inspire profundamente por 5 segundos...'), 220);
        }
      });
    }

    async function runSession() {
      setCounter(0);
      setPhase('Preparar');
      showMessage('Inspire profundamente por 5 segundos...');

      await countdown(PRE_START_SECONDS);

      for (let currentCycle = 1; currentCycle <= TOTAL_CYCLES; currentCycle += 1) {
        if (stopped) {
          return;
        }

        cycle = currentCycle;
        setCounter(cycle);
        await runCycle(currentCycle);
      }

      finalize();
    }

    function finalize() {
      stopped = true;
      clearTimers();
      image.classList.remove('show');
      hideMessage();
      setPhase('Finalizado');
      setTime(0);
      endMessage.textContent = 'Sessão finalizada. Excelente trabalho.';
      endMessage.classList.add('show');
    }

    runSession();

    window.addEventListener('beforeunload', clearTimers);
  })();
</script>

<?php include __DIR__ . '/../partials/footer.php'; ?>
