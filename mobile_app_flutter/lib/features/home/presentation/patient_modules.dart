import 'dart:async';
import 'dart:convert';
import 'dart:io';

import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'package:just_audio/just_audio.dart';
import 'package:url_launcher/url_launcher.dart';

import '../../../core/config/app_config.dart';
import '../../../core/network/api_client.dart';
import '../../../core/services/analytics_service.dart';
import '../../../core/utils/date_formatters.dart';

class TasksPage extends StatefulWidget {
  const TasksPage({
    super.key,
    required this.apiClient,
    required this.analyticsService,
  });

  final ApiClient apiClient;
  final AnalyticsService analyticsService;

  @override
  State<TasksPage> createState() => _TasksPageState();
}

class _TasksPageState extends State<TasksPage> {
  List<Map<String, dynamic>> _items = <Map<String, dynamic>>[];
  String? _error;
  bool _loading = true;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() {
      _loading = true;
      _error = null;
    });

    try {
      final response = await widget.apiClient.fetchTasks();
      final items = _mapList(response['data'], 'items');
      await widget.analyticsService.track('tasks_view', context: 'tasks');
      if (!mounted) {
        return;
      }
      setState(() {
        _items = items;
      });
    } catch (error) {
      if (!mounted) {
        return;
      }
      setState(() {
        _error = _normalizeError(error);
      });
    } finally {
      if (mounted) {
        setState(() {
          _loading = false;
        });
      }
    }
  }

  Future<void> _openTask(Map<String, dynamic> item) async {
    final task = _mapValue(item, 'task');
    final taskId = _intValue(task['id']);
    if (taskId <= 0) {
      return;
    }

    final responseController = TextEditingController();
    bool saving = false;

    try {
      final response = await widget.apiClient.fetchTaskDetails(taskId: taskId);
      final data = _mapValue(response, 'data');
      final taskDetails = _mapValue(data, 'task');
      final linkedMaterials = _mapList(data, 'linked_materials');
      final therapistFiles = _mapList(data, 'therapist_files');
      final patientFiles = _mapList(data, 'patient_files');

      if (!mounted) {
        return;
      }

      if (_isTreeOfLifeTask(taskDetails)) {
        await _showTreeOfLifeTaskSheet(
          parentContext: context,
          apiClient: widget.apiClient,
          analyticsService: widget.analyticsService,
          taskDetails: taskDetails,
          onCompleted: _load,
          analyticsContext: 'tasks',
        );
        return;
      }

      responseController.text = _plainText(_stringValue(taskDetails, ['patient_response_html']));

      await showModalBottomSheet<void>(
        context: context,
        isScrollControlled: true,
        useSafeArea: true,
        builder: (context) {
          return StatefulBuilder(
            builder: (context, setModalState) {
              Future<void> submit() async {
                final plain = responseController.text.trim();
                if (plain.isEmpty) {
                  ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Escreva sua resposta antes de enviar.')));
                  return;
                }

                setModalState(() {
                  saving = true;
                });

                try {
                  await widget.apiClient.respondTask(taskId: taskId, responseHtml: _textToHtml(plain));
                  await widget.analyticsService.track('task_response_submit', context: 'tasks', payload: {'task_id': taskId});
                  if (!mounted || !context.mounted) {
                    return;
                  }
                  Navigator.of(context).pop();
                  ScaffoldMessenger.of(this.context).showSnackBar(const SnackBar(content: Text('Resposta enviada com sucesso.')));
                  await _load();
                } catch (error) {
                  if (!mounted) {
                    return;
                  }
                  ScaffoldMessenger.of(this.context).showSnackBar(SnackBar(content: Text(_normalizeError(error))));
                } finally {
                  if (mounted) {
                    setModalState(() {
                      saving = false;
                    });
                  }
                }
              }

              return Padding(
                padding: EdgeInsets.fromLTRB(24, 16, 24, 24 + MediaQuery.of(context).viewInsets.bottom),
                child: SingleChildScrollView(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      if (_stringValue(taskDetails, ['cover_image_path']).isNotEmpty) ...[
                        _MediaPreview(
                          imageUrl: _resolveMediaUrl(_stringValue(taskDetails, ['cover_image_path'])),
                          height: 180,
                          borderRadius: 18,
                        ),
                        const SizedBox(height: 16),
                      ],
                      Text(_stringValue(taskDetails, ['title', 'material_title'], fallback: 'Tarefa'), style: Theme.of(context).textTheme.titleLarge),
                      const SizedBox(height: 8),
                      Wrap(
                        spacing: 8,
                        runSpacing: 8,
                        children: [
                          _InfoChip(label: 'Status', value: _stringValue(taskDetails, ['status'], fallback: 'pending')),
                          if (_stringValue(taskDetails, ['due_date']).isNotEmpty)
                            _InfoChip(label: 'Entrega', value: _formatDateLabel(_stringValue(taskDetails, ['due_date']))),
                          if (_stringValue(taskDetails, ['task_type']).isNotEmpty)
                            _InfoChip(label: 'Tipo', value: _stringValue(taskDetails, ['task_type'])),
                        ],
                      ),
                      const SizedBox(height: 12),
                      Text(_plainText(_stringValue(taskDetails, ['description', 'description_html'], fallback: 'Sem descrição.'))),
                      const SizedBox(height: 16),
                      if (linkedMaterials.isNotEmpty) ...[
                        Text('Materiais vinculados', style: Theme.of(context).textTheme.titleMedium),
                        const SizedBox(height: 8),
                        Wrap(
                          spacing: 8,
                          runSpacing: 8,
                          children: linkedMaterials
                              .map((material) => Chip(label: Text(_stringValue(material, ['title'], fallback: 'Material'))))
                              .toList(),
                        ),
                        const SizedBox(height: 16),
                      ],
                      if (therapistFiles.isNotEmpty) ...[
                        Text('Arquivos do terapeuta', style: Theme.of(context).textTheme.titleMedium),
                        const SizedBox(height: 8),
                        ...therapistFiles.map((file) => Text(_stringValue(file, ['original_name', 'file_name'], fallback: 'Arquivo'))),
                        const SizedBox(height: 16),
                      ],
                      if (patientFiles.isNotEmpty) ...[
                        Text('Arquivos enviados por você', style: Theme.of(context).textTheme.titleMedium),
                        const SizedBox(height: 8),
                        ...patientFiles.map((file) => Text(_stringValue(file, ['original_name', 'file_name'], fallback: 'Arquivo'))),
                        const SizedBox(height: 16),
                      ],
                      TextField(
                        controller: responseController,
                        minLines: 4,
                        maxLines: 8,
                        decoration: const InputDecoration(
                          labelText: 'Sua resposta',
                          helperText: 'Escreva sua devolutiva para o terapeuta.',
                          alignLabelWithHint: true,
                        ),
                      ),
                      const SizedBox(height: 16),
                      FilledButton(
                        onPressed: saving ? null : submit,
                        child: saving
                            ? const SizedBox(width: 22, height: 22, child: CircularProgressIndicator(strokeWidth: 2.2, color: Colors.white))
                            : const Text('Enviar resposta ao terapeuta'),
                      ),
                    ],
                  ),
                ),
              );
            },
          );
        },
      );
    } catch (error) {
      if (!mounted) {
        return;
      }
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(_normalizeError(error))));
    } finally {
      responseController.dispose();
    }
  }

  @override
  Widget build(BuildContext context) {
    return RefreshIndicator(
      onRefresh: _load,
      child: ListView(
        padding: const EdgeInsets.all(24),
        children: [
          const _ModuleHeaderCard(
            title: 'Tarefas',
            description: 'Acompanhe atividades enviadas pelo terapeuta e responda diretamente pelo app.',
          ),
          const SizedBox(height: 16),
          if (_loading)
            const _LoadingCard()
          else if (_error != null)
            _ErrorCard(message: _error!)
          else if (_items.isEmpty)
            const _EmptyCard(message: 'Nenhuma tarefa disponível no momento.')
          else
            ..._items.map((item) {
              final task = _mapValue(item, 'task');
              final linkedMaterials = _mapList(item, 'linked_materials');
              return Padding(
                padding: const EdgeInsets.only(bottom: 12),
                child: Card(
                  child: Padding(
                    padding: const EdgeInsets.all(20),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(_stringValue(task, ['title', 'material_title'], fallback: 'Tarefa'), style: Theme.of(context).textTheme.titleLarge),
                        const SizedBox(height: 8),
                        Text(_plainText(_stringValue(task, ['description', 'description_html'], fallback: 'Sem descrição.'))),
                        const SizedBox(height: 12),
                        Wrap(
                          spacing: 8,
                          runSpacing: 8,
                          children: [
                            _InfoChip(label: 'Status', value: _stringValue(task, ['status'], fallback: 'pending')),
                            if (_stringValue(task, ['due_date']).isNotEmpty)
                              _InfoChip(label: 'Entrega', value: _formatDateLabel(_stringValue(task, ['due_date']))),
                          ],
                        ),
                        if (linkedMaterials.isNotEmpty) ...[
                          const SizedBox(height: 8),
                          Text('Materiais vinculados: ${linkedMaterials.length}'),
                        ],
                        const SizedBox(height: 16),
                        OutlinedButton(
                          onPressed: () => _openTask(item),
                          child: Text(_isTreeOfLifeTask(task) ? 'Abrir jornada' : 'Abrir e responder'),
                        ),
                      ],
                    ),
                  ),
                ),
              );
            }),
        ],
      ),
    );
  }
}

class TreeOfLifePage extends StatefulWidget {
  const TreeOfLifePage({
    super.key,
    required this.apiClient,
    required this.analyticsService,
  });

  final ApiClient apiClient;
  final AnalyticsService analyticsService;

  @override
  State<TreeOfLifePage> createState() => _TreeOfLifePageState();
}

class _TreeOfLifePageState extends State<TreeOfLifePage> {
  List<Map<String, dynamic>> _items = <Map<String, dynamic>>[];
  String? _error;
  bool _loading = true;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() {
      _loading = true;
      _error = null;
    });

    try {
      final response = await widget.apiClient.fetchTasks();
      final items = _mapList(response['data'], 'items').where((item) => _isTreeOfLifeTask(_mapValue(item, 'task'))).toList();
      await widget.analyticsService.track('tree_of_life_view', context: 'tree_of_life');
      if (!mounted) {
        return;
      }
      setState(() {
        _items = items;
      });
    } catch (error) {
      if (!mounted) {
        return;
      }
      setState(() {
        _error = _normalizeError(error);
      });
    } finally {
      if (mounted) {
        setState(() {
          _loading = false;
        });
      }
    }
  }

  Future<void> _openTask(Map<String, dynamic> item) async {
    final task = _mapValue(item, 'task');
    final taskId = _intValue(task['id']);
    if (taskId <= 0) {
      return;
    }

    try {
      final response = await widget.apiClient.fetchTaskDetails(taskId: taskId);
      final data = _mapValue(response, 'data');
      final taskDetails = _mapValue(data, 'task');
      if (!mounted) {
        return;
      }

      await _showTreeOfLifeTaskSheet(
        parentContext: context,
        apiClient: widget.apiClient,
        analyticsService: widget.analyticsService,
        taskDetails: taskDetails,
        onCompleted: _load,
      );
    } catch (error) {
      if (!mounted) {
        return;
      }
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(_normalizeError(error))));
    }
  }

  @override
  Widget build(BuildContext context) {
    return RefreshIndicator(
      onRefresh: _load,
      child: ListView(
        padding: const EdgeInsets.all(24),
        children: [
          const _ModuleHeaderCard(
            title: 'Árvore da Vida',
            description: 'Responda sua jornada de forma guiada, em etapas, e envie o resultado completo ao terapeuta.',
          ),
          const SizedBox(height: 16),
          if (_loading)
            const _LoadingCard()
          else if (_error != null)
            _ErrorCard(message: _error!)
          else if (_items.isEmpty)
            const _EmptyCard(
              icon: Icons.park_outlined,
              title: 'Nenhuma jornada disponível',
              message: 'Quando o terapeuta enviar uma Árvore da Vida, ela aparecerá aqui pronta para preenchimento.',
              hint: 'As tarefas comuns continuam disponíveis no módulo Tarefas.',
            )
          else ...[
            _SectionTitle(title: 'Jornadas recebidas', count: _items.length),
            ..._items.map((item) {
              final task = _mapValue(item, 'task');
              final isDone = _stringValue(task, ['status'], fallback: 'pending') == 'done' || _intValue(task['is_active']) == 0;
              return Padding(
                padding: const EdgeInsets.only(bottom: 12),
                child: Card(
                  child: Padding(
                    padding: const EdgeInsets.all(20),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(_stringValue(task, ['title'], fallback: 'Árvore da Vida'), style: Theme.of(context).textTheme.titleLarge),
                        const SizedBox(height: 8),
                        Text(_plainText(_stringValue(task, ['description'], fallback: 'Jornada guiada da Árvore da Vida.'))),
                        const SizedBox(height: 12),
                        Wrap(
                          spacing: 8,
                          runSpacing: 8,
                          children: [
                            _InfoChip(label: 'Status', value: _stringValue(task, ['status'], fallback: 'pending')),
                            if (_stringValue(task, ['due_date']).isNotEmpty)
                              _InfoChip(label: 'Entrega', value: _formatDateLabel(_stringValue(task, ['due_date']))),
                            _InfoChip(label: 'Modo', value: isDone ? 'Concluída' : 'Disponível agora'),
                          ],
                        ),
                        const SizedBox(height: 16),
                        FilledButton.tonal(
                          onPressed: () => _openTask(item),
                          child: Text(isDone ? 'Ver envio' : 'Abrir jornada'),
                        ),
                      ],
                    ),
                  ),
                ),
              );
            }),
          ],
        ],
      ),
    );
  }
}

class BreathingGamePage extends StatefulWidget {
  const BreathingGamePage({
    super.key,
    required this.apiClient,
    required this.analyticsService,
    required this.onExit,
  });

  final ApiClient apiClient;
  final AnalyticsService analyticsService;
  final VoidCallback onExit;

  @override
  State<BreathingGamePage> createState() => _BreathingGamePageState();
}

class _BreathingGamePageState extends State<BreathingGamePage> {
  static const int _totalCycles = 3;
  static const Duration _prepareDelay = Duration(milliseconds: 300);

  int _sessionToken = 0;
  int _cycle = 0;
  int _seconds = 0;
  String _phase = 'Preparar';
  String _message = 'Inspire por 5 segundos';
  bool _running = false;
  bool _showIntro = true;
  double _ballY = 0.88;
  Duration _ballDuration = Duration.zero;
  Color _ballColor = Colors.white;
  double _messageOpacity = 1;

