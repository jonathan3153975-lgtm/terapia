<?php

namespace App\Models;

use Classes\Model;

class VirtualTask extends Model
{
    protected string $table = 'tasks';

    /**
     * Retorna a estrutura padrão da Árvore da Vida
     */
    public static function getTreeOfLifeStructure(): array
    {
        return [
            'sections' => [
                [
                    'key' => 'tempestades',
                    'title' => '🌪️ TEMPESTADES (Desafios)',
                    'color' => '#e74c3c',
                    'helper_text' => 'As tempestades podem incluir: problemas de saúde mental, conflitos com amigos ou familiares e falta de recursos e apoio.',
                    'questions' => [
                        'Quais tempestades você já enfrentou no passado?',
                        'Quais obstáculos você precisa superar?',
                        'Quais tempestades você pode enfrentar no futuro?'
                    ]
                ],
                [
                    'key' => 'folhas',
                    'title' => '🍃 FOLHAS (Pessoas Significativas)',
                    'color' => '#27ae60',
                    'questions' => [
                        'Quem desempenha um papel vital na sua vida?',
                        'Quem são as pessoas em quem você confia e procura para obter apoio?',
                        'O que faz você confiar nelas?',
                        'Como elas influenciaram sua vida até agora?',
                        'Elas sabem que são importantes para você?'
                    ]
                ],
                [
                    'key' => 'solo',
                    'title' => '🌍 SOLO (Vida Presente)',
                    'color' => '#795548',
                    'questions' => [
                        'Qual é a melhor coisa sobre sua vida atual?',
                        'Como você gosta de passar seu tempo livre?',
                        'O que você gostaria de ter mais tempo para fazer?',
                        'O que você valoriza mais?'
                    ]
                ],
                [
                    'key' => 'raizes',
                    'title' => '🌳 RAÍZES (De Onde Eu Vim)',
                    'color' => '#8B4513',
                    'questions' => [
                        'Onde você nasceu?',
                        'Quais são suas memórias favoritas?',
                        'Quem ajudou a moldar sua vida?',
                        'Você acha que seu passado influenciou sua vida presente?'
                    ]
                ],
                [
                    'key' => 'tronco',
                    'title' => '💪 TRONCO (Habilidades)',
                    'color' => '#a0522d',
                    'questions' => [
                        'Quais são suas 3 principais habilidades?',
                        'Como você desenvolveu suas habilidades e capacidades de enfrentamento?',
                        'Algo impactou suas habilidades de enfrentamento?',
                        'Você foca mais em suas falhas do que em suas habilidades?'
                    ]
                ],
                [
                    'key' => 'galhos',
                    'title' => '🎯 GALHOS (Sonhos)',
                    'color' => '#f39c12',
                    'questions' => [
                        'Se você tivesse três desejos, quais seriam?',
                        'Você usaria seus desejos para se aprimorar?',
                        'Você daria algum dos seus desejos para outras pessoas? Se sim, para quem e por quê?'
                    ]
                ],
                [
                    'key' => 'frutos',
                    'title' => '✨ FRUTOS (Dons)',
                    'color' => '#f1c40f',
                    'questions' => [
                        'Que elogios você já recebeu?',
                        'Quais são algumas das suas forças?',
                        'As pessoas significativas na sua vida ajudaram a moldar suas forças?',
                        'Você já recebeu algum presente material que tenha te ajudado?',
                        'Em qual força você está trabalhando no momento?'
                    ]
                ]
            ],
            'final_section' => [
                'key' => 'historia',
                'title' => '📖 HISTÓRIA DE VIDA',
                'blocks' => [
                    [
                        'key' => 'passado',
                        'icon' => '🔙',
                        'title' => 'Passado',
                        'questions' => [
                            'Qual é a história do meu passado?',
                            'Quais desafios eu tive que superar?',
                            'Quais forças eu ganhei com minhas experiências passadas?'
                        ]
                    ],
                    [
                        'key' => 'presente',
                        'icon' => '📍',
                        'title' => 'Presente',
                        'questions' => [
                            'Como eu descreveria minha vida atual e o tipo de pessoa que sou?',
                            'Eu sou diferente da pessoa que fui no passado?',
                            'Estou enfrentando algum novo desafio atualmente?'
                        ]
                    ],
                    [
                        'key' => 'futuro',
                        'icon' => '🔮',
                        'title' => 'Futuro',
                        'questions' => [
                            'Como é o meu futuro ideal?',
                            'Ele seria diferente de como é agora? Se sim, como?',
                            'Quem está no meu futuro?'
                        ]
                    ]
                ]
            ]
        ];
    }

