<?php $title = 'Assistir - teraTube'; include __DIR__ . '/../partials/header.php'; include __DIR__ . '/../partials/nav.php'; ?>
<style>
  .teratube-watch-page .video-shell {
    background: linear-gradient(145deg, #fbfaf6 0%, #fff 55%, #f3f7fa 100%);
    border: 1px solid #e7eaee;
    border-radius: 1rem;
    box-shadow: 0 12px 28px rgba(19, 33, 68, .08);
  }
  .teratube-watch-page .rating-pill {
    background: #fff6e1;
    border: 1px solid #f3d28a;
    color: #7a4e00;
    font-weight: 600;
  }
  .teratube-watch-page .comment-card {
    border: 1px solid #e8edf1;
    border-radius: .9rem;
    background: #fff;
  }
  .teratube-watch-page .comment-card.is-top {
    border-color: #f3d28a;
    box-shadow: 0 10px 20px rgba(243, 210, 138, .18);
  }
  .teratube-watch-page .comment-score {
    font-size: .8rem;
    background: #eef8f1;
    color: #186838;
    border: 1px solid #cde8d6;
    border-radius: 999px;
    padding: .15rem .55rem;
    display: inline-flex;
    align-items: center;
    gap: .3rem;
  }
</style>
<div class="container page-wrap teratube-watch-page">
  <?php include __DIR__ . '/../partials/flash-alert.php'; ?>

  <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <h3 class="mb-0"><?php echo htmlspecialchars((string) ($video['title'] ?? 'Vídeo')); ?></h3>
    <a class="btn btn-light" href="<?php echo $appUrl; ?>/patient.php?action=teratube">Voltar ao teraTube</a>
  </div>

  <div class="card mb-3 video-shell">
    <div class="card-body">
      <?php if ((string) ($video['source_type'] ?? '') === 'youtube' && !empty($video['youtube_video_id'])): ?>
        <div class="ratio ratio-16x9 mb-3">
          <iframe src="https://www.youtube.com/embed/<?php echo rawurlencode((string) $video['youtube_video_id']); ?>" title="Reprodução do vídeo" allowfullscreen></iframe>
        </div>
      <?php elseif (!empty($video['video_path'])): ?>
        <video controls preload="metadata" style="width:100%;max-height:72vh;border-radius:.75rem;border:1px solid #dee2e6;" src="<?php echo $appUrl; ?>/patient.php?action=teratube-file&id=<?php echo (int) ($video['id'] ?? 0); ?>"></video>
      <?php else: ?>
        <div class="text-muted">Conteúdo indisponível.</div>
      <?php endif; ?>

      <div class="d-flex flex-wrap align-items-center gap-2 mt-3">
        <form method="POST" action="<?php echo $appUrl; ?>/patient.php?action=teratube-toggle-favorite" class="m-0">
          <input type="hidden" name="video_id" value="<?php echo (int) ($video['id'] ?? 0); ?>">
          <input type="hidden" name="redirect_action" value="teratube-watch">
          <button class="btn <?php echo !empty($isFavorite) ? 'btn-outline-warning' : 'btn-outline-primary'; ?>" type="submit"><i class="fa-solid <?php echo !empty($isFavorite) ? 'fa-bookmark-slash' : 'fa-bookmark'; ?> me-1"></i><?php echo !empty($isFavorite) ? 'Remover de Meus conteúdos' : 'Salvar em Meus conteúdos'; ?></button>
        </form>

        <span class="badge rating-pill"><i class="fa-solid fa-star"></i> Nota média: <?php echo number_format((float) ($video['average_rating'] ?? 0), 1, ',', '.'); ?> / 5 (<?php echo (int) ($video['rating_count'] ?? 0); ?> avaliações)</span>
        <span class="badge text-bg-light border"><?php echo (int) ($video['comment_count'] ?? 0); ?> comentário(s)</span>
      </div>

      <?php if (!empty($video['description_text'])): ?>
        <hr>
        <p class="mb-0"><?php echo nl2br(htmlspecialchars((string) $video['description_text'])); ?></p>
      <?php endif; ?>
    </div>
  </div>

  <div class="row g-3">
    <div class="col-12 col-lg-5">
      <div class="card h-100">
        <div class="card-header bg-transparent"><strong>Sua avaliação</strong></div>
        <div class="card-body">
          <form method="POST" action="<?php echo $appUrl; ?>/patient.php?action=teratube-rate" class="d-flex flex-column gap-3">
            <input type="hidden" name="video_id" value="<?php echo (int) ($video['id'] ?? 0); ?>">
            <div>
              <div class="mb-2">Escolha de 1 a 5 estrelas:</div>
              <div class="d-flex flex-wrap gap-2">
                <?php for ($star = 1; $star <= 5; $star++): ?>
                  <label class="btn btn-sm <?php echo (int) ($myRating ?? 0) === $star ? 'btn-warning' : 'btn-outline-warning'; ?>">
                    <input class="d-none" type="radio" name="rating" value="<?php echo $star; ?>" <?php echo (int) ($myRating ?? 0) === $star ? 'checked' : ''; ?> required>
                    <?php echo $star; ?> <i class="fa-solid fa-star"></i>
                  </label>
                <?php endfor; ?>
              </div>
            </div>
            <button class="btn btn-primary" type="submit">Salvar avaliação</button>
          </form>
        </div>
      </div>
    </div>

    <div class="col-12 col-lg-7">
      <div class="card h-100">
        <div class="card-header bg-transparent"><strong>Comentários dos pacientes</strong></div>
        <div class="card-body">
          <form method="POST" action="<?php echo $appUrl; ?>/patient.php?action=teratube-comment" class="mb-4">
            <input type="hidden" name="video_id" value="<?php echo (int) ($video['id'] ?? 0); ?>">
            <label class="form-label">Seu comentário</label>
            <textarea class="form-control mb-2" name="comment_text" rows="3" maxlength="1200" required placeholder="Compartilhe como esse vídeo ajudou você..."></textarea>
            <button class="btn btn-outline-primary" type="submit">Publicar comentário</button>
          </form>

          <?php if (empty($comments)): ?>
            <div class="text-muted">Ainda não há comentários neste vídeo.</div>
          <?php else: ?>
            <div class="small text-muted mb-3">
              Exibindo comentários por utilidade: mais bem avaliados aparecem primeiro.
            </div>
            <div class="d-flex flex-column gap-3">
              <?php foreach ($comments as $idx => $comment): ?>
                <?php $commentId = (int) ($comment['id'] ?? 0); ?>
                <?php $isTopComment = $idx === 0 && (int) ($comment['rating_count'] ?? 0) > 0; ?>
                <?php $helpfulness = (float) ($comment['average_rating'] ?? 0); ?>
                <div class="comment-card p-3 <?php echo $isTopComment ? 'is-top' : ''; ?>">
                  <div class="d-flex justify-content-between align-items-center mb-2">
                    <div class="d-flex align-items-center gap-2">
                      <strong><?php echo htmlspecialchars((string) ($comment['patient_first_name'] ?? 'Paciente')); ?></strong>
                      <?php if ($isTopComment): ?>
                        <span class="badge text-bg-warning">Destaque</span>
                      <?php endif; ?>
                    </div>
                    <small class="text-muted"><?php echo !empty($comment['created_at']) ? date('d/m/Y H:i', strtotime((string) $comment['created_at'])) : '-'; ?></small>
                  </div>

                  <div class="mb-2"><?php echo nl2br(htmlspecialchars((string) ($comment['comment_text'] ?? ''))); ?></div>
                  <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                    <span class="comment-score"><i class="fa-solid fa-thumbs-up"></i> Utilidade: <?php echo number_format($helpfulness, 1, ',', '.'); ?>/5</span>
                    <span class="small text-muted"><?php echo (int) ($comment['rating_count'] ?? 0); ?> avaliação(ões)</span>
                  </div>

                  <?php if ((int) ($comment['patient_id'] ?? 0) !== (int) ($_SESSION['auth']['patient_id'] ?? 0)): ?>
                    <form method="POST" action="<?php echo $appUrl; ?>/patient.php?action=teratube-comment-rate" class="d-flex flex-wrap gap-2 align-items-center">
                      <input type="hidden" name="video_id" value="<?php echo (int) ($video['id'] ?? 0); ?>">
                      <input type="hidden" name="comment_id" value="<?php echo $commentId; ?>">
                      <span class="small">Este comentário foi útil?</span>
                      <?php $myCommentRate = (int) ($commentRatingMap[$commentId] ?? 0); ?>
                      <label class="btn btn-sm <?php echo $myCommentRate >= 4 ? 'btn-success' : 'btn-outline-success'; ?> mb-0">
                        <input class="d-none" type="radio" name="rating" value="5" <?php echo $myCommentRate >= 4 ? 'checked' : ''; ?> required>
                        <i class="fa-solid fa-thumbs-up"></i> Sim
                      </label>
                      <label class="btn btn-sm <?php echo ($myCommentRate > 0 && $myCommentRate <= 2) ? 'btn-danger' : 'btn-outline-danger'; ?> mb-0">
                        <input class="d-none" type="radio" name="rating" value="1" <?php echo ($myCommentRate > 0 && $myCommentRate <= 2) ? 'checked' : ''; ?> required>
                        <i class="fa-solid fa-thumbs-down"></i> Não
                      </label>
                      <button class="btn btn-sm btn-outline-primary" type="submit">Enviar</button>
                    </form>
                  <?php else: ?>
                    <div class="small text-muted">Este comentário é seu.</div>
                  <?php endif; ?>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../partials/footer.php'; ?>