  @override
  void dispose() {
    _sessionToken += 1;
    super.dispose();
  }

  void _closePage() {
    _sessionToken += 1;
    if (mounted) {
      setState(() {
        _running = false;
        _showIntro = true;
        _cycle = 0;
        _seconds = 0;
        _phase = 'Preparar';
        _message = 'Inspire por 5 segundos';
        _messageOpacity = 1;
        _resetVisualState();
      });
    }
    widget.onExit();
  }

  Future<void> _showMessage(String text, {bool immediate = false}) async {
    if (!mounted || _sessionToken < 0) {
      return;
    }

    if (immediate || _message.isEmpty) {
      setState(() {
        _message = text;
        _messageOpacity = 1;
      });
      return;
    }

    setState(() {
      _messageOpacity = 0;
    });
    await Future<void>.delayed(const Duration(milliseconds: 260));
    if (!mounted) {
      return;
    }
    setState(() {
      _message = text;
      _messageOpacity = 1;
    });
  }

  void _resetVisualState() {
    _ballY = 0.88;
    _ballDuration = Duration.zero;
    _ballColor = Colors.white;
  }

  void _moveBallToTop() {
    setState(() {
      _ballY = -0.88;
      _ballDuration = const Duration(seconds: 5);
      _ballColor = const Color(0xFFBDE5FF);
    });
  }

  void _pinBallTop() {
    setState(() {
      _ballY = -0.88;
      _ballDuration = Duration.zero;
      _ballColor = const Color(0xFFBDE5FF);
    });
  }

  void _moveBallToBottom() {
    setState(() {
      _ballY = 0.88;
      _ballDuration = const Duration(seconds: 7);
      _ballColor = Colors.white;
    });
  }

  void _pinBallBottom() {
    setState(() {
      _ballY = 0.88;
      _ballDuration = Duration.zero;
      _ballColor = Colors.white;
    });
  }

  Future<bool> _runPhase({
    required int sessionToken,
    required int cycle,
    required String phase,
    required List<int> ticks,
    required Future<void> Function() beforeStart,
    required Future<void> Function(int tick) onTick,
  }) async {
    await beforeStart();

    for (final tick in ticks) {
      if (!mounted || sessionToken != _sessionToken) {
        return false;
      }

      setState(() {
        _cycle = cycle;
        _phase = phase;
        _seconds = tick;
      });

      await onTick(tick);

      await Future<void>.delayed(const Duration(seconds: 1));
    }

    return mounted && sessionToken == _sessionToken;
  }

  Future<void> _startSession() async {
    if (_running) {
      return;
    }

    _sessionToken += 1;
    final sessionToken = _sessionToken;
    setState(() {
      _running = true;
      _showIntro = false;
      _cycle = 0;
      _seconds = 0;
      _phase = 'Preparar';
      _message = 'Inspire por 5 segundos';
      _messageOpacity = 0;
      _resetVisualState();
    });

    await widget.analyticsService.track('breathing_game_start', context: 'breathing_game');
    await Future<void>.delayed(_prepareDelay);
    if (!mounted || sessionToken != _sessionToken) {
      return;
    }
    await _showMessage('Inspire por 5 segundos', immediate: true);

    for (var cycle = 1; cycle <= _totalCycles; cycle += 1) {
      if (cycle == 1) {
        final prepared = await _runPhase(
          sessionToken: sessionToken,
          cycle: 0,
          phase: 'Preparar',
          ticks: const [3, 2, 1, 0],
          beforeStart: () async {
            _pinBallBottom();
            await _showMessage('Inspire por 5 segundos', immediate: true);
          },
          onTick: (_) async {},
        );
        if (!prepared) {
          return;
        }
      }

      final inspired = await _runPhase(
        sessionToken: sessionToken,
        cycle: cycle,
        phase: 'Inspirar',
        ticks: const [1, 2, 3, 4, 5],
        beforeStart: () async {
          if (cycle != 1) {
            await _showMessage('Inspire por 5 segundos');
          }
          _moveBallToTop();
        },
        onTick: (tick) async {
          if (tick == 3) {
            await _showMessage('Segure por 4 segundos');
          }
        },
      );
      if (!inspired) {
        return;
      }

      final heldTop = await _runPhase(
        sessionToken: sessionToken,
        cycle: cycle,
        phase: 'Segure no topo',
        ticks: const [1, 2, 3, 4],
        beforeStart: () async {
          _pinBallTop();
        },
        onTick: (tick) async {
          if (tick == 2) {
            await _showMessage('Expirar por 7 segundos');
          }
        },
      );
      if (!heldTop) {
        return;
      }

      final expired = await _runPhase(
        sessionToken: sessionToken,
        cycle: cycle,
        phase: 'Expirar',
        ticks: const [7, 6, 5, 4, 3, 2, 1, 0],
        beforeStart: () async {
          await _showMessage('Expirar por 7 segundos', immediate: true);
          _moveBallToBottom();
        },
        onTick: (tick) async {
          if (tick == 5) {
            await _showMessage('Segure por 4 segundos');
          }
        },
      );
      if (!expired) {
        return;
      }

      final heldBottom = await _runPhase(
        sessionToken: sessionToken,
        cycle: cycle,
        phase: 'Segure embaixo',
        ticks: const [4, 3, 2, 1, 0],
        beforeStart: () async {
          _pinBallBottom();
        },
        onTick: (tick) async {
          if (tick == 2) {
            await _showMessage('Inspire por 5 segundos');
          }
        },
      );
      if (!heldBottom) {
        return;
      }
    }

    if (!mounted || sessionToken != _sessionToken) {
      return;
    }

    setState(() {
      _running = false;
      _showIntro = true;
      _cycle = 0;
      _seconds = 0;
      _phase = 'Preparar';
      _message = 'Inspire por 5 segundos';
      _messageOpacity = 1;
      _resetVisualState();
    });
    await widget.analyticsService.track('breathing_game_complete', context: 'breathing_game');
    if (mounted) {
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Sessão de respiração concluída.')));
    }
  }

  @override
  Widget build(BuildContext context) {
    final textTheme = Theme.of(context).textTheme;

    return DecoratedBox(
      decoration: const BoxDecoration(
        gradient: LinearGradient(
          begin: Alignment.topCenter,
          end: Alignment.bottomCenter,
          colors: [Color(0xFFFFFFFF), Color(0xFF5EA9FF)],
        ),
      ),
      child: SafeArea(
        child: SizedBox.expand(
          child: Stack(
            children: [
              Padding(
                padding: const EdgeInsets.fromLTRB(18, 18, 18, 24),
                child: Column(
                  children: [
                    Row(
                      children: [
                        IconButton.filledTonal(
                          onPressed: _closePage,
                          icon: const Icon(Icons.close),
                        ),
                        const Spacer(),
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
                          decoration: BoxDecoration(
                            color: Colors.white.withValues(alpha: 0.58),
                            borderRadius: BorderRadius.circular(999),
                          ),
                          child: Text('Ciclo $_cycle/$_totalCycles', style: textTheme.labelLarge?.copyWith(fontWeight: FontWeight.w700)),
                        ),
                      ],
                    ),
                    const SizedBox(height: 12),
                    Expanded(
                      child: ClipRRect(
                        borderRadius: BorderRadius.circular(32),
                        child: Stack(
                          fit: StackFit.expand,
                          children: [
                            AnimatedOpacity(
                              duration: const Duration(milliseconds: 350),
                              opacity: _showIntro ? 1 : 0,
                              child: IgnorePointer(
                                ignoring: !_showIntro,
                                child: Padding(
                                  padding: const EdgeInsets.symmetric(horizontal: 28, vertical: 24),
                                  child: Column(
                                    mainAxisAlignment: MainAxisAlignment.center,
                                    children: [
                                      Container(
                                        padding: const EdgeInsets.all(22),
                                        decoration: BoxDecoration(
                                          color: Colors.white.withValues(alpha: 0.42),
                                          borderRadius: BorderRadius.circular(26),
                                          boxShadow: const [
                                            BoxShadow(color: Color(0x1F124267), blurRadius: 30, offset: Offset(0, 12)),
                                          ],
                                        ),
                                        child: Text(
                                          'A respiração é fundamental não apenas para manter a vida, mas também para regular o corpo e a mente. Respirar corretamente ajuda a reduzir o estresse, melhorar o sono, fortalecer os pulmões e otimizar a produção de energia. Respire comigo!',
                                          textAlign: TextAlign.center,
                                          style: textTheme.titleMedium?.copyWith(color: const Color(0xFF114163), height: 1.65, fontWeight: FontWeight.w600),
                                        ),
                                      ),
                                      const SizedBox(height: 28),
                                      FilledButton.tonal(
                                        onPressed: _running ? null : _startSession,
                                        style: FilledButton.styleFrom(
                                          padding: const EdgeInsets.symmetric(horizontal: 36, vertical: 18),
                                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(999)),
                                        ),
                                        child: const Text('Iniciar'),
                                      ),
                                    ],
                                  ),
                                ),
                              ),
                            ),
                            AnimatedOpacity(
                              duration: const Duration(milliseconds: 350),
                              opacity: _showIntro ? 0 : 1,
                              child: IgnorePointer(
                                ignoring: _showIntro,
                                child: Padding(
                                  padding: const EdgeInsets.fromLTRB(22, 22, 22, 28),
                                  child: Stack(
                                    alignment: Alignment.center,
                                    children: [
                                      Positioned(
                                        top: 0,
                                        left: 0,
                                        right: 0,
                                        child: Row(
                                          children: [
                                            Expanded(
                                              child: _BreathingInfoCard(label: 'Fase', value: _phase),
                                            ),
                                            const SizedBox(width: 10),
                                            Expanded(
                                              child: _BreathingInfoCard(label: 'Tempo', value: '${_seconds}s'),
                                            ),
                                          ],
                                        ),
                                      ),
                                      Align(
                                        child: Container(
                                          width: 2,
                                          margin: const EdgeInsets.symmetric(vertical: 72),
                                          decoration: BoxDecoration(
                                            borderRadius: BorderRadius.circular(999),
                                            gradient: const LinearGradient(
                                              begin: Alignment.topCenter,
                                              end: Alignment.bottomCenter,
                                              colors: [Color(0xC0FFFFFF), Color(0x33FFFFFF)],
                                            ),
                                          ),
                                        ),
                                      ),
                                      Container(
                                        width: 110,
                                        height: 110,
                                        decoration: BoxDecoration(
                                          shape: BoxShape.circle,
                                          color: const Color(0x1F0A3C61),
                                          border: Border.all(color: Colors.white.withValues(alpha: 0.78), width: 4),
                                          boxShadow: const [
                                            BoxShadow(color: Color(0x2A093554), blurRadius: 22, offset: Offset(0, 10)),
                                          ],
                                        ),
                                        alignment: Alignment.center,
                                        child: Text(
                                          '$_seconds',
                                          style: textTheme.displaySmall?.copyWith(color: const Color(0xFF0D3554), fontWeight: FontWeight.w800),
                                        ),
                                      ),
                                      AnimatedAlign(
                                        duration: _ballDuration,
                                        curve: Curves.linear,
                                        alignment: Alignment(0, _ballY),
                                        child: Container(
                                          width: 72,
                                          height: 72,
                                          decoration: BoxDecoration(
                                            shape: BoxShape.circle,
                                            color: _ballColor,
                                            boxShadow: const [
                                              BoxShadow(color: Color(0x4D1E4D72), blurRadius: 18, offset: Offset(0, 10)),
                                            ],
                                          ),
                                        ),
                                      ),
                                      Align(
                                        alignment: const Alignment(0, -0.34),
                                        child: IgnorePointer(
                                          child: Padding(
                                            padding: const EdgeInsets.symmetric(horizontal: 28),
                                            child: AnimatedOpacity(
                                              duration: const Duration(milliseconds: 260),
                                              opacity: _messageOpacity,
                                              child: Text(
                                                _message,
                                                textAlign: TextAlign.center,
                                                style: textTheme.titleMedium?.copyWith(
                                                  color: const Color(0xFF0C2F48),
                                                  fontWeight: FontWeight.w700,
                                                  shadows: const [
                                                    Shadow(color: Color(0x66FFFFFF), blurRadius: 10),
                                                  ],
                                                ),
                                              ),
                                            ),
                                          ),
                                        ),
                                      ),
                                    ],
                                  ),
                                ),
                              ),
                            ),
                          ],
                        ),
                      ),
                    ),
                  ],
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

class _BreathingInfoCard extends StatelessWidget {
  const _BreathingInfoCard({required this.label, required this.value});

  final String label;
  final String value;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
      decoration: BoxDecoration(
        color: Colors.white.withValues(alpha: 0.52),
        borderRadius: BorderRadius.circular(16),
        boxShadow: const [
          BoxShadow(color: Color(0x1F104770), blurRadius: 14, offset: Offset(0, 6)),
        ],
      ),
      child: Column(
        children: [
          Text(label, style: Theme.of(context).textTheme.labelSmall?.copyWith(color: const Color(0xFF2F5F84), fontWeight: FontWeight.w700)),
          const SizedBox(height: 2),
          Text(value, textAlign: TextAlign.center, style: Theme.of(context).textTheme.titleSmall?.copyWith(color: const Color(0xFF0D3654), fontWeight: FontWeight.w700)),
        ],
      ),
    );
  }
}

Future<void> _showTreeOfLifeTaskSheet({
  required BuildContext parentContext,
  required ApiClient apiClient,
  required AnalyticsService analyticsService,
  required Map<String, dynamic> taskDetails,
  required Future<void> Function() onCompleted,
  String analyticsContext = 'tree_of_life',
}) async {
  final taskId = _intValue(taskDetails['id']);
  final structure = _decodeJsonObject(taskDetails['content_json']);
  final sections = _listFromDynamic(structure['sections']);
  final finalSection = _jsonObjectFromDynamic(structure['final_section']);
  final finalBlocks = _listFromDynamic(finalSection['blocks']);
  final isDone = _stringValue(taskDetails, ['status'], fallback: 'pending') == 'done' || _intValue(taskDetails['is_active']) == 0;

  if (taskId <= 0 || (sections.isEmpty && finalBlocks.isEmpty && !isDone)) {
    if (parentContext.mounted) {
      ScaffoldMessenger.of(parentContext).showSnackBar(const SnackBar(content: Text('Não foi possível carregar a estrutura da Árvore da Vida.')));
    }
    return;
  }

  final sectionControllers = <String, List<TextEditingController>>{};
  for (final section in sections) {
    final sectionKey = _stringValue(section, ['key']);
    if (sectionKey.isEmpty) {
      continue;
    }
    final questions = _stringListFromDynamic(section['questions']);
    sectionControllers[sectionKey] = List<TextEditingController>.generate(questions.length, (_) => TextEditingController());
  }

  final finalControllers = <String, TextEditingController>{};
  for (final block in finalBlocks) {
    final blockKey = _stringValue(block, ['key']);
    if (blockKey.isEmpty) {
      continue;
    }
    finalControllers[blockKey] = TextEditingController();
  }

  try {
    await showModalBottomSheet<void>(
      context: parentContext,
      isScrollControlled: true,
      useSafeArea: true,
      builder: (context) {
        var saving = false;

        return StatefulBuilder(
          builder: (context, setModalState) {
            Future<void> submit() async {
              final answers = <String, List<String>>{};
              for (final section in sections) {
                final sectionKey = _stringValue(section, ['key']);
                final controllers = sectionControllers[sectionKey] ?? const <TextEditingController>[];
                answers[sectionKey] = controllers.map((controller) => controller.text.trim()).toList();
              }

              final finalReflections = <String, String>{};
              for (final entry in finalControllers.entries) {
                final text = entry.value.text.trim();
                if (text.length < 10) {
                  ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Preencha passado, presente e futuro com pelo menos uma reflexão curta.')));
                  return;
                }
                finalReflections[entry.key] = _textToHtml(text);
              }

              setModalState(() {
                saving = true;
              });

              try {
                await apiClient.completeVirtualTask(
                  taskId: taskId,
                  answers: answers,
                  finalReflections: finalReflections,
                );
                await analyticsService.track('tree_of_life_submit', context: analyticsContext, payload: {'task_id': taskId});
                if (!parentContext.mounted || !context.mounted) {
                  return;
                }
                Navigator.of(context).pop();
                await onCompleted();
                if (!parentContext.mounted) {
                  return;
                }
                ScaffoldMessenger.of(parentContext).showSnackBar(const SnackBar(content: Text('Árvore da Vida enviada com sucesso.')));
              } catch (error) {
                if (parentContext.mounted) {
                  ScaffoldMessenger.of(parentContext).showSnackBar(SnackBar(content: Text(_normalizeError(error))));
                }
              } finally {
                if (context.mounted) {
                  setModalState(() {
                    saving = false;
                  });
                }
              }
            }

            return Padding(
              padding: EdgeInsets.fromLTRB(24, 16, 24, 24 + MediaQuery.of(context).viewInsets.bottom),
              child: SingleChildScrollView(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Text(_stringValue(taskDetails, ['title'], fallback: 'Árvore da Vida'), style: Theme.of(context).textTheme.titleLarge),
                    const SizedBox(height: 8),
                    Wrap(
                      spacing: 8,
                      runSpacing: 8,
                      children: [
                        _InfoChip(label: 'Status', value: _stringValue(taskDetails, ['status'], fallback: 'pending')),
                        if (_stringValue(taskDetails, ['due_date']).isNotEmpty)
                          _InfoChip(label: 'Entrega', value: _formatDateLabel(_stringValue(taskDetails, ['due_date']))),
                        _InfoChip(label: 'Etapas', value: '${sections.length + finalBlocks.length}'),
                      ],
                    ),
                    const SizedBox(height: 12),
                    Text(_plainText(_stringValue(taskDetails, ['description'], fallback: 'Jornada guiada da Árvore da Vida.'))),
                    const SizedBox(height: 16),
                    if (isDone) ...[
                      Text('Resposta enviada', style: Theme.of(context).textTheme.titleMedium),
                      const SizedBox(height: 8),
                      Card(
                        margin: EdgeInsets.zero,
                        child: Padding(
                          padding: const EdgeInsets.all(16),
                          child: Text(_plainText(_stringValue(taskDetails, ['patient_response_html'], fallback: 'Resposta já enviada ao terapeuta.'))),
                        ),
                      ),
                    ] else ...[
                      ...sections.map((section) {
                        final sectionKey = _stringValue(section, ['key']);
                        final sectionTitle = _stringValue(section, ['title'], fallback: 'Seção');
                        final helperText = _stringValue(section, ['helper_text']);
                        final questions = _stringListFromDynamic(section['questions']);
                        final controllers = sectionControllers[sectionKey] ?? const <TextEditingController>[];

                        return Padding(
                          padding: const EdgeInsets.only(bottom: 12),
                          child: Card(
                            child: Padding(
                              padding: const EdgeInsets.all(16),
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Text(sectionTitle, style: Theme.of(context).textTheme.titleMedium),
                                  if (helperText.isNotEmpty) ...[
                                    const SizedBox(height: 8),
                                    Text(helperText),
                                  ],
                                  for (var index = 0; index < questions.length; index += 1) ...[
                                    const SizedBox(height: 12),
                                    Text(questions[index], style: Theme.of(context).textTheme.bodyMedium?.copyWith(fontWeight: FontWeight.w600)),
                                    const SizedBox(height: 8),
                                    TextField(
                                      controller: controllers[index],
                                      minLines: 3,
                                      maxLines: 5,
                                      decoration: InputDecoration(
                                        labelText: 'Resposta ${index + 1}',
                                        alignLabelWithHint: true,
                                      ),
                                    ),
                                  ],
                                ],
                              ),
                            ),
                          ),
                        );
                      }),
                      if (finalBlocks.isNotEmpty) ...[
                        const SizedBox(height: 8),
                        Text('Encerramento da jornada', style: Theme.of(context).textTheme.titleMedium),
                        const SizedBox(height: 8),
                        ...finalBlocks.map((block) {
                          final blockKey = _stringValue(block, ['key']);
                          final blockTitle = _stringValue(block, ['title'], fallback: 'Reflexão final');
                          final prompts = _stringListFromDynamic(block['questions']);
                          final controller = finalControllers[blockKey]!;
                          return Padding(
                            padding: const EdgeInsets.only(bottom: 12),
                            child: Card(
                              child: Padding(
                                padding: const EdgeInsets.all(16),
                                child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    Text(blockTitle, style: Theme.of(context).textTheme.titleMedium),
                                    if (prompts.isNotEmpty) ...[
                                      const SizedBox(height: 8),
                                      ...prompts.map((prompt) => Padding(
                                            padding: const EdgeInsets.only(bottom: 4),
                                            child: Text(prompt),
                                          )),
                                    ],
                                    const SizedBox(height: 8),
                                    TextField(
                                      controller: controller,
                                      minLines: 4,
                                      maxLines: 7,
                                      decoration: const InputDecoration(
                                        labelText: 'Minha reflexão',
                                        helperText: 'Escreva com detalhes suficientes para contextualizar sua jornada.',
                                        alignLabelWithHint: true,
                                      ),
                                    ),
                                  ],
                                ),
                              ),
                            ),
                          );
                        }),
                        FilledButton(
                          onPressed: saving ? null : submit,
                          child: saving
                              ? const SizedBox(width: 22, height: 22, child: CircularProgressIndicator(strokeWidth: 2.2, color: Colors.white))
                              : const Text('Enviar Árvore da Vida'),
                        ),
                      ],
                    ],
                  ],
                ),
              ),
            );
          },
        );
      },
    );
  } finally {
    for (final controllers in sectionControllers.values) {
      for (final controller in controllers) {
        controller.dispose();
      }
    }
    for (final controller in finalControllers.values) {
      controller.dispose();
    }
  }
}

