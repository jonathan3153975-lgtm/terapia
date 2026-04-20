<?php $title = 'Visualizar Vídeo - teraTube'; include __DIR__ . '/../../partials/header.php'; include __DIR__ . '/../../partials/nav.php'; ?>
<div class="page-wrap">
  <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <h4 class="mb-0">Vídeo: <?php echo htmlspecialchars((string) ($video['title'] ?? '')); ?></h4>
    <div class="d-flex gap-2">
      <a class="btn btn-light" href="<?php echo $appUrl; ?>/dashboard.php?action=therapist-teratube">Voltar</a>
      <a class="btn btn-primary" href="<?php echo $appUrl; ?>/dashboard.php?action=therapist-teratube-edit&id=<?php echo (int) ($video['id'] ?? 0); ?>">Editar</a>
    </div>
  </div>

  <div class="card mb-3">
    <div class="card-body">
      <div class="row g-3">
        <div class="col-md-4">
          <div><strong>Status:</strong> <?php echo (int) ($video['is_published'] ?? 0) === 1 ? 'Liberado para pacientes' : 'Rascunho'; ?></div>
        </div>
        <div class="col-md-4">
          <div><strong>Nota média:</strong> <?php echo number_format((float) ($video['average_rating'] ?? 0), 1, ',', '.'); ?> (<?php echo (int) ($video['rating_count'] ?? 0); ?>)</div>
        </div>
        <div class="col-md-4">
          <div><strong>Comentários:</strong> <?php echo (int) ($video['comment_count'] ?? 0); ?></div>
        </div>
        <div class="col-12">
          <div><strong>Palavras-chave:</strong> <?php echo htmlspecialchars((string) ($video['keywords'] ?? '-')); ?></div>
        </div>
        <div class="col-12">
          <div><strong>Descrição:</strong><br><?php echo nl2br(htmlspecialchars((string) ($video['description_text'] ?? '-'))); ?></div>
        </div>
      </div>
    </div>
  </div>

  <div class="card mb-3">
    <div class="card-header bg-transparent"><strong>Reprodução</strong></div>
    <div class="card-body">
      <?php if ((string) ($video['source_type'] ?? '') === 'youtube' && !empty($video['youtube_video_id'])): ?>
        <div class="ratio ratio-16x9">
          <iframe src="https://www.youtube.com/embed/<?php echo rawurlencode((string) $video['youtube_video_id']); ?>" title="Reprodução do vídeo" allowfullscreen></iframe>
        </div>
      <?php elseif (!empty($video['video_path'])): ?>
        <video controls preload="metadata" style="width:100%;max-height:72vh;border-radius:.75rem;border:1px solid #dee2e6;" src="<?php echo $appUrl; ?>/dashboard.php?action=therapist-teratube-file&id=<?php echo (int) ($video['id'] ?? 0); ?>"></video>
      <?php else: ?>
        <div class="text-muted">Conteúdo de vídeo indisponível.</div>
      <?php endif; ?>
    </div>
  </div>

  <div class="card">
    <div class="card-header bg-transparent d-flex justify-content-between align-items-center flex-wrap gap-2">
      <strong>Comentários dos pacientes</strong>
      <span class="small text-muted"><?php echo count($comments ?? []); ?> comentário(s)</span>
    </div>
    <div class="card-body">
      <?php if (empty($comments)): ?>
        <div class="text-muted">Ainda não há comentários neste vídeo.</div>
      <?php else: ?>
        <div class="d-flex flex-column gap-3">
          <?php foreach ($comments as $comment): ?>
            <div class="border rounded p-3">
              <div class="d-flex justify-content-between align-items-center mb-2">
                <strong><?php echo htmlspecialchars((string) ($comment['patient_first_name'] ?? 'Paciente')); ?></strong>
                <small class="text-muted"><?php echo !empty($comment['created_at']) ? date('d/m/Y H:i', strtotime((string) $comment['created_at'])) : '-'; ?></small>
              </div>
              <div><?php echo nl2br(htmlspecialchars((string) ($comment['comment_text'] ?? ''))); ?></div>
              <div class="small text-muted mt-2">Avaliação do comentário: <?php echo number_format((float) ($comment['average_rating'] ?? 0), 1, ',', '.'); ?> (<?php echo (int) ($comment['rating_count'] ?? 0); ?>)</div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../../partials/footer.php'; ?>