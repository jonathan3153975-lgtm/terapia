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
    inset: 7% 8% auto 8%;
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
    top: 22%;
    width: min(44vw, 176px);
    transform: translateX(-50%);
    opacity: 0;
    transition: opacity 1s ease;
    filter: drop-shadow(0 10px 24px rgba(18, 93, 148, 0.22));
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

  .breathing-message.fade {
    opacity: 0;
    transition-duration: 5s;
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
    const INHALE_MS = 5000;
    const EXHALE_MS = 7000;
    const PREPARE_NEXT_MS = 2000;

    const ball = document.getElementById('breathing-ball');
    const message = document.getElementById('breathing-message');
    const image = document.getElementById('breathing-image');
    const endMessage = document.getElementById('breathing-end');
    const cycleCounter = document.getElementById('breathing-cycle-counter');

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

    function startInhale() {
      if (stopped) {
        return;
      }

      cycle += 1;
      setCounter(cycle);

      image.classList.remove('show');
      message.classList.remove('fade');
      message.textContent = 'Inspire profundamente por 5 segundos...';

      void ball.offsetWidth;
      ball.classList.remove('fall');
      ball.classList.add('rise');

      later(() => {
        image.classList.add('show');
        message.classList.remove('fade');
        message.textContent = 'Espire por 7 segundos...';

        ball.classList.remove('rise');
        ball.classList.add('fall');
      }, 3000);

      later(() => {
        if (cycle < TOTAL_CYCLES) {
          message.classList.remove('fade');
          message.textContent = 'Inspire profundamente por 5 segundos...';
        }
      }, 3000 + EXHALE_MS - PREPARE_NEXT_MS);

      later(() => {
        if (cycle >= TOTAL_CYCLES) {
          finalize();
          return;
        }

        startInhale();
      }, 3000 + EXHALE_MS);

      later(() => {
        message.classList.add('fade');
      }, 400);
    }

    function finalize() {
      stopped = true;
      clearTimers();
      image.classList.remove('show');
      message.classList.add('fade');
      endMessage.textContent = 'Sessão finalizada. Excelente trabalho.';
      endMessage.classList.add('show');
    }

    setCounter(0);
    later(startInhale, 450);

    window.addEventListener('beforeunload', clearTimers);
  })();
</script>

<?php include __DIR__ . '/../partials/footer.php'; ?>