class MaterialsPage extends StatefulWidget {
  const MaterialsPage({
    super.key,
    required this.apiClient,
    required this.analyticsService,
  });

  final ApiClient apiClient;
  final AnalyticsService analyticsService;

  @override
  State<MaterialsPage> createState() => _MaterialsPageState();
}

class _MaterialsPageState extends State<MaterialsPage> {
  List<Map<String, dynamic>> _deliveries = <Map<String, dynamic>>[];
  List<Map<String, dynamic>> _tasks = <Map<String, dynamic>>[];
  Map<int, List<Map<String, dynamic>>> _assetsByMaterial = <int, List<Map<String, dynamic>>>{};
  Map<int, List<Map<String, dynamic>>> _taskLinkedMaterials = <int, List<Map<String, dynamic>>>{};
  String? _error;
  bool _loading = true;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() {
      _loading = true;
      _error = null;
    });

    try {
      final response = await widget.apiClient.fetchMaterials();
      final data = _mapValue(response, 'data');
      await widget.analyticsService.track('materials_view', context: 'materials');

      if (!mounted) {
        return;
      }

      setState(() {
        _deliveries = _mapList(data, 'deliveries');
        _tasks = _mapList(data, 'tasks');
        _assetsByMaterial = _mapOfLists(data['assets_by_material']);
        _taskLinkedMaterials = _mapOfLists(data['task_linked_materials']);
      });
    } catch (error) {
      if (!mounted) {
        return;
      }
      setState(() {
        _error = _normalizeError(error);
      });
    } finally {
      if (mounted) {
        setState(() {
          _loading = false;
        });
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return RefreshIndicator(
      onRefresh: _load,
      child: ListView(
        padding: const EdgeInsets.all(24),
        children: [
          const _ModuleHeaderCard(
            title: 'Materiais',
            description: 'Acesse conteúdos liberados pelo terapeuta e abra arquivos e links do tratamento.',
          ),
          const SizedBox(height: 16),
          if (_loading)
            const _LoadingCard()
          else if (_error != null)
            _ErrorCard(message: _error!)
          else ...[
            _SectionTitle(title: 'Entregas diretas', count: _deliveries.length),
            if (_deliveries.isEmpty)
              const _EmptyCard(
                icon: Icons.folder_open_outlined,
                title: 'Nenhum material direto por aqui',
                message: 'Quando seu terapeuta liberar PDFs, áudios ou links para o seu perfil, eles aparecerão nesta área.',
                hint: 'Puxe a tela para baixo para verificar novas liberações.',
              )
            else
              ..._deliveries.map((delivery) {
                final materialId = _intValue(delivery['material_id']);
                final assets = _assetsByMaterial[materialId] ?? <Map<String, dynamic>>[];
                return Padding(
                  padding: const EdgeInsets.only(bottom: 12),
                  child: Card(
                    child: Padding(
                      padding: const EdgeInsets.all(20),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(_stringValue(delivery, ['material_title'], fallback: 'Material'), style: Theme.of(context).textTheme.titleLarge),
                          const SizedBox(height: 8),
                          Text('Tipo: ${_stringValue(delivery, ['material_type'], fallback: 'conteúdo')}'),
                          if (_stringValue(delivery, ['message']).isNotEmpty) ...[
                            const SizedBox(height: 8),
                            Text(_stringValue(delivery, ['message'])),
                          ],
                          if (assets.isNotEmpty) ...[
                            const SizedBox(height: 12),
                            Wrap(
                              spacing: 8,
                              runSpacing: 8,
                              children: assets.map((asset) {
                                return ActionChip(
                                  label: Text(_stringValue(asset, ['file_name', 'asset_type'], fallback: 'Abrir arquivo')),
                                  onPressed: () => _launchUrl(context, _stringValue(asset, ['view_url'])),
                                );
                              }).toList(),
                            ),
                          ],
                        ],
                      ),
                    ),
                  ),
                );
              }),
            const SizedBox(height: 16),
            _SectionTitle(title: 'Materiais vinculados a tarefas', count: _tasks.length),
            if (_tasks.isEmpty)
              const _EmptyCard(
                icon: Icons.assignment_outlined,
                title: 'Sem materiais vinculados a tarefas',
                message: 'As próximas atividades que dependerem de apoio complementar aparecerão aqui com seus anexos e referências.',
                hint: 'Isso costuma ser preenchido quando uma tarefa vem acompanhada de leitura ou exercício extra.',
              )
            else
              ..._tasks.map((task) {
                final taskId = _intValue(task['id']);
                final linked = _taskLinkedMaterials[taskId] ?? <Map<String, dynamic>>[];
                return Padding(
                  padding: const EdgeInsets.only(bottom: 12),
                  child: Card(
                    child: Padding(
                      padding: const EdgeInsets.all(20),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(_stringValue(task, ['title'], fallback: 'Tarefa'), style: Theme.of(context).textTheme.titleLarge),
                          const SizedBox(height: 8),
                          Text(_plainText(_stringValue(task, ['description', 'description_html'], fallback: 'Sem descrição.'))),
                          const SizedBox(height: 12),
                          if (linked.isEmpty)
                            const Text('Nenhum material extra vinculado.')
                          else
                            Wrap(
                              spacing: 8,
                              runSpacing: 8,
                              children: linked.map((material) => Chip(label: Text(_stringValue(material, ['title'], fallback: 'Material')))).toList(),
                            ),
                        ],
                      ),
                    ),
                  ),
                );
              }),
          ],
        ],
      ),
    );
  }
}

class BooksPage extends StatefulWidget {
  const BooksPage({
    super.key,
    required this.apiClient,
    required this.analyticsService,
  });

  final ApiClient apiClient;
  final AnalyticsService analyticsService;

  @override
  State<BooksPage> createState() => _BooksPageState();
}

class _BooksPageState extends State<BooksPage> {
  List<Map<String, dynamic>> _items = <Map<String, dynamic>>[];
  final TextEditingController _searchController = TextEditingController();
  Timer? _searchDebounce;
  String? _error;
  String _searchTerm = '';
  bool _showFavoritesOnly = false;
  bool _loading = true;

  @override
  void initState() {
    super.initState();
    _load();
  }

  @override
  void dispose() {
    _searchDebounce?.cancel();
    _searchController.dispose();
    super.dispose();
  }

  Future<void> _load() async {
    setState(() {
      _loading = true;
      _error = null;
    });

    try {
      final response = await widget.apiClient.fetchBooks(search: _searchTerm);
      await widget.analyticsService.track('books_view', context: 'books');
      if (!mounted) {
        return;
      }
      setState(() {
        _items = _mapList(response['data'], 'items');
      });
    } catch (error) {
      if (!mounted) {
        return;
      }
      setState(() {
        _error = _normalizeError(error);
      });
    } finally {
      if (mounted) {
        setState(() {
          _loading = false;
        });
      }
    }
  }