    /**
     * Cria uma tarefa dinâmica para um paciente
     */
    public function createVirtualTask(int $therapistId, int $patientId, string $taskType = 'virtual_tree_of_life', ?string $title = null): int|false
    {
        $structure = self::getTreeOfLifeStructure();
        $title = $title ?? 'Árvore da Vida';

        $data = [
            'therapist_id' => $therapistId,
            'patient_id' => $patientId,
            'title' => $title,
            'description' => 'Explore sua árvore da vida respondendo perguntas sobre seus desafios, pessoas importantes, habilidades e sonhos.',
            'task_type' => $taskType,
            'content_json' => json_encode($structure),
            'is_active' => 1,
            'due_date' => date('Y-m-d'),
            'send_to_patient' => 1,
            'delivery_kind' => 'task',
            'status' => 'pending',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        return $this->insert($data);
    }

    /**
     * Busca tarefas dinâmicas de um terapeuta
     */
    public function getVirtualTasksByTherapist(int $therapistId, ?string $taskType = null): array
    {
        $sql = "SELECT * FROM tasks WHERE therapist_id = ? AND task_type != 'regular'";
        $params = [$therapistId];

        if ($taskType) {
            $sql .= " AND task_type = ?";
            $params[] = $taskType;
        }

        $sql .= " ORDER BY created_at DESC";

        $stmt = $this->query($sql, $params);
        if (!$stmt) {
            return [];
        }

        return $stmt->fetchAll();
    }

    /**
     * Busca tarefas dinâmicas ativas de um paciente
     */
    public function getActiveVirtualTasksForPatient(int $patientId): array
    {
        $sql = "SELECT t.* FROM tasks t 
                WHERE t.patient_id = ? 
                AND t.task_type != 'regular'
                AND t.is_active = 1
                AND t.status = 'pending'
                ORDER BY t.created_at DESC";

        $stmt = $this->query($sql, [$patientId]);
        if (!$stmt) {
            return [];
        }

        return $stmt->fetchAll();
    }

    /**
     * Marca tarefa dinâmica como concluída
     */
    public function completeVirtualTask(int $taskId, string $reflectionHtml): bool
    {
        $sql = "UPDATE tasks SET status = ?, is_active = 0, patient_response_html = ?, responded_at = NOW() 
                WHERE id = ?";

        return (bool) $this->query($sql, ['done', $reflectionHtml, $taskId]);
    }

    /**
     * Salva resposta de uma seção da tarefa
     */
    public function saveResponse(int $therapistId, int $patientId, int $taskId, string $sectionName, string $answersJson): bool
    {
        $existingStmt = $this->query(
            'SELECT id FROM virtual_task_responses WHERE task_id = ? AND section_name = ? LIMIT 1',
            [$taskId, $sectionName]
        );
        $existing = $existingStmt ? $existingStmt->fetch() : null;

        if ($existing && isset($existing['id'])) {
            return (bool) $this->query(
                'UPDATE virtual_task_responses SET answers_json = ?, updated_at = NOW() WHERE id = ?',
                [$answersJson, (int) $existing['id']]
            );
        }

        return (bool) $this->query(
            'INSERT INTO virtual_task_responses (therapist_id, patient_id, task_id, section_name, answers_json, created_at, updated_at) VALUES (?, ?, ?, ?, ?, NOW(), NOW())',
            [$therapistId, $patientId, $taskId, $sectionName, $answersJson]
        );
    }

    /**
     * Busca respostas completas de uma tarefa
     */
    public function getTaskResponses(int $taskId, ?int $patientId = null): array
    {
        $sql = "SELECT * FROM virtual_task_responses WHERE task_id = ?";
        $params = [$taskId];

        if ($patientId) {
            $sql .= " AND patient_id = ?";
            $params[] = $patientId;
        }

        $stmt = $this->query($sql, $params);
        if (!$stmt) {
            return [];
        }

        return $stmt->fetchAll();
    }

    public function formatResponseHtml(array $structure, array $answersBySection, string $reflectionHtml = ''): string
    {
        $sections = $structure['sections'] ?? [];
        $htmlParts = [
            '<div style="font-family: -apple-system, BlinkMacSystemFont, Segoe UI, Roboto, Arial, sans-serif; color: #243746; line-height: 1.6;">',
            '<div style="padding: 18px 20px; border-radius: 14px; background: linear-gradient(135deg, #0f6aa1 0%, #3da9e4 55%, #9edcff 100%); color: #ffffff; margin-bottom: 16px;">',
            '<div style="font-size: 12px; letter-spacing: .08em; text-transform: uppercase; opacity: .9;">Relatório terapêutico</div>',
            '<h2 style="margin: 4px 0 0; font-size: 24px; line-height: 1.2;">Árvore da Vida</h2>',
            '</div>',
        ];

        if (is_array($sections) && $sections !== []) {
            foreach ($sections as $section) {
                $sectionKey = (string) ($section['key'] ?? '');
                $sectionTitle = (string) ($section['title'] ?? 'Seção');
                $questions = is_array($section['questions'] ?? null) ? $section['questions'] : [];
                $answers = is_array($answersBySection[$sectionKey] ?? null) ? $answersBySection[$sectionKey] : [];

                if ($sectionKey === '') {
                    continue;
                }

                $htmlParts[] = '<section style="margin: 0 0 18px; padding: 16px; border: 1px solid #dbe9f4; border-radius: 14px; background: #f9fcff; box-shadow: 0 4px 14px rgba(20, 69, 99, 0.05);">';
                $htmlParts[] = '<h3 style="margin: 0 0 12px; font-size: 18px; color: #123d5a;">' . $this->escapeHtml($sectionTitle) . '</h3>';

                if ($questions === []) {
                    $questions = ['Sem perguntas cadastradas para esta seção.'];
                }

                foreach ($questions as $index => $question) {
                    $questionText = (string) $question;
                    $answerText = trim((string) ($answers[$index] ?? ''));
                    $isAnswered = $answerText !== '';

                    $htmlParts[] = '<div style="margin-bottom: 14px;">';
                    $htmlParts[] = '<div style="font-weight: 600; color: #35576f; margin-bottom: 4px;">' . ($index + 1) . '. ' . $this->escapeHtml($questionText) . '</div>';
                    $htmlParts[] = '<div style="padding: 10px 12px; background: #ffffff; border: 1px solid ' . ($isAnswered ? '#d5e7f3' : '#f0d9b2') . '; border-radius: 10px;">';
                    $htmlParts[] = $isAnswered
                        ? nl2br($this->escapeHtml($answerText))
                        : '<span style="color: #8b6b2c; font-style: italic;">Não respondida.</span>';
                    $htmlParts[] = '</div>';
                    $htmlParts[] = '</div>';
                }

                $htmlParts[] = '</section>';
            }
        } else {
            foreach ($answersBySection as $sectionKey => $answers) {
                if (!is_array($answers) || $answers === []) {
                    continue;
                }

                $htmlParts[] = '<section style="margin: 0 0 18px; padding: 16px; border: 1px solid #dbe9f4; border-radius: 12px; background: #f9fcff;">';
                $htmlParts[] = '<h3 style="margin: 0 0 12px; font-size: 18px; color: #123d5a;">' . $this->escapeHtml((string) $sectionKey) . '</h3>';

                foreach ($answers as $index => $answer) {
                    $answerText = trim((string) $answer);

                    $htmlParts[] = '<div style="margin-bottom: 14px;">';
                    $htmlParts[] = '<div style="font-weight: 600; color: #35576f; margin-bottom: 4px;">Pergunta ' . ($index + 1) . '</div>';
                    $htmlParts[] = '<div style="padding: 10px 12px; background: #ffffff; border: 1px solid #e2edf5; border-radius: 10px;">' . ($answerText !== '' ? nl2br($this->escapeHtml($answerText)) : '<span style="color: #8b6b2c; font-style: italic;">Não respondida.</span>') . '</div>';
                    $htmlParts[] = '</div>';
                }

                $htmlParts[] = '</section>';
            }
        }

        if (trim(strip_tags($reflectionHtml)) !== '') {
            $htmlParts[] = '<section style="margin: 0 0 18px; padding: 16px; border: 1px solid #dbe9f4; border-radius: 14px; background: #f9fcff; box-shadow: 0 4px 14px rgba(20, 69, 99, 0.05);">';
            $htmlParts[] = '<h3 style="margin: 0 0 12px; font-size: 18px; color: #123d5a;">Reflexão final</h3>';
            $htmlParts[] = '<div style="padding: 10px 12px; background: #ffffff; border: 1px solid #e2edf5; border-radius: 10px;">' . $reflectionHtml . '</div>';
            $htmlParts[] = '</section>';
        }

        $htmlParts[] = '</div>';

        return implode('', $htmlParts);
    }

    private function escapeHtml(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Obtém a estrutura JSON de uma tarefa dinâmica
     */
    public function getTaskStructure(int $taskId): ?array
    {
        $stmt = $this->query("SELECT content_json FROM tasks WHERE id = ? AND task_type != 'regular' LIMIT 1", [$taskId]);
        $result = $stmt ? $stmt->fetch() : null;

        return $result && !empty($result['content_json']) ? json_decode((string) $result['content_json'], true) : null;
    }

    public function findByTherapistAndId(int $therapistId, int $taskId): ?array
    {
        $stmt = $this->query('SELECT * FROM tasks WHERE id = ? AND therapist_id = ? LIMIT 1', [$taskId, $therapistId]);
        if (!$stmt) {
            return null;
        }

        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function deleteByTherapistAndId(int $therapistId, int $taskId): bool
    {
        return (bool) $this->query('DELETE FROM tasks WHERE id = ? AND therapist_id = ?', [$taskId, $therapistId]);
    }

    public function markInactive(int $taskId): bool
    {
        return (bool) $this->query('UPDATE tasks SET is_active = 0, updated_at = NOW() WHERE id = ?', [$taskId]);
    }

}