  Future<void> _toggleFavorite(Map<String, dynamic> item) async {
    final bookId = _intValue(item['id']);
    if (bookId <= 0) {
      return;
    }

    try {
      final response = await widget.apiClient.toggleBookFavorite(bookId: bookId);
      final data = _mapValue(response, 'data');
      final isFavorite = data['is_favorite'] == true;
      if (!mounted) {
        return;
      }
      setState(() {
        item['is_favorite'] = isFavorite;
      });
    } catch (error) {
      if (!mounted) {
        return;
      }
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(_normalizeError(error))));
    }
  }

  void _handleSearchChanged(String value) {
    _searchDebounce?.cancel();
    _searchDebounce = Timer(const Duration(milliseconds: 350), () {
      if (!mounted) {
        return;
      }
      final normalized = value.trim();
      if (normalized == _searchTerm) {
        return;
      }
      setState(() {
        _searchTerm = normalized;
      });
      _load();
    });
  }

  @override
  Widget build(BuildContext context) {
    final visibleBooks = _items.where((book) {
      final hasPdf = _stringValue(book, ['pdf_url']).trim().isNotEmpty;
      final favoriteMatch = !_showFavoritesOnly || book['is_favorite'] == true;
      return hasPdf && favoriteMatch;
    }).toList();
    final hiddenBooksCount = _items.length - visibleBooks.length;
    final favoriteCount = _items.where((book) => book['is_favorite'] == true).length;

    return RefreshIndicator(
      onRefresh: _load,
      child: ListView(
        padding: const EdgeInsets.all(24),
        children: [
          const _ModuleHeaderCard(
            title: 'Livros',
            description: 'Abra sua biblioteca terapêutica e salve conteúdos em Meus conteúdos.',
          ),
          const SizedBox(height: 16),
          Card(
            child: Padding(
              padding: const EdgeInsets.all(20),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  TextField(
                    controller: _searchController,
                    onChanged: _handleSearchChanged,
                    decoration: InputDecoration(
                      prefixIcon: const Icon(Icons.search),
                      labelText: 'Buscar livros',
                      hintText: 'Digite o título do livro...',
                      suffixIcon: _searchTerm.isEmpty
                          ? null
                          : IconButton(
                              onPressed: () {
                                _searchController.clear();
                                _handleSearchChanged('');
                              },
                              icon: const Icon(Icons.close),
                            ),
                    ),
                  ),
                  const SizedBox(height: 16),
                  Wrap(
                    spacing: 12,
                    runSpacing: 12,
                    children: [
                      _InfoChip(label: 'Biblioteca', value: '${_items.length}'),
                      _InfoChip(label: 'Favoritos', value: '$favoriteCount'),
                      FilterChip(
                        label: const Text('Somente salvos'),
                        selected: _showFavoritesOnly,
                        onSelected: (value) {
                          setState(() {
                            _showFavoritesOnly = value;
                          });
                        },
                      ),
                    ],
                  ),
                ],
              ),
            ),
          ),
          const SizedBox(height: 16),
          if (_loading)
            const _LoadingCard()
          else if (_error != null)
            _ErrorCard(message: _error!)
          else if (_items.isEmpty)
            const _EmptyCard(
              icon: Icons.menu_book_outlined,
              title: 'Sua biblioteca ainda está vazia',
              message: 'Assim que seu terapeuta publicar um PDF para o seu perfil, ele aparecerá aqui para leitura e favoritar.',
              hint: 'Puxe a tela para baixo para atualizar quando um novo livro for liberado.',
            )
          else if (visibleBooks.isEmpty)
            _EmptyCard(
              icon: Icons.picture_as_pdf_outlined,
              title: _showFavoritesOnly ? 'Nenhum livro salvo agora' : 'Nenhum livro com PDF disponível',
              message: _showFavoritesOnly
                  ? 'Você ainda não salvou nenhum livro em Meus conteúdos.'
                  : hiddenBooksCount == 1
                      ? 'Há 1 livro cadastrado para o seu perfil, mas ele ainda não tem um PDF utilizável para abertura no app.'
                      : 'Há $hiddenBooksCount livros cadastrados para o seu perfil, mas nenhum deles tem um PDF utilizável para abertura no app.',
              hint: _showFavoritesOnly
                  ? 'Toque no marcador de um livro disponível para salvá-lo e encontrá-lo depois com mais facilidade.'
                  : 'Quando o arquivo for publicado corretamente pelo terapeuta, ele passará a aparecer aqui.',
            )
          else
            ...visibleBooks.map((book) {
              final isFavorite = book['is_favorite'] == true;
              return Padding(
                padding: const EdgeInsets.only(bottom: 12),
                child: Card(
                  child: Padding(
                    padding: const EdgeInsets.all(20),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Row(
                          children: [
                            Expanded(child: Text(_stringValue(book, ['title'], fallback: 'Livro'), style: Theme.of(context).textTheme.titleLarge)),
                            IconButton(
                              onPressed: () => _toggleFavorite(book),
                              tooltip: isFavorite ? 'Remover de Meus conteúdos' : 'Salvar em Meus conteúdos',
                              icon: Icon(isFavorite ? Icons.bookmark : Icons.bookmark_border),
                            ),
                          ],
                        ),
                        const SizedBox(height: 8),
                        Text(_plainText(_stringValue(book, ['description_text', 'description_html'], fallback: 'Sem descrição.'))),
                        const SizedBox(height: 12),
                        Wrap(
                          spacing: 8,
                          runSpacing: 8,
                          children: [
                            _InfoChip(label: 'Salvo', value: isFavorite ? 'Sim' : 'Não'),
                            if (_stringValue(book, ['updated_at']).isNotEmpty)
                              _InfoChip(label: 'Disponibilizado em', value: _formatDateLabel(_stringValue(book, ['updated_at']))),
                          ],
                        ),
                        const SizedBox(height: 16),
                        Row(
                          children: [
                            Expanded(
                              child: FilledButton.tonal(
                                onPressed: () => _launchUrl(context, _stringValue(book, ['pdf_url'])),
                                child: const Text('Visualizar'),
                              ),
                            ),
                            const SizedBox(width: 12),
                            Expanded(
                              child: OutlinedButton.icon(
                                onPressed: () => _toggleFavorite(book),
                                icon: Icon(isFavorite ? Icons.bookmark_remove_outlined : Icons.bookmark_add_outlined),
                                label: Text(isFavorite ? 'Remover' : 'Salvar'),
                              ),
                            ),
                          ],
                        ),
                      ],
                    ),
                  ),
                ),
              );
            }),
        ],
      ),
    );
  }
}

class VideosPage extends StatefulWidget {
  const VideosPage({
    super.key,
    required this.apiClient,
    required this.analyticsService,
  });

  final ApiClient apiClient;
  final AnalyticsService analyticsService;

  @override
  State<VideosPage> createState() => _VideosPageState();
}

class _VideosPageState extends State<VideosPage> {
  List<Map<String, dynamic>> _items = <Map<String, dynamic>>[];
  String? _error;
  bool _loading = true;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() {
      _loading = true;
      _error = null;
    });

    try {
      final response = await widget.apiClient.fetchVideos();
      await widget.analyticsService.track('videos_view', context: 'videos');
      if (!mounted) {
        return;
      }
      setState(() {
        _items = _mapList(response['data'], 'items');
      });
    } catch (error) {
      if (!mounted) {
        return;
      }
      setState(() {
        _error = _normalizeError(error);
      });
    } finally {
      if (mounted) {
        setState(() {
          _loading = false;
        });
      }
    }
  }

  Future<void> _toggleFavorite(Map<String, dynamic> video) async {
    final videoId = _intValue(video['id']);
    if (videoId <= 0) {
      return;
    }

    try {
      final response = await widget.apiClient.toggleVideoFavorite(videoId: videoId);
      final data = _mapValue(response, 'data');
      if (!mounted) {
        return;
      }
      setState(() {
        video['is_favorite'] = data['is_favorite'] == true;
      });
    } catch (error) {
      if (!mounted) {
        return;
      }
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(_normalizeError(error))));
    }
  }

  Future<void> _openVideoDetails(Map<String, dynamic> video) async {
    final videoId = _intValue(video['id']);
    if (videoId <= 0) {
      return;
    }

    final commentController = TextEditingController();
    int selectedRating = 0;
    bool saving = false;

    try {
      final response = await widget.apiClient.fetchVideoDetails(videoId: videoId);
      final data = _mapValue(response, 'data');
      final details = _mapValue(data, 'video');
      final comments = _mapList(data, 'comments');
      selectedRating = _intValue(data['my_rating']);

      if (!mounted) {
        return;
      }

      await showModalBottomSheet<void>(
        context: context,
        isScrollControlled: true,
        useSafeArea: true,
        builder: (context) {
          return StatefulBuilder(
            builder: (context, setModalState) {
              Future<void> saveRating(int rating) async {
                setModalState(() {
                  saving = true;
                });
                try {
                  await widget.apiClient.rateVideo(videoId: videoId, rating: rating);
                  await widget.analyticsService.track('video_rate', context: 'videos', payload: {'video_id': videoId, 'rating': rating});
                  setModalState(() {
                    selectedRating = rating;
                  });
                } catch (error) {
                  if (mounted) {
                    ScaffoldMessenger.of(this.context).showSnackBar(SnackBar(content: Text(_normalizeError(error))));
                  }
                } finally {
                  if (mounted) {
                    setModalState(() {
                      saving = false;
                    });
                  }
                }
              }

              Future<void> saveComment() async {
                final comment = commentController.text.trim();
                if (comment.isEmpty) {
                  return;
                }

                setModalState(() {
                  saving = true;
                });
                try {
                  await widget.apiClient.commentVideo(videoId: videoId, comment: comment);
                  await widget.analyticsService.track('video_comment', context: 'videos', payload: {'video_id': videoId});
                  commentController.clear();
                  if (!mounted || !context.mounted) {
                    return;
                  }
                  Navigator.of(context).pop();
                  await _load();
                  if (!mounted) {
                    return;
                  }
                  ScaffoldMessenger.of(this.context).showSnackBar(const SnackBar(content: Text('Comentário salvo com sucesso.')));
                } catch (error) {
                  if (mounted) {
                    ScaffoldMessenger.of(this.context).showSnackBar(SnackBar(content: Text(_normalizeError(error))));
                  }
                } finally {
                  if (mounted) {
                    setModalState(() {
                      saving = false;
                    });
                  }
                }
              }

              return Padding(
                padding: EdgeInsets.fromLTRB(24, 16, 24, 24 + MediaQuery.of(context).viewInsets.bottom),
                child: SingleChildScrollView(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Text(_stringValue(details, ['title'], fallback: 'Vídeo'), style: Theme.of(context).textTheme.titleLarge),
                      const SizedBox(height: 8),
                      Text(_plainText(_stringValue(details, ['description_text'], fallback: 'Sem descrição.'))),
                      const SizedBox(height: 16),
                      Wrap(
                        spacing: 8,
                        runSpacing: 8,
                        children: [
                          _InfoChip(label: 'Origem', value: _stringValue(details, ['source_type'], fallback: 'upload')),
                          _InfoChip(label: 'Média', value: _stringValue(details, ['average_rating'], fallback: '0')),
                          _InfoChip(label: 'Comentários', value: _stringValue(details, ['comment_count'], fallback: '0')),
                        ],
                      ),
                      const SizedBox(height: 12),
                      Text('Sua avaliação', style: Theme.of(context).textTheme.titleMedium),
                      const SizedBox(height: 4),
                      Wrap(
                        spacing: 8,
                        children: List<Widget>.generate(5, (index) {
                          final rating = index + 1;
                          return IconButton(
                            onPressed: saving ? null : () => saveRating(rating),
                            icon: Icon(rating <= selectedRating ? Icons.star : Icons.star_border),
                          );
                        }),
                      ),
                      const SizedBox(height: 8),
                      FilledButton.tonal(
                        onPressed: () => _launchUrl(context, _resolveVideoUrl(details)),
                        child: const Text('Assistir vídeo'),
                      ),
                      const SizedBox(height: 16),
                      TextField(
                        controller: commentController,
                        minLines: 3,
                        maxLines: 6,
                        decoration: const InputDecoration(
                          labelText: 'Seu comentário',
                          helperText: 'Compartilhe como esse vídeo dialogou com seu processo.',
                          alignLabelWithHint: true,
                        ),
                      ),
                      const SizedBox(height: 12),
                      FilledButton(
                        onPressed: saving ? null : saveComment,
                        child: saving
                            ? const SizedBox(width: 22, height: 22, child: CircularProgressIndicator(strokeWidth: 2.2, color: Colors.white))
                            : const Text('Salvar comentário'),
                      ),
                      const SizedBox(height: 16),
                      Text('Comentários', style: Theme.of(context).textTheme.titleMedium),
                      const SizedBox(height: 8),
                      if (comments.isEmpty)
                        const Text('Nenhum comentário ainda.')
                      else
                        ...comments.map(
                          (comment) => Padding(
                            padding: const EdgeInsets.only(bottom: 10),
                            child: Card(
                              margin: EdgeInsets.zero,
                              child: Padding(
                                padding: const EdgeInsets.all(12),
                                child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    Text(_stringValue(comment, ['patient_name', 'first_name'], fallback: 'Paciente'), style: Theme.of(context).textTheme.labelLarge),
                                    const SizedBox(height: 4),
                                    Text(_stringValue(comment, ['comment_text'], fallback: '')),
                                  ],
                                ),
                              ),
                            ),
                          ),
                        ),
                    ],
                  ),
                ),
              );
            },
          );
        },
      );
    } catch (error) {
      if (!mounted) {
        return;
      }
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(_normalizeError(error))));
    } finally {
      commentController.dispose();
    }
  }

  @override
  Widget build(BuildContext context) {
    return RefreshIndicator(
      onRefresh: _load,
      child: ListView(
        padding: const EdgeInsets.all(24),
        children: [
          const _ModuleHeaderCard(
            title: 'Vídeos',
            description: 'Explore o feed do TeraTube, favoritando conteúdos, avaliando e comentando.',
          ),
          const SizedBox(height: 16),
          if (_loading)
            const _LoadingCard()
          else if (_error != null)
            _ErrorCard(message: _error!)
          else if (_items.isEmpty)
            const _EmptyCard(message: 'Nenhum vídeo disponível no momento.')
          else
            ..._items.map((video) {
              final isFavorite = video['is_favorite'] == true;
              return Padding(
                padding: const EdgeInsets.only(bottom: 12),
                child: Card(
                  child: Padding(
                    padding: const EdgeInsets.all(20),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Row(
                          children: [
                            Expanded(child: Text(_stringValue(video, ['title'], fallback: 'Vídeo'), style: Theme.of(context).textTheme.titleLarge)),
                            IconButton(
                              onPressed: () => _toggleFavorite(video),
                              icon: Icon(isFavorite ? Icons.favorite : Icons.favorite_border),
                            ),
                          ],
                        ),
                        const SizedBox(height: 8),
                        Text(_plainText(_stringValue(video, ['description_text'], fallback: 'Vídeo publicado pelo terapeuta.'))),
                        const SizedBox(height: 12),
                        Wrap(
                          spacing: 8,
                          runSpacing: 8,
                          children: [
                            _InfoChip(label: 'Origem', value: _stringValue(video, ['source_type'], fallback: 'upload')),
                            _InfoChip(label: 'Média', value: _stringValue(video, ['average_rating'], fallback: '0')),
                            _InfoChip(label: 'Comentários', value: _stringValue(video, ['comment_count'], fallback: '0')),
                          ],
                        ),
                        const SizedBox(height: 16),
                        Wrap(
                          spacing: 8,
                          runSpacing: 8,
                          children: [
                            FilledButton.tonal(
                              onPressed: () => _launchUrl(context, _resolveVideoUrl(video)),
                              child: const Text('Assistir'),
                            ),
                            OutlinedButton(
                              onPressed: () => _openVideoDetails(video),
                              child: const Text('Abrir detalhes'),
                            ),
                          ],
                        ),
                      ],
                    ),
                  ),
                ),
              );
            }),
        ],
      ),
    );
  }
}

class MessengerPage extends StatefulWidget {
  const MessengerPage({
    super.key,
    required this.apiClient,
    required this.analyticsService,
  });

  final ApiClient apiClient;
  final AnalyticsService analyticsService;

  @override
  State<MessengerPage> createState() => _MessengerPageState();
}

class _MessengerPageState extends State<MessengerPage> {
  final TextEditingController _noteController = TextEditingController();
  List<Map<String, dynamic>> _entries = <Map<String, dynamic>>[];
  Map<String, dynamic>? _drawnMessage;
  String? _error;
  bool _loading = true;
  bool _saving = false;
  bool _shareWithTherapist = true;

  @override
  void initState() {
    super.initState();
    _load();
  }

  @override
  void dispose() {
    _noteController.dispose();
    super.dispose();
  }

  Future<void> _load() async {
    setState(() {
      _loading = true;
      _error = null;
    });

    try {
      final response = await widget.apiClient.fetchMessengerEntries();
      await widget.analyticsService.track('messenger_view', context: 'messenger');
      if (!mounted) {
        return;
      }
      setState(() {
        _entries = _mapList(response['data'], 'items');
      });
    } catch (error) {
      if (!mounted) {
        return;
      }
      setState(() {
        _error = _normalizeError(error);
      });
    } finally {
      if (mounted) {
        setState(() {
          _loading = false;
        });
      }
    }
  }

  Future<void> _drawMessage() async {
    try {
      final response = await widget.apiClient.drawMessenger();
      final data = _mapValue(response, 'data');
      if (!mounted) {
        return;
      }
      setState(() {
        _drawnMessage = _mapValue(data, 'message');
      });
    } catch (error) {
      if (!mounted) {
        return;
      }
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(_normalizeError(error))));
    }
  }

  Future<void> _saveMessage() async {
    final message = _drawnMessage;
    if (message == null) {
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Sorteie uma mensagem antes de salvar.')));
      return;
    }

    final note = _noteController.text.trim();
    if (note.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Escreva sua reflexão antes de salvar.')));
      return;
    }

    setState(() {
      _saving = true;
    });

    try {
      await widget.apiClient.saveMessenger(
        messageId: _intValue(message['id']) == 0 ? null : _intValue(message['id']),
        messageCategory: _stringValue(message, ['category'], fallback: 'dores'),
        messageText: _stringValue(message, ['text']),
        patientNote: note,
        shareWithTherapist: _shareWithTherapist,
      );
      await widget.analyticsService.track('messenger_save', context: 'messenger');
      _noteController.clear();
      if (!mounted) {
        return;
      }
      setState(() {
        _drawnMessage = null;
      });
      await _load();
      if (!mounted) {
        return;
      }
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Reflexão salva com sucesso.')));
    } catch (error) {
      if (!mounted) {
        return;
      }
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(_normalizeError(error))));
    } finally {
      if (mounted) {
        setState(() {
          _saving = false;
        });
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return RefreshIndicator(
      onRefresh: _load,
      child: ListView(
        padding: const EdgeInsets.all(24),
        children: [
          _ModuleHeaderCard(
            title: 'Mensageiro',
            description: 'Sorteie mensagens reflexivas e registre suas anotações para acompanhar o processo terapêutico.',
            imageUrl: _resolveAppUrl('app/images/mensageiro.png'),
            imageTitle: 'Abra a mensagem que chegou para você.',
            imageCopy: 'Essa mensagem vai falar ao seu coração. Observe como seu corpo reage e escreva sua reflexão sobre o que está sentindo.',
          ),
          const SizedBox(height: 16),
          Card(
            child: Padding(
              padding: const EdgeInsets.all(20),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  FilledButton.tonal(
                    onPressed: _drawMessage,
                    child: const Text('Sortear mensagem'),
                  ),
                  if (_drawnMessage != null) ...[
                    const SizedBox(height: 16),
                    Text(_stringValue(_drawnMessage!, ['category'], fallback: 'mensagem').toUpperCase(), style: Theme.of(context).textTheme.labelLarge),
                    const SizedBox(height: 8),
                    Text(_stringValue(_drawnMessage!, ['text']), style: Theme.of(context).textTheme.titleMedium),
                    const SizedBox(height: 16),
                    TextField(
                      controller: _noteController,
                      minLines: 4,
                      maxLines: 8,
                      decoration: const InputDecoration(labelText: 'Sua reflexão', alignLabelWithHint: true),
                    ),
                    const SizedBox(height: 8),
                    SwitchListTile(
                      contentPadding: EdgeInsets.zero,
                      title: const Text('Compartilhar com terapeuta'),
                      value: _shareWithTherapist,
                      onChanged: (value) {
                        setState(() {
                          _shareWithTherapist = value;
                        });
                      },
                    ),
                    const SizedBox(height: 8),
                    FilledButton(
                      onPressed: _saving ? null : _saveMessage,
                      child: _saving
                          ? const SizedBox(width: 22, height: 22, child: CircularProgressIndicator(strokeWidth: 2.2, color: Colors.white))
                          : const Text('Salvar reflexão'),
                    ),
                  ],
                ],
              ),
            ),
          ),
          const SizedBox(height: 16),
          if (_loading)
            const _LoadingCard()
          else if (_error != null)
            _ErrorCard(message: _error!)
          else ...[
            _SectionTitle(title: 'Histórico', count: _entries.length),
            if (_entries.isEmpty)
              const _EmptyCard(
                icon: Icons.chat_bubble_outline,
                title: 'Seu histórico ainda está em branco',
                message: 'Depois de sortear uma mensagem e salvar sua reflexão, os registros anteriores ficarão listados aqui.',
                hint: 'Use o botão acima para gerar uma nova mensagem e começar seu histórico.',
              )
            else
              ..._entries.map(
                (entry) => Padding(
                  padding: const EdgeInsets.only(bottom: 12),
                  child: Card(
                    child: Padding(
                      padding: const EdgeInsets.all(20),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(_stringValue(entry, ['message_category'], fallback: 'mensagem').toUpperCase(), style: Theme.of(context).textTheme.labelLarge),
                          const SizedBox(height: 8),
                          Text(_stringValue(entry, ['message_text']), style: Theme.of(context).textTheme.titleMedium),
                          const SizedBox(height: 8),
                          Text(_stringValue(entry, ['patient_note'])),
                        ],
                      ),
                    ),
                  ),
                ),
              ),
          ],
        ],
      ),
    );
  }
}

class FatherWordPage extends StatefulWidget {
  const FatherWordPage({
    super.key,
    required this.apiClient,
    required this.analyticsService,
  });

  final ApiClient apiClient;
  final AnalyticsService analyticsService;

  @override
  State<FatherWordPage> createState() => _FatherWordPageState();
}

class _FatherWordPageState extends State<FatherWordPage> {
  final TextEditingController _noteController = TextEditingController();

  List<Map<String, dynamic>> _entries = <Map<String, dynamic>>[];
  Map<String, dynamic>? _drawnWord;
  String? _error;
  bool _loading = true;
  bool _saving = false;
  bool _shareWithTherapist = true;

  @override
  void initState() {
    super.initState();
    _load();
  }

  @override
  void dispose() {
    _noteController.dispose();
    super.dispose();
  }

  Future<void> _load() async {
    setState(() {
      _loading = true;
      _error = null;
    });

    try {
      final response = await widget.apiClient.fetchFatherWordEntries();
      await widget.analyticsService.track('father_word_view', context: 'father_word');
      if (!mounted) {
        return;
      }
      setState(() {
        _entries = _mapList(response['data'], 'items');
      });
    } catch (error) {
      if (!mounted) {
        return;
      }
      setState(() {
        _error = _normalizeError(error);
      });
    } finally {
      if (mounted) {
        setState(() {
          _loading = false;
        });
      }
    }
  }

  Future<void> _drawWord() async {
    try {
      final response = await widget.apiClient.drawFatherWord();
      final data = _mapValue(response, 'data');
      await widget.analyticsService.track('father_word_draw', context: 'father_word');
      if (!mounted) {
        return;
      }
      setState(() {
        _drawnWord = _mapValue(data, 'word');
      });
    } catch (error) {
      if (!mounted) {
        return;
      }
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(_normalizeError(error))));
    }
  }

  Future<void> _saveWord() async {
    final word = _drawnWord;
    if (word == null) {
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Receba uma palavra antes de salvar.')));
      return;
    }

    final note = _noteController.text.trim();
    if (note.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Escreva sua reflexão antes de salvar.')));
      return;
    }

    setState(() {
      _saving = true;
    });

    try {
      await widget.apiClient.saveFatherWordEntry(
        wordId: _intValue(word['id']) == 0 ? null : _intValue(word['id']),
        wordReference: _stringValue(word, ['reference']),
        wordText: _stringValue(word, ['text']),
        patientNote: note,
        shareWithTherapist: _shareWithTherapist,
      );
      await widget.analyticsService.track('father_word_save', context: 'father_word');
      _noteController.clear();
      if (!mounted) {
        return;
      }
      setState(() {
        _drawnWord = null;
      });
      await _load();
      if (!mounted) {
        return;
      }
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Reflexão salva com sucesso.')));
    } catch (error) {
      if (!mounted) {
        return;
      }
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(_normalizeError(error))));
    } finally {
      if (mounted) {
        setState(() {
          _saving = false;
        });
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return RefreshIndicator(
      onRefresh: _load,
      child: ListView(
        padding: const EdgeInsets.all(24),
        children: [
          _ModuleHeaderCard(
            title: 'Pai, fala comigo',
            description: 'Receba uma palavra de fé, registre sua reflexão e acompanhe seu histórico espiritual.',
            imageUrl: _resolveAppUrl('app/images/fala-comigo.png'),
            imageTitle: 'Não importa o que você está vivendo, Deus sempre tem uma palavra para o seu coração.',
          ),
          const SizedBox(height: 16),
          Card(
            child: Padding(
              padding: const EdgeInsets.all(20),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  FilledButton.tonal(
                    onPressed: _drawWord,
                    child: const Text('Pai, fala comigo'),
                  ),
                  if (_drawnWord != null) ...[
                    const SizedBox(height: 16),
                    Text(_stringValue(_drawnWord!, ['reference']), style: Theme.of(context).textTheme.labelLarge),
                    const SizedBox(height: 8),
                    Text(_stringValue(_drawnWord!, ['text']), style: Theme.of(context).textTheme.titleMedium),
                    const SizedBox(height: 16),
                    TextField(
                      controller: _noteController,
                      minLines: 4,
                      maxLines: 8,
                      decoration: const InputDecoration(labelText: 'Sua reflexão', alignLabelWithHint: true),
                    ),
                    const SizedBox(height: 8),
                    SwitchListTile(
                      contentPadding: EdgeInsets.zero,
                      title: const Text('Compartilhar com terapeuta'),
                      value: _shareWithTherapist,
                      onChanged: (value) {
                        setState(() {
                          _shareWithTherapist = value;
                        });
                      },
                    ),
                    const SizedBox(height: 8),
                    FilledButton(
                      onPressed: _saving ? null : _saveWord,
                      child: _saving
                          ? const SizedBox(width: 22, height: 22, child: CircularProgressIndicator(strokeWidth: 2.2, color: Colors.white))
                          : const Text('Salvar reflexão'),
                    ),
                  ],
                ],
              ),
            ),
          ),
          const SizedBox(height: 16),
          if (_loading)
            const _LoadingCard()
          else if (_error != null)
            _ErrorCard(message: _error!)
          else ...[
            _SectionTitle(title: 'Meu histórico de palavras', count: _entries.length),
            if (_entries.isEmpty)
              const _EmptyCard(
                icon: Icons.bookmark_outline,
                title: 'Nenhuma reflexão salva ainda',
                message: 'Depois de receber uma palavra e registrar sua reflexão, seu histórico deste módulo ficará disponível aqui.',
                hint: 'Use o botão acima para sortear sua primeira palavra.',
              )
            else
              ..._entries.map(
                (entry) => Padding(
                  padding: const EdgeInsets.only(bottom: 12),
                  child: Card(
                    child: Padding(
                      padding: const EdgeInsets.all(20),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(_stringValue(entry, ['word_reference']), style: Theme.of(context).textTheme.labelLarge),
                          const SizedBox(height: 8),
                          Text(_stringValue(entry, ['word_text']), style: Theme.of(context).textTheme.titleMedium),
                          const SizedBox(height: 8),
                          Text(_stringValue(entry, ['patient_note'])),
                        ],
                      ),
                    ),
                  ),
                ),
              ),
          ],
        ],
      ),
    );
  }
}

class MeditationsPage extends StatefulWidget {
  const MeditationsPage({
    super.key,
    required this.apiClient,
    required this.analyticsService,
  });

  final ApiClient apiClient;
  final AnalyticsService analyticsService;

  @override
  State<MeditationsPage> createState() => _MeditationsPageState();
}

class _MeditationsPageState extends State<MeditationsPage> {
  List<Map<String, dynamic>> _items = <Map<String, dynamic>>[];
  String? _error;
  bool _loading = true;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() {
      _loading = true;
      _error = null;
    });

    try {
      final response = await widget.apiClient.fetchGuidedMeditations();
      await widget.analyticsService.track('guided_meditations_view', context: 'meditations');
      if (!mounted) {
        return;
      }
      setState(() {
        _items = _mapList(response['data'], 'items');
      });
    } catch (error) {
      if (!mounted) {
        return;
      }
      setState(() {
        _error = _normalizeError(error);
      });
    } finally {
      if (mounted) {
        setState(() {
          _loading = false;
        });
      }
    }
  }

  Future<void> _openMeditation(Map<String, dynamic> meditation) async {
    final meditationId = _intValue(meditation['id']);
    if (meditationId <= 0) {
      return;
    }

    final noteController = TextEditingController();
    Map<String, dynamic>? drawnLetter;
    bool audioCompleted = _stringValue(meditation, ['audio_url']).isEmpty;
    bool saving = false;
    bool shareWithTherapist = true;

    try {
      final response = await widget.apiClient.fetchGuidedMeditationDetails(meditationId: meditationId);
      final data = _mapValue(response, 'data');
      final details = _mapValue(data, 'meditation');
      final entries = _mapList(data, 'entries');

      if (!mounted) {
        return;
      }

      await showModalBottomSheet<void>(
        context: context,
        isScrollControlled: true,
        useSafeArea: true,
        builder: (context) {
          return StatefulBuilder(
            builder: (context, setModalState) {
              Future<void> drawLetter() async {
                try {
                  final drawResponse = await widget.apiClient.drawGuidedMeditationLetter(meditationId: meditationId);
                  final drawData = _mapValue(drawResponse, 'data');
                  setModalState(() {
                    drawnLetter = _mapValue(drawData, 'letter');
                  });
                } catch (error) {
                  if (mounted) {
                    ScaffoldMessenger.of(this.context).showSnackBar(SnackBar(content: Text(_normalizeError(error))));
                  }
                }
              }

              Future<void> save() async {
                final note = noteController.text.trim();
                if (drawnLetter == null || note.isEmpty) {
                  ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Sorteie uma carta e escreva sua reflexão.')));
                  return;
                }
                setModalState(() {
                  saving = true;
                });
                try {
                  await widget.apiClient.saveGuidedMeditationEntry(
                    meditationId: meditationId,
                    letterId: _intValue(drawnLetter!['id']) == 0 ? null : _intValue(drawnLetter!['id']),
                    letterCategory: _stringValue(drawnLetter!, ['category'], fallback: 'dores'),
                    letterText: _stringValue(drawnLetter!, ['text']),
                    patientNote: note,
                    shareWithTherapist: shareWithTherapist,
                  );
                  await widget.analyticsService.track('guided_meditation_save', context: 'meditations', payload: {'meditation_id': meditationId});
                  if (!mounted || !context.mounted) {
                    return;
                  }
                  Navigator.of(context).pop();
                  await _load();
                  if (!mounted) {
                    return;
                  }
                  ScaffoldMessenger.of(this.context).showSnackBar(const SnackBar(content: Text('Reflexão salva com sucesso.')));
                } catch (error) {
                  if (mounted) {
                    ScaffoldMessenger.of(this.context).showSnackBar(SnackBar(content: Text(_normalizeError(error))));
                  }
                } finally {
                  if (mounted) {
                    setModalState(() {
                      saving = false;
                    });
                  }
                }
              }

              return Padding(
                padding: EdgeInsets.fromLTRB(24, 16, 24, 24 + MediaQuery.of(context).viewInsets.bottom),
                child: SingleChildScrollView(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      if (_stringValue(details, ['reference_image_path']).isNotEmpty) ...[
                        _MediaPreview(
                          imageUrl: _resolveMediaUrl(_stringValue(details, ['reference_image_path'])),
                          height: 180,
                          borderRadius: 18,
                        ),
                        const SizedBox(height: 16),
                      ],
                      Text(_stringValue(details, ['title'], fallback: 'Meditação'), style: Theme.of(context).textTheme.titleLarge),
                      const SizedBox(height: 8),
                      Text(_plainText(_stringValue(details, ['description', 'description_text'], fallback: 'Sem descrição.'))),
                      const SizedBox(height: 16),
                      _InlineAudioPlayerCard(
                        title: 'Áudio da meditação',
                        audioUrl: _resolveAudioUrl(details),
                        audioHeaders: widget.apiClient.mediaHeaders,
                        onCompleted: () {
                          setModalState(() {
                            audioCompleted = true;
                          });
                        },
                      ),
                      const SizedBox(height: 12),
                      OutlinedButton(
                        onPressed: audioCompleted ? drawLetter : null,
                        child: const Text('Sortear palavra de cura'),
                      ),
                      if (!audioCompleted)
                        Padding(
                          padding: const EdgeInsets.only(top: 8),
                          child: Text(
                            'Ouça o áudio até o fim para liberar o sorteio da palavra de cura.',
                            style: Theme.of(context).textTheme.bodySmall,
                          ),
                        ),
                      if (drawnLetter != null) ...[
                        const SizedBox(height: 16),
                        Text(_stringValue(drawnLetter!, ['category']).toUpperCase(), style: Theme.of(context).textTheme.labelLarge),
                        const SizedBox(height: 8),
                        Text(_stringValue(drawnLetter!, ['text'])),
                        const SizedBox(height: 12),
                        TextField(
                          controller: noteController,
                          minLines: 4,
                          maxLines: 8,
                          decoration: const InputDecoration(labelText: 'Sua reflexão', alignLabelWithHint: true),
                        ),
                        SwitchListTile(
                          contentPadding: EdgeInsets.zero,
                          title: const Text('Compartilhar com terapeuta'),
                          value: shareWithTherapist,
                          onChanged: (value) {
                            setModalState(() {
                              shareWithTherapist = value;
                            });
                          },
                        ),
                        FilledButton(
                          onPressed: saving ? null : save,
                          child: saving
                              ? const SizedBox(width: 22, height: 22, child: CircularProgressIndicator(strokeWidth: 2.2, color: Colors.white))
                              : const Text('Salvar reflexão'),
                        ),
                      ],
                      const SizedBox(height: 16),
                      Text('Histórico', style: Theme.of(context).textTheme.titleMedium),
                      const SizedBox(height: 8),
                      if (entries.isEmpty)
                        const Text('Nenhuma reflexão salva para esta meditação.')
                      else
                        ...entries.map((entry) => Padding(
                              padding: const EdgeInsets.only(bottom: 8),
                              child: Text(_stringValue(entry, ['patient_note'], fallback: 'Reflexão registrada.')),
                            )),
                    ],
                  ),
                ),
              );
            },
          );
        },
      );
    } catch (error) {
      if (!mounted) {
        return;
      }
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(_normalizeError(error))));
    } finally {
      noteController.dispose();
    }
  }

  @override
  Widget build(BuildContext context) {
    return RefreshIndicator(
      onRefresh: _load,
      child: ListView(
        padding: const EdgeInsets.all(24),
        children: [
          const _ModuleHeaderCard(
            title: 'Meditações',
            description: 'Ouça as meditações guiadas, sorteie cartas de cura e registre suas reflexões.',
          ),
          const SizedBox(height: 16),
          if (_loading)
            const _LoadingCard()
          else if (_error != null)
            _ErrorCard(message: _error!)
          else if (_items.isEmpty)
            const _EmptyCard(message: 'Nenhuma meditação disponível no momento.')
          else
            ..._items.map((meditation) => Padding(
                  padding: const EdgeInsets.only(bottom: 12),
                  child: Card(
                    child: Padding(
                      padding: const EdgeInsets.all(20),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(_stringValue(meditation, ['title'], fallback: 'Meditação'), style: Theme.of(context).textTheme.titleLarge),
                          const SizedBox(height: 8),
                          Text(_plainText(_stringValue(meditation, ['description', 'description_text'], fallback: 'Sem descrição.'))),
                          const SizedBox(height: 16),
                          OutlinedButton(
                            onPressed: () => _openMeditation(meditation),
                            child: const Text('Abrir meditação'),
                          ),
                        ],
                      ),
                    ),
                  ),
                )),
        ],
      ),
    );
  }
}

class PrayersPage extends StatefulWidget {
  const PrayersPage({
    super.key,
    required this.apiClient,
    required this.analyticsService,
  });

  final ApiClient apiClient;
  final AnalyticsService analyticsService;

  @override
  State<PrayersPage> createState() => _PrayersPageState();
}

class _PrayersPageState extends State<PrayersPage> {
  List<Map<String, dynamic>> _items = <Map<String, dynamic>>[];
  String? _error;
  bool _loading = true;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() {
      _loading = true;
      _error = null;
    });

    try {
      final response = await widget.apiClient.fetchPrayers();
      await widget.analyticsService.track('prayers_view', context: 'prayers');
      if (!mounted) {
        return;
      }
      setState(() {
        _items = _mapList(response['data'], 'items');
      });
    } catch (error) {
      if (!mounted) {
        return;
      }
      setState(() {
        _error = _normalizeError(error);
      });
    } finally {
      if (mounted) {
        setState(() {
          _loading = false;
        });
      }
    }
  }

  Future<void> _openPrayer(Map<String, dynamic> prayer) async {
    final prayerId = _intValue(prayer['id']);
    if (prayerId <= 0) {
      return;
    }

    final noteController = TextEditingController();
    bool saving = false;
    bool shareWithTherapist = true;

    try {
      final response = await widget.apiClient.fetchPrayerDetails(prayerId: prayerId);
      final data = _mapValue(response, 'data');
      final details = _mapValue(data, 'prayer');
      final entries = _mapList(data, 'entries');

      if (!mounted) {
        return;
      }

      await showModalBottomSheet<void>(
        context: context,
        isScrollControlled: true,
        useSafeArea: true,
        builder: (context) {
          return StatefulBuilder(
            builder: (context, setModalState) {
              Future<void> save() async {
                final note = noteController.text.trim();
                if (note.isEmpty) {
                  ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Escreva sua reflexão antes de salvar.')));
                  return;
                }
                setModalState(() {
                  saving = true;
                });
                try {
                  await widget.apiClient.savePrayerEntry(
                    prayerId: prayerId,
                    patientNote: note,
                    shareWithTherapist: shareWithTherapist,
                  );
                  await widget.analyticsService.track('prayer_save', context: 'prayers', payload: {'prayer_id': prayerId});
                  if (!mounted || !context.mounted) {
                    return;
                  }
                  Navigator.of(context).pop();
                  await _load();
                  if (!mounted) {
                    return;
                  }
                  ScaffoldMessenger.of(this.context).showSnackBar(const SnackBar(content: Text('Reflexão salva com sucesso.')));
                } catch (error) {
                  if (mounted) {
                    ScaffoldMessenger.of(this.context).showSnackBar(SnackBar(content: Text(_normalizeError(error))));
                  }
                } finally {
                  if (mounted) {
                    setModalState(() {
                      saving = false;
                    });
                  }
                }
              }

              return Padding(
                padding: EdgeInsets.fromLTRB(24, 16, 24, 24 + MediaQuery.of(context).viewInsets.bottom),
                child: SingleChildScrollView(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      if (_stringValue(details, ['reference_image_path']).isNotEmpty) ...[
                        _MediaPreview(
                          imageUrl: _resolveMediaUrl(_stringValue(details, ['reference_image_path'])),
                          height: 180,
                          borderRadius: 18,
                        ),
                        const SizedBox(height: 16),
                      ],
                      Text(_stringValue(details, ['title'], fallback: 'Oração'), style: Theme.of(context).textTheme.titleLarge),
                      const SizedBox(height: 8),
                      Text(_plainText(_stringValue(details, ['description', 'description_text'], fallback: 'Sem descrição.'))),
                      const SizedBox(height: 16),
                      _InlineAudioPlayerCard(
                        title: 'Áudio da oração',
                        audioUrl: _resolveAudioUrl(details),
                        audioHeaders: widget.apiClient.mediaHeaders,
                      ),
                      const SizedBox(height: 16),
                      TextField(
                        controller: noteController,
                        minLines: 4,
                        maxLines: 8,
                        decoration: const InputDecoration(labelText: 'Sua reflexão', alignLabelWithHint: true),
                      ),
                      SwitchListTile(
                        contentPadding: EdgeInsets.zero,
                        title: const Text('Compartilhar com terapeuta'),
                        value: shareWithTherapist,
                        onChanged: (value) {
                          setModalState(() {
                            shareWithTherapist = value;
                          });
                        },
                      ),
                      FilledButton(
                        onPressed: saving ? null : save,
                        child: saving
                            ? const SizedBox(width: 22, height: 22, child: CircularProgressIndicator(strokeWidth: 2.2, color: Colors.white))
                            : const Text('Salvar reflexão'),
                      ),
                      const SizedBox(height: 16),
                      Text('Histórico', style: Theme.of(context).textTheme.titleMedium),
                      const SizedBox(height: 8),
                      if (entries.isEmpty)
                        const Text('Nenhuma reflexão salva para esta oração.')
                      else
                        ...entries.map((entry) => Padding(
                              padding: const EdgeInsets.only(bottom: 8),
                              child: Text(_stringValue(entry, ['patient_note'], fallback: 'Reflexão registrada.')),
                            )),
                    ],
                  ),
                ),
              );
            },
          );
        },
      );
    } catch (error) {
      if (!mounted) {
        return;
      }
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(_normalizeError(error))));
    } finally {
      noteController.dispose();
    }
  }

  @override
  Widget build(BuildContext context) {
    return RefreshIndicator(
      onRefresh: _load,
      child: ListView(
        padding: const EdgeInsets.all(24),
        children: [
          const _ModuleHeaderCard(
            title: 'Orações',
            description: 'Ouça os áudios enviados pelo terapeuta e registre suas reflexões espirituais.',
          ),
          const SizedBox(height: 16),
          if (_loading)
            const _LoadingCard()
          else if (_error != null)
            _ErrorCard(message: _error!)
          else if (_items.isEmpty)
            const _EmptyCard(message: 'Nenhuma oração disponível no momento.')
          else
            ..._items.map((prayer) => Padding(
                  padding: const EdgeInsets.only(bottom: 12),
                  child: Card(
                    child: Padding(
                      padding: const EdgeInsets.all(20),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(_stringValue(prayer, ['title'], fallback: 'Oração'), style: Theme.of(context).textTheme.titleLarge),
                          const SizedBox(height: 8),
                          Text(_plainText(_stringValue(prayer, ['description', 'description_text'], fallback: 'Sem descrição.'))),
                          const SizedBox(height: 16),
                          OutlinedButton(
                            onPressed: () => _openPrayer(prayer),
                            child: const Text('Abrir oração'),
                          ),
                        ],
                      ),
                    ),
                  ),
                )),
        ],
      ),
    );
  }
}

class GratitudePage extends StatefulWidget {
  const GratitudePage({
    super.key,
    required this.apiClient,
    required this.analyticsService,
  });

  final ApiClient apiClient;
  final AnalyticsService analyticsService;

  @override
  State<GratitudePage> createState() => _GratitudePageState();
}

class _GratitudePageState extends State<GratitudePage> {
  final TextEditingController _controller = TextEditingController();

  List<Map<String, dynamic>> _entries = <Map<String, dynamic>>[];
  int _currentCycle = 0;
  int _nextCycle = 1;
  int _nextDay = 1;
  bool _locked = false;
  String? _error;
  bool _loading = true;
  bool _saving = false;

  @override
  void initState() {
    super.initState();
    _load();
  }

  @override
  void dispose() {
    _controller.dispose();
    super.dispose();
  }

  Future<void> _load() async {
    setState(() {
      _loading = true;
      _error = null;
    });

    try {
      final response = await widget.apiClient.fetchGratitude();
      final data = _mapValue(response, 'data');
      await widget.analyticsService.track('gratitude_view', context: 'gratitude');
      if (!mounted) {
        return;
      }
      setState(() {
        _entries = _mapList(data, 'entries');
        _currentCycle = _intValue(data['current_cycle']);
        _nextCycle = _intValue(data['next_cycle']);
        _nextDay = _intValue(data['next_day']);
        _locked = data['is_current_cycle_locked'] == true;
      });
    } catch (error) {
      if (!mounted) {
        return;
      }
      setState(() {
        _error = _normalizeError(error);
      });
    } finally {
      if (mounted) {
        setState(() {
          _loading = false;
        });
      }
    }
  }

  Future<void> _save() async {
    final text = _controller.text.trim();
    if (text.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Escreva sua gratidão antes de salvar.')));
      return;
    }

    setState(() {
      _saving = true;
    });

    try {
      await widget.apiClient.saveGratitudeEntry(contentHtml: _textToHtml(text));
      await widget.analyticsService.track('gratitude_save', context: 'gratitude', payload: {'day': _nextDay, 'cycle': _nextCycle});
      _controller.clear();
      await _load();
      if (!mounted) {
        return;
      }
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Registro salvo com sucesso.')));
    } catch (error) {
      if (!mounted) {
        return;
      }
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(_normalizeError(error))));
    } finally {
      if (mounted) {
        setState(() {
          _saving = false;
        });
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return RefreshIndicator(
      onRefresh: _load,
      child: ListView(
        padding: const EdgeInsets.all(24),
        children: [
          _ModuleHeaderCard(
            title: 'Diário da gratidão',
            description: 'Registre sua gratidão diária e acompanhe a evolução do ciclo de 30 dias.',
            imageUrl: _resolveAppUrl('app/images/gratidao.png'),
            imageTitle: 'Diário da gratidão',
            imageCopy: 'Registre diariamente motivos de gratidão. Ao completar 30 dias, o diário é consolidado e enviado ao terapeuta.',
          ),
          const SizedBox(height: 16),
          if (_loading)
            const _LoadingCard()
          else if (_error != null)
            _ErrorCard(message: _error!)
          else ...[
            Card(
              child: Padding(
                padding: const EdgeInsets.all(20),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text('Ciclo atual: $_currentCycle | Próximo registro: ciclo $_nextCycle, dia $_nextDay'),
                    const SizedBox(height: 12),
                    TextField(
                      controller: _controller,
                      minLines: 4,
                      maxLines: 8,
                      enabled: !_locked && !_saving,
                      decoration: InputDecoration(
                        labelText: _locked ? 'Ciclo atual concluído' : 'Minha gratidão de hoje',
                        alignLabelWithHint: true,
                      ),
                    ),
                    const SizedBox(height: 16),
                    FilledButton(
                      onPressed: _locked || _saving ? null : _save,
                      child: _saving
                          ? const SizedBox(width: 22, height: 22, child: CircularProgressIndicator(strokeWidth: 2.2, color: Colors.white))
                          : const Text('Salvar gratidão'),
                    ),
                  ],
                ),
              ),
            ),
            const SizedBox(height: 16),
            _SectionTitle(title: 'Histórico do ciclo', count: _entries.length),
            if (_entries.isEmpty)
              const _EmptyCard(message: 'Nenhum registro salvo neste ciclo ainda.')
            else
              ..._entries.map((entry) => Padding(
                    padding: const EdgeInsets.only(bottom: 12),
                    child: Card(
                      child: Padding(
                        padding: const EdgeInsets.all(20),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text('Dia ${_stringValue(entry, ['day_number'], fallback: '?')}'),
                            const SizedBox(height: 8),
                            Text(_plainText(_stringValue(entry, ['content_html'], fallback: ''))),
                          ],
                        ),
                      ),
                    ),
                  )),
          ],
        ],
      ),
    );
  }
}

class DevotionalsPage extends StatefulWidget {
  const DevotionalsPage({
    super.key,
    required this.apiClient,
    required this.analyticsService,
  });

  final ApiClient apiClient;
  final AnalyticsService analyticsService;

  @override
  State<DevotionalsPage> createState() => _DevotionalsPageState();
}

class _DevotionalsPageState extends State<DevotionalsPage> {
  List<Map<String, dynamic>> _records = <Map<String, dynamic>>[];
  Map<String, dynamic>? _todayEntry;
  String? _error;
  bool _loading = true;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() {
      _loading = true;
      _error = null;
    });

    try {
      final response = await widget.apiClient.fetchDevotionals();
      final data = _mapValue(response, 'data');
      await widget.analyticsService.track('devotionals_view', context: 'devotionals');
      if (!mounted) {
        return;
      }
      setState(() {
        _todayEntry = data['today_entry'] is Map<String, dynamic> ? Map<String, dynamic>.from(data['today_entry'] as Map) : null;
        _records = _mapList(data, 'records');
      });
    } catch (error) {
      if (!mounted) {
        return;
      }
      setState(() {
        _error = _normalizeError(error);
      });
    } finally {
      if (mounted) {
        setState(() {
          _loading = false;
        });
      }
    }
  }

  Future<void> _openToday() async {
    final controller = TextEditingController();
    bool saving = false;

    try {
      final response = await widget.apiClient.fetchDevotionalToday();
      final data = _mapValue(response, 'data');
      final entry = _mapValue(data, 'entry');
      final existing = data['existing_reflection'] is Map<String, dynamic> ? Map<String, dynamic>.from(data['existing_reflection'] as Map) : <String, dynamic>{};
      controller.text = _plainText(_stringValue(existing, ['reflection_html']));
      final entryId = _intValue(entry['id']);

      if (!mounted) {
        return;
      }

      await showModalBottomSheet<void>(
        context: context,
        isScrollControlled: true,
        useSafeArea: true,
        builder: (context) {
          return StatefulBuilder(
            builder: (context, setModalState) {
              Future<void> save() async {
                final reflection = controller.text.trim();
                if (reflection.isEmpty) {
                  ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Escreva sua reflexão antes de salvar.')));
                  return;
                }
                setModalState(() {
                  saving = true;
                });
                try {
                  await widget.apiClient.saveDevotionalReflection(
                    devotionalEntryId: entryId,
                    reflectionHtml: _textToHtml(reflection),
                  );
                  await widget.analyticsService.track('devotional_save', context: 'devotionals', payload: {'entry_id': entryId});
                  if (!mounted || !context.mounted) {
                    return;
                  }
                  Navigator.of(context).pop();
                  await _load();
                  if (!mounted) {
                    return;
                  }
                  ScaffoldMessenger.of(this.context).showSnackBar(const SnackBar(content: Text('Reflexão salva com sucesso.')));
                } catch (error) {
                  if (mounted) {
                    ScaffoldMessenger.of(this.context).showSnackBar(SnackBar(content: Text(_normalizeError(error))));
                  }
                } finally {
                  if (mounted) {
                    setModalState(() {
                      saving = false;
                    });
                  }
                }
              }

              return Padding(
                padding: EdgeInsets.fromLTRB(24, 16, 24, 24 + MediaQuery.of(context).viewInsets.bottom),
                child: SingleChildScrollView(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Text(_stringValue(entry, ['title'], fallback: 'Devocional do dia'), style: Theme.of(context).textTheme.titleLarge),
                      const SizedBox(height: 8),
                      Text(_formatDateLabel(_stringValue(entry, ['entry_date'], fallback: ''))),
                      const SizedBox(height: 8),
                      Text(_stringValue(entry, ['word_of_god'], fallback: ''), style: Theme.of(context).textTheme.titleMedium),
                      const SizedBox(height: 16),
                      Card(
                        margin: EdgeInsets.zero,
                        child: Padding(
                          padding: const EdgeInsets.all(16),
                          child: Text(_plainText(_stringValue(entry, ['text_content'], fallback: ''))),
                        ),
                      ),
                      const SizedBox(height: 12),
                      TextField(
                        controller: controller,
                        minLines: 5,
                        maxLines: 8,
                        decoration: InputDecoration(
                          labelText: 'Minha reflexão',
                          helperText: existing.isEmpty ? 'Registre aqui sua reflexão de hoje.' : 'Sua reflexão anterior foi carregada para edição.',
                          alignLabelWithHint: true,
                        ),
                      ),
                      const SizedBox(height: 16),
                      FilledButton(
                        onPressed: saving ? null : save,
                        child: saving
                            ? const SizedBox(width: 22, height: 22, child: CircularProgressIndicator(strokeWidth: 2.2, color: Colors.white))
                            : const Text('Salvar reflexão'),
                      ),
                    ],
                  ),
                ),
              );
            },
          );
        },
      );
    } catch (error) {
      if (!mounted) {
        return;
      }
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(_normalizeError(error))));
    } finally {
      controller.dispose();
    }
  }

  @override
  Widget build(BuildContext context) {
    return RefreshIndicator(
      onRefresh: _load,
      child: ListView(
        padding: const EdgeInsets.all(24),
        children: [
          _ModuleHeaderCard(
            title: 'Devocional diário',
            description: 'Acesse o devocional do dia e mantenha um histórico das reflexões já registradas.',
            imageUrl: _resolveAppUrl('app/images/devocional.png'),
            imageTitle: 'Devocional diário',
            imageCopy: 'Acesse o devocional do dia e registre sua reflexão pessoal com qualidade.',
          ),
          const SizedBox(height: 16),
          if (_loading)
            const _LoadingCard()
          else if (_error != null)
            _ErrorCard(message: _error!)
          else ...[
            Card(
              child: Padding(
                padding: const EdgeInsets.all(20),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(_todayEntry != null ? _stringValue(_todayEntry!, ['title'], fallback: 'Devocional do dia') : 'Devocional do dia', style: Theme.of(context).textTheme.titleLarge),
                    const SizedBox(height: 8),
                    Text(_todayEntry != null ? _stringValue(_todayEntry!, ['theme', 'word_of_god'], fallback: 'Disponível para leitura.') : 'Nenhum devocional disponível para hoje.'),
                    if (_todayEntry != null) ...[
                      const SizedBox(height: 8),
                      Text(_plainText(_stringValue(_todayEntry!, ['text_content'])).split('. ').take(2).join('. ')),
                    ],
                    const SizedBox(height: 16),
                    FilledButton.tonal(
                      onPressed: _todayEntry == null ? null : _openToday,
                      child: const Text('Abrir devocional do dia'),
                    ),
                  ],
                ),
              ),
            ),
            const SizedBox(height: 16),
            _SectionTitle(title: 'Histórico de reflexões', count: _records.length),
            if (_records.isEmpty)
              const _EmptyCard(message: 'Nenhuma reflexão devocional salva ainda.')
            else
              ..._records.map((record) => Padding(
                    padding: const EdgeInsets.only(bottom: 12),
                    child: Card(
                      child: Padding(
                        padding: const EdgeInsets.all(20),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(_stringValue(record, ['title'], fallback: 'Devocional'), style: Theme.of(context).textTheme.titleMedium),
                            const SizedBox(height: 8),
                            Text(_formatDateLabel(_stringValue(record, ['entry_date'], fallback: ''))),
                            const SizedBox(height: 8),
                            Text(_plainText(_stringValue(record, ['reflection_html', 'compiled_html'], fallback: ''))),
                          ],
                        ),
                      ),
                    ),
                  )),
          ],
        ],
      ),
    );
  }
}

class _ModuleHeaderCard extends StatelessWidget {
  const _ModuleHeaderCard({
    required this.title,
    required this.description,
    this.imageUrl,
    this.imageTitle,
    this.imageCopy,
  });

  final String title;
  final String description;
  final String? imageUrl;
  final String? imageTitle;
  final String? imageCopy;

  @override
  Widget build(BuildContext context) {
    final hasImage = imageUrl != null && imageUrl!.trim().isNotEmpty;

    return Card(
      clipBehavior: Clip.antiAlias,
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          if (hasImage)
            Container(
              height: 220,
              width: double.infinity,
              decoration: BoxDecoration(
                color: Theme.of(context).colorScheme.surfaceContainerHighest,
                image: DecorationImage(
                  image: NetworkImage(imageUrl!),
                  fit: BoxFit.cover,
                  onError: (_, __) {},
                ),
              ),
              child: Container(
                decoration: const BoxDecoration(
                  gradient: LinearGradient(
                    begin: Alignment.topCenter,
                    end: Alignment.bottomCenter,
                    colors: [Color(0x14000000), Color(0xB8000000)],
                  ),
                ),
                padding: const EdgeInsets.all(20),
                child: Column(
                  mainAxisAlignment: MainAxisAlignment.end,
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    if ((imageTitle ?? '').trim().isNotEmpty)
                      Text(
                        imageTitle!,
                        style: Theme.of(context).textTheme.headlineSmall?.copyWith(
                              color: Colors.white,
                              fontWeight: FontWeight.w700,
                            ),
                      ),
                    if ((imageCopy ?? '').trim().isNotEmpty) ...[
                      const SizedBox(height: 8),
                      Text(
                        imageCopy!,
                        style: Theme.of(context).textTheme.bodyMedium?.copyWith(color: Colors.white),
                      ),
                    ],
                  ],
                ),
              ),
            ),
          Padding(
            padding: const EdgeInsets.all(20),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(title, style: Theme.of(context).textTheme.titleLarge),
                const SizedBox(height: 8),
                Text(description),
              ],
            ),
          ),
        ],
      ),
    );
  }
}

class _SectionTitle extends StatelessWidget {
  const _SectionTitle({required this.title, required this.count});

  final String title;
  final int count;

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 12),
      child: Text('$title ($count)', style: Theme.of(context).textTheme.titleMedium),
    );
  }
}

class _LoadingCard extends StatelessWidget {
  const _LoadingCard();

  @override
  Widget build(BuildContext context) {
    return const Card(
      child: Padding(
        padding: EdgeInsets.all(24),
        child: Center(child: CircularProgressIndicator()),
      ),
    );
  }
}

class _ErrorCard extends StatelessWidget {
  const _ErrorCard({required this.message});

  final String message;

  @override
  Widget build(BuildContext context) {
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(20),
        child: Text(message, style: TextStyle(color: Theme.of(context).colorScheme.error)),
      ),
    );
  }
}

class _EmptyCard extends StatelessWidget {
  const _EmptyCard({
    required this.message,
    this.title,
    this.hint,
    this.icon,
  });

  final String message;
  final String? title;
  final String? hint;
  final IconData? icon;

  @override
  Widget build(BuildContext context) {
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(20),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            if (icon != null) ...[
              Icon(icon, size: 28, color: Theme.of(context).colorScheme.primary),
              const SizedBox(height: 12),
            ],
            if (title != null) ...[
              Text(title!, style: Theme.of(context).textTheme.titleMedium),
              const SizedBox(height: 8),
            ],
            Text(message),
            if (hint != null) ...[
              const SizedBox(height: 12),
              Text(
                hint!,
                style: Theme.of(context).textTheme.bodySmall?.copyWith(
                      color: Theme.of(context).colorScheme.onSurfaceVariant,
                    ),
              ),
            ],
          ],
        ),
      ),
    );
  }
}

class _InfoChip extends StatelessWidget {
  const _InfoChip({required this.label, required this.value});

  final String label;
  final String value;

  @override
  Widget build(BuildContext context) {
    return Chip(label: Text('$label: $value'));
  }
}

Future<void> _launchUrl(BuildContext context, String rawUrl) async {
  final url = rawUrl.trim();
  if (url.isEmpty) {
    ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Conteúdo indisponível no momento.')));
    return;
  }

  final uri = Uri.tryParse(url);
  if (uri == null) {
    ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('URL inválida para este conteúdo.')));
    return;
  }

  final launched = await launchUrl(uri, mode: LaunchMode.externalApplication);
  if (!launched && context.mounted) {
    ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Não foi possível abrir o conteúdo.')));
  }
}

Map<String, dynamic> _mapValue(Object? value, String key) {
  if (value is Map<String, dynamic>) {
    final nested = value[key];
    if (nested is Map<String, dynamic>) {
      return nested;
    }
    if (nested is Map) {
      return Map<String, dynamic>.from(nested);
    }
  }
  return <String, dynamic>{};
}

List<Map<String, dynamic>> _mapList(Object? value, String key) {
  if (value is Map<String, dynamic>) {
    return _listFromDynamic(value[key]);
  }
  return <Map<String, dynamic>>[];
}

List<Map<String, dynamic>> _listFromDynamic(Object? value) {
  if (value is List) {
    return value.map((item) => item is Map<String, dynamic> ? item : Map<String, dynamic>.from(item as Map)).toList();
  }
  return <Map<String, dynamic>>[];
}

Map<int, List<Map<String, dynamic>>> _mapOfLists(Object? value) {
  if (value is! Map) {
    return <int, List<Map<String, dynamic>>>{};
  }

  final result = <int, List<Map<String, dynamic>>>{};
  value.forEach((key, nested) {
    final parsedKey = int.tryParse('$key');
    if (parsedKey == null) {
      return;
    }
    result[parsedKey] = _listFromDynamic(nested);
  });
  return result;
}

bool _isTreeOfLifeTask(Map<String, dynamic> task) {
  return _stringValue(task, ['task_type']) == 'virtual_tree_of_life';
}

Map<String, dynamic> _jsonObjectFromDynamic(Object? value) {
  if (value is Map<String, dynamic>) {
    return value;
  }
  if (value is Map) {
    return Map<String, dynamic>.from(value);
  }
  return <String, dynamic>{};
}

Map<String, dynamic> _decodeJsonObject(Object? value) {
  if (value is String && value.trim().isNotEmpty) {
    try {
      final decoded = jsonDecode(value);
      return _jsonObjectFromDynamic(decoded);
    } catch (_) {
      return <String, dynamic>{};
    }
  }

  return _jsonObjectFromDynamic(value);
}

List<String> _stringListFromDynamic(Object? value) {
  if (value is List) {
    return value.map((item) => '$item'.trim()).where((item) => item.isNotEmpty).toList();
  }
  return <String>[];
}

String _normalizeError(Object error) {
  return error.toString().replaceFirst('Exception: ', '');
}

int _intValue(Object? value) {
  if (value is num) {
    return value.toInt();
  }
  return int.tryParse('$value') ?? 0;
}

String _stringValue(Map<String, dynamic> data, List<String> keys, {String fallback = ''}) {
  for (final key in keys) {
    final value = data[key];
    if (value == null) {
      continue;
    }
    final text = '$value'.trim();
    if (text.isNotEmpty) {
      return text;
    }
  }
  return fallback;
}

String _plainText(String input) {
  final withoutTags = input.replaceAll(RegExp(r'<[^>]*>'), ' ');
  return withoutTags.replaceAll(RegExp(r'\s+'), ' ').trim();
}

String _formatDateLabel(String input) {
  final value = input.trim();
  if (value.isEmpty) {
    return '';
  }

  final parsed = DateFormatters.parse(value);
  if (parsed == null) {
    return value;
  }

  final hasTime = parsed.hour != 0 || parsed.minute != 0 || parsed.second != 0;
  return hasTime ? DateFormatters.dateTime(parsed) : DateFormatters.date(parsed);
}

String _textToHtml(String input) {
  final normalized = input
      .trim()
      .split(RegExp(r'\r?\n'))
      .where((line) => line.trim().isNotEmpty)
      .map((line) => '<p>${_escapeHtml(line.trim())}</p>')
      .join();
  return normalized;
}

String _escapeHtml(String input) {
  return input
      .replaceAll('&', '&amp;')
      .replaceAll('<', '&lt;')
      .replaceAll('>', '&gt;')
      .replaceAll('"', '&quot;')
      .replaceAll("'", '&#39;');
}

String _resolveVideoUrl(Map<String, dynamic> video) {
  final sourceType = _stringValue(video, ['source_type']);
  if (sourceType == 'youtube') {
    return _stringValue(video, ['youtube_url']);
  }
  return _stringValue(video, ['stream_url']);
}

String _resolveAppUrl(String path) {
  if (path.startsWith('http://') || path.startsWith('https://')) {
    return path;
  }

  final trimmedBase = AppConfig.baseUrl.replaceFirst(RegExp(r'/+$'), '');
  final normalizedPath = path.startsWith('/') ? path.substring(1) : path;
  return '$trimmedBase/$normalizedPath';
}

class _MediaPreview extends StatelessWidget {
  const _MediaPreview({
    required this.imageUrl,
    required this.height,
    this.borderRadius = 20,
  });

  final String imageUrl;
  final double height;
  final double borderRadius;

  @override
  Widget build(BuildContext context) {
    return ClipRRect(
      borderRadius: BorderRadius.circular(borderRadius),
      child: Image.network(
        imageUrl,
        height: height,
        width: double.infinity,
        fit: BoxFit.cover,
        errorBuilder: (context, error, stackTrace) {
          return Container(
            height: height,
            width: double.infinity,
            color: Theme.of(context).colorScheme.surfaceContainerHighest,
            alignment: Alignment.center,
            padding: const EdgeInsets.all(20),
            child: Column(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                const Icon(Icons.image_not_supported_outlined, size: 36),
                const SizedBox(height: 12),
                Text(
                  'A imagem de referência não pôde ser carregada neste momento.',
                  textAlign: TextAlign.center,
                  style: Theme.of(context).textTheme.bodyMedium,
                ),
              ],
            ),
          );
        },
      ),
    );
  }
}

class _InlineAudioPlayerCard extends StatefulWidget {
  const _InlineAudioPlayerCard({
    required this.title,
    required this.audioUrl,
    this.audioHeaders = const <String, String>{},
    this.onCompleted,
  });

  final String title;
  final String audioUrl;
  final Map<String, String> audioHeaders;
  final VoidCallback? onCompleted;

  @override
  State<_InlineAudioPlayerCard> createState() => _InlineAudioPlayerCardState();
}

class _InlineAudioPlayerCardState extends State<_InlineAudioPlayerCard> {
  late final AudioPlayer _player;
  String? _error;
  bool _completedNotified = false;
  bool _loading = true;

  @override
  void initState() {
    super.initState();
    _player = AudioPlayer();
    _player.playerStateStream.listen((state) {
      if (!mounted) {
        return;
      }
      if (state.processingState == ProcessingState.completed && !_completedNotified) {
        _completedNotified = true;
        widget.onCompleted?.call();
      }
      if (state.processingState != ProcessingState.loading && state.processingState != ProcessingState.buffering && _loading) {
        setState(() {
          _loading = false;
        });
      }
    }, onError: (Object error, StackTrace stackTrace) {
      if (!mounted) {
        return;
      }
      setState(() {
        _loading = false;
        _error = _normalizeError(error);
      });
    });
    _prepare();
  }

  Future<void> _prepare() async {
    final url = widget.audioUrl.trim();
    if (url.isEmpty) {
      setState(() {
        _loading = false;
        _error = 'Áudio indisponível no momento.';
      });
      return;
    }

    try {
      final audioFile = await _resolveAudioFile(url);
      await _player.setFilePath(audioFile.path);
      if (mounted) {
        setState(() {
          _loading = false;
        });
      }
    } catch (error) {
      debugPrint('Falha ao preparar áudio $url: $error');
      if (!mounted) {
        return;
      }
      setState(() {
        _loading = false;
        _error = _normalizeError(error);
      });
    }
  }

  Future<File> _resolveAudioFile(String url) async {
    final uri = Uri.parse(url);
    final extensionMatch = RegExp(r'(\.[A-Za-z0-9]+)$').firstMatch(uri.path);
    final extension = extensionMatch?.group(1) ?? '.bin';
    final cachedFile = File('${Directory.systemTemp.path}/terapia_audio_${url.hashCode}$extension');

    if (await cachedFile.exists() && await cachedFile.length() > 0) {
      return cachedFile;
    }

    final response = await http.get(uri, headers: widget.audioHeaders);
    if (response.statusCode >= 400 || response.bodyBytes.isEmpty) {
      throw Exception('Falha ao baixar o áudio (${response.statusCode}).');
    }

    await cachedFile.writeAsBytes(response.bodyBytes, flush: true);
    return cachedFile;
  }

  @override
  void dispose() {
    _player.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Card(
      margin: EdgeInsets.zero,
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(widget.title, style: Theme.of(context).textTheme.titleMedium),
            const SizedBox(height: 12),
            if (_loading)
              const LinearProgressIndicator()
            else if (_error != null)
              Text(_error!, style: TextStyle(color: Theme.of(context).colorScheme.error))
            else ...[
              StreamBuilder<PlayerState>(
                stream: _player.playerStateStream,
                builder: (context, snapshot) {
                  final playerState = snapshot.data;
                  final isPlaying = playerState?.playing == true;
                  final processingState = playerState?.processingState;
                  final busy = processingState == ProcessingState.loading || processingState == ProcessingState.buffering;

                  return Row(
                    children: [
                      FilledButton.icon(
                        onPressed: busy
                            ? null
                            : () async {
                                if (processingState == ProcessingState.completed) {
                                  await _player.seek(Duration.zero);
                                }
                                if (isPlaying) {
                                  await _player.pause();
                                } else {
                                  await _player.play();
                                }
                              },
                        icon: Icon(isPlaying ? Icons.pause : Icons.play_arrow),
                        label: Text(isPlaying ? 'Pausar' : 'Ouvir áudio'),
                      ),
                      const SizedBox(width: 12),
                      if (processingState == ProcessingState.completed)
                        const Text('Áudio concluído')
                      else if (busy)
                        const Text('Carregando...'),
                    ],
                  );
                },
              ),
              const SizedBox(height: 12),
              StreamBuilder<Duration>(
                stream: _player.positionStream,
                builder: (context, positionSnapshot) {
                  return StreamBuilder<Duration?>(
                    stream: _player.durationStream,
                    builder: (context, durationSnapshot) {
                      final duration = durationSnapshot.data ?? Duration.zero;
                      final position = positionSnapshot.data ?? Duration.zero;
                      final max = duration.inMilliseconds <= 0 ? 1.0 : duration.inMilliseconds.toDouble();
                      final value = position.inMilliseconds.clamp(0, max.toInt()).toDouble();

                      return Column(
                        children: [
                          Slider(
                            min: 0,
                            max: max,
                            value: value,
                            onChanged: duration.inMilliseconds <= 0
                                ? null
                                : (nextValue) => _player.seek(Duration(milliseconds: nextValue.round())),
                          ),
                          Row(
                            mainAxisAlignment: MainAxisAlignment.spaceBetween,
                            children: [
                              Text(_formatAudioDuration(position)),
                              Text(_formatAudioDuration(duration)),
                            ],
                          ),
                        ],
                      );
                    },
                  );
                },
              ),
            ],
          ],
        ),
      ),
    );
  }
}

String _resolveMediaUrl(String path) {
  if (path.startsWith('http://') || path.startsWith('https://')) {
    return path;
  }

  final normalizedPath = path.startsWith('/') ? path.substring(1) : path;
  final baseUrl = normalizedPath.startsWith('uploads/') ? AppConfig.mediaBaseUrl : AppConfig.baseUrl;
  final trimmedBase = baseUrl.replaceFirst(RegExp(r'/+$'), '');
  return '$trimmedBase/$normalizedPath';
}

String _resolveAudioUrl(Map<String, dynamic> item) {
  final directPath = _stringValue(item, ['audio_path']);
  if (directPath.isNotEmpty) {
    return _resolveMediaUrl(directPath);
  }

  return _stringValue(item, ['audio_url']);
}

String _formatAudioDuration(Duration duration) {
  final totalSeconds = duration.inSeconds;
  final minutes = totalSeconds ~/ 60;
  final seconds = totalSeconds % 60;
  return '${minutes.toString().padLeft(2, '0')}:${seconds.toString().padLeft(2, '0')}';
}