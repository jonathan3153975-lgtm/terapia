import 'package:flutter/material.dart';

import '../../../core/network/api_client.dart';
import '../../../core/services/appointment_notification_service.dart';
import '../../../core/services/analytics_service.dart';
import '../../../core/theme/app_theme.dart';
import '../../../core/utils/date_formatters.dart';
import '../../account/presentation/account_page.dart';
import '../../subscription/presentation/subscription_page.dart';
import 'patient_modules.dart';

enum _PatientModuleId {
  dashboard,
  tasks,
  treeOfLife,
  breathingGame,
  materials,
  books,
  videos,
  messenger,
  fatherWord,
  meditations,
  prayers,
  gratitude,
  devotionals,
  subscription,
  account,
}

class _PatientModuleItem {
  const _PatientModuleItem({
    required this.id,
    required this.label,
    required this.icon,
    required this.description,
    this.available = false,
  });

  final _PatientModuleId id;
  final String label;
  final IconData icon;
  final String description;
  final bool available;
}

class HomePage extends StatefulWidget {
  const HomePage({
    super.key,
    required this.apiClient,
    required this.analyticsService,
    required this.onLogout,
    required this.profile,
  });

  final ApiClient apiClient;
  final AnalyticsService analyticsService;
  final Future<void> Function() onLogout;
  final Map<String, dynamic>? profile;

  @override
  State<HomePage> createState() => _HomePageState();
}

class _HomePageState extends State<HomePage> {
  late Map<String, dynamic>? _profile;
  late final List<_PatientModuleItem> _menuItems;
  _PatientModuleId _selectedModule = _PatientModuleId.dashboard;

  bool get _hasActiveSubscription => _profile?['subscription'] is Map<String, dynamic>;

  @override
  void initState() {
    super.initState();
    _profile = widget.profile;
    if (!_hasActiveSubscription) {
      _selectedModule = _PatientModuleId.subscription;
    }
    _menuItems = const [
      _PatientModuleItem(
        id: _PatientModuleId.dashboard,
        label: 'Painel principal',
        icon: Icons.dashboard_outlined,
        description: 'Resumo do progresso, sessões e tarefas.',
        available: true,
      ),
      _PatientModuleItem(
        id: _PatientModuleId.tasks,
        label: 'Tarefas',
        icon: Icons.task_alt_outlined,
        description: 'Atividades recebidas e respostas enviadas.',
        available: true,
      ),
      _PatientModuleItem(
        id: _PatientModuleId.breathingGame,
        label: 'Exercício de respiração',
        icon: Icons.air,
        description: 'Prática guiada de presença em três ciclos.',
        available: true,
      ),
      _PatientModuleItem(
        id: _PatientModuleId.materials,
        label: 'Materiais',
        icon: Icons.folder_open_outlined,
        description: 'Conteúdos liberados pelo terapeuta.',
        available: true,
      ),
      _PatientModuleItem(
        id: _PatientModuleId.books,
        label: 'Livros',
        icon: Icons.menu_book_outlined,
        description: 'Biblioteca e favoritos do paciente.',
        available: true,
      ),
      _PatientModuleItem(
        id: _PatientModuleId.videos,
        label: 'Vídeos',
        icon: Icons.ondemand_video_outlined,
        description: 'TeraTube, avaliações e comentários.',
        available: true,
      ),
      _PatientModuleItem(
        id: _PatientModuleId.messenger,
        label: 'Mensageiro',
        icon: Icons.chat_bubble_outline,
        description: 'Entradas reflexivas e sorteios do mensageiro.',
        available: true,
      ),
      _PatientModuleItem(
        id: _PatientModuleId.fatherWord,
        label: 'Pai, fala comigo',
        icon: Icons.auto_awesome_outlined,
        description: 'Palavras de fé, reflexão e acolhimento.',
        available: true,
      ),
      _PatientModuleItem(
        id: _PatientModuleId.meditations,
        label: 'Meditações',
        icon: Icons.self_improvement_outlined,
        description: 'Meditações guiadas e cartas de cura.',
        available: true,
      ),
      _PatientModuleItem(
        id: _PatientModuleId.prayers,
        label: 'Orações',
        icon: Icons.volunteer_activism_outlined,
        description: 'Orações, áudios e reflexões salvas.',
        available: true,
      ),
      _PatientModuleItem(
        id: _PatientModuleId.gratitude,
        label: 'Gratidão',
        icon: Icons.favorite_border,
        description: 'Diário da gratidão e ciclos do paciente.',
        available: true,
      ),
      _PatientModuleItem(
        id: _PatientModuleId.devotionals,
        label: 'Devocionais',
        icon: Icons.auto_stories_outlined,
        description: 'Devocional diário e histórico de reflexões.',
        available: true,
      ),
      _PatientModuleItem(
        id: _PatientModuleId.subscription,
        label: 'Assinatura',
        icon: Icons.workspace_premium_outlined,
        description: 'Plano ativo, histórico e checkout.',
        available: true,
      ),
      _PatientModuleItem(
        id: _PatientModuleId.account,
        label: 'Minha conta',
        icon: Icons.person_outline,
        description: 'Dados pessoais, senha e status do cadastro.',
        available: true,
      ),
    ];
  }

  Future<void> _handleProfileChanged(Map<String, dynamic> profile) async {
    setState(() {
      _profile = profile;
      if (!_hasActiveSubscription && _selectedModule == _PatientModuleId.dashboard) {
        _selectedModule = _PatientModuleId.subscription;
      }
    });
  }

  bool _moduleNeedsSubscription(_PatientModuleId module) {
    switch (module) {
      case _PatientModuleId.tasks:
      case _PatientModuleId.subscription:
      case _PatientModuleId.account:
        return false;
      default:
        return true;
    }
  }

  void _showSubscriptionRequiredMessage() {
    ScaffoldMessenger.of(context).showSnackBar(
      const SnackBar(content: Text('Escolha um plano para continuar usando a plataforma.')),
    );
  }

  void _selectModule(_PatientModuleId module, {bool showFeedback = false}) {
    if (_moduleNeedsSubscription(module) && !_hasActiveSubscription) {
      setState(() {
        _selectedModule = _PatientModuleId.subscription;
      });
      if (showFeedback) {
        _showSubscriptionRequiredMessage();
      }
      return;
    }

    setState(() {
      _selectedModule = module;
    });
  }

  @override
  void didUpdateWidget(covariant HomePage oldWidget) {
    super.didUpdateWidget(oldWidget);
    if (widget.profile != oldWidget.profile) {
      _profile = widget.profile;
      if (!_hasActiveSubscription && _selectedModule == _PatientModuleId.dashboard) {
        _selectedModule = _PatientModuleId.subscription;
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final user = _profile?['user'] as Map<String, dynamic>? ?? <String, dynamic>{};
    final pages = <_PatientModuleId, Widget>{
      _PatientModuleId.dashboard: _DashboardTab(
        apiClient: widget.apiClient,
        analyticsService: widget.analyticsService,
        onSelectModule: (module) => _selectModule(module, showFeedback: true),
      ),
      _PatientModuleId.tasks: TasksPage(
        apiClient: widget.apiClient,
        analyticsService: widget.analyticsService,
      ),
      _PatientModuleId.treeOfLife: TreeOfLifePage(
        apiClient: widget.apiClient,
        analyticsService: widget.analyticsService,
      ),
      _PatientModuleId.breathingGame: BreathingGamePage(
        apiClient: widget.apiClient,
        analyticsService: widget.analyticsService,
        onExit: () => _selectModule(_PatientModuleId.dashboard),
      ),
      _PatientModuleId.materials: MaterialsPage(
        apiClient: widget.apiClient,
        analyticsService: widget.analyticsService,
      ),
      _PatientModuleId.books: BooksPage(
        apiClient: widget.apiClient,
        analyticsService: widget.analyticsService,
      ),
      _PatientModuleId.videos: VideosPage(
        apiClient: widget.apiClient,
        analyticsService: widget.analyticsService,
      ),
      _PatientModuleId.messenger: MessengerPage(
        apiClient: widget.apiClient,
        analyticsService: widget.analyticsService,
      ),
      _PatientModuleId.fatherWord: FatherWordPage(
        apiClient: widget.apiClient,
        analyticsService: widget.analyticsService,
      ),
      _PatientModuleId.meditations: MeditationsPage(
        apiClient: widget.apiClient,
        analyticsService: widget.analyticsService,
      ),
      _PatientModuleId.prayers: PrayersPage(
        apiClient: widget.apiClient,
        analyticsService: widget.analyticsService,
      ),
      _PatientModuleId.gratitude: GratitudePage(
        apiClient: widget.apiClient,
        analyticsService: widget.analyticsService,
      ),
      _PatientModuleId.devotionals: DevotionalsPage(
        apiClient: widget.apiClient,
        analyticsService: widget.analyticsService,
      ),
      _PatientModuleId.subscription: SubscriptionPage(
        apiClient: widget.apiClient,
        analyticsService: widget.analyticsService,
        profile: _profile,
        onProfileChanged: _handleProfileChanged,
      ),
      _PatientModuleId.account: AccountPage(
        apiClient: widget.apiClient,
        analyticsService: widget.analyticsService,
        profile: _profile,
        onProfileChanged: _handleProfileChanged,
      ),
    };
    final selectedItem = _menuItems.firstWhere((item) => item.id == _selectedModule);
    final immersiveBreathing = _selectedModule == _PatientModuleId.breathingGame;

    return Scaffold(
      appBar: immersiveBreathing
          ? null
          : AppBar(
              title: Text(selectedItem.label == 'Painel principal' ? 'Olá, ${user['name'] ?? 'Paciente'}' : selectedItem.label),
              actions: [
                IconButton(
                  onPressed: widget.onLogout,
                  icon: const Icon(Icons.logout),
                ),
              ],
            ),
      drawer: immersiveBreathing
          ? null
          : Drawer(
        child: SafeArea(
          child: Column(
            children: [
              Padding(
                padding: const EdgeInsets.fromLTRB(20, 16, 20, 12),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text('Menu do paciente', style: Theme.of(context).textTheme.titleLarge),
                    const SizedBox(height: 8),
                    Text('${user['name'] ?? 'Paciente'}', style: Theme.of(context).textTheme.bodyLarge),
                    const SizedBox(height: 4),
                    Text('${user['email'] ?? ''}', style: Theme.of(context).textTheme.bodyMedium),
                  ],
                ),
              ),
              const Divider(height: 1),
              Expanded(
                child: ListView.separated(
                  padding: const EdgeInsets.symmetric(vertical: 8),
                  itemCount: _menuItems.length,
                  separatorBuilder: (_, __) => const SizedBox(height: 2),
                  itemBuilder: (context, index) {
                    final item = _menuItems[index];
                    final selected = item.id == _selectedModule;

                    return ListTile(
                      leading: Icon(item.icon),
                      title: Text(item.label),
                      subtitle: Text(item.available ? 'Disponível agora' : 'Próxima integração'),
                      selected: selected,
                      selectedTileColor: Theme.of(context).colorScheme.primary.withValues(alpha: 0.10),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(18)),
                      contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 4),
                      onTap: () {
                        _selectModule(item.id, showFeedback: true);
                        Navigator.of(context).pop();
                      },
                    );
                  },
                ),
              ),
            ],
          ),
        ),
      ),
      body: IndexedStack(
        index: _menuItems.indexWhere((item) => item.id == _selectedModule),
        children: _menuItems.map((item) => pages[item.id]!).toList(),
      ),
    );
  }
}

class _DashboardTab extends StatefulWidget {
  const _DashboardTab({
    required this.apiClient,
    required this.analyticsService,
    required this.onSelectModule,
  });

  final ApiClient apiClient;
  final AnalyticsService analyticsService;
  final ValueChanged<_PatientModuleId> onSelectModule;

  @override
  State<_DashboardTab> createState() => _DashboardTabState();
}

class _DashboardTabState extends State<_DashboardTab> {
  final AppointmentNotificationService _appointmentNotificationService = AppointmentNotificationService();

  Map<String, dynamic>? _dashboard;
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
      final response = await widget.apiClient.fetchDashboard();
      final dashboard = response['data'] as Map<String, dynamic>? ?? <String, dynamic>{};
      await widget.analyticsService.track('dashboard_view', context: 'home');
      await _appointmentNotificationService.syncTodayAppointments(_mapDynamicList(dashboard['today_appointments']));

      if (mounted) {
        setState(() {
          _dashboard = dashboard;
        });
      }
    } on ApiException catch (error) {
      if (mounted && error.code == 'subscription_required') {
        widget.onSelectModule(_PatientModuleId.subscription);
        WidgetsBinding.instance.addPostFrameCallback((_) {
          if (!mounted) {
            return;
          }
          ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(error.message)));
        });
        return;
      }

      if (mounted) {
        setState(() {
          _error = error.toString().replaceFirst('Exception: ', '');
        });
      }
    } catch (error) {
      if (mounted) {
        setState(() {
          _error = error.toString().replaceFirst('Exception: ', '');
        });
      }
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
    final summary = _dashboard?['summary'] as Map<String, dynamic>? ?? <String, dynamic>{};
    final nextAppointment = _dashboard?['next_appointment'] as Map<String, dynamic>? ?? <String, dynamic>{};
    final charts = _dashboard?['charts'] as Map<String, dynamic>? ?? <String, dynamic>{};
    final labels = _stringListFromDynamic(charts['labels']);
    final sessions = _numberListFromDynamic(charts['sessions']);
    final tasksDone = _numberListFromDynamic(charts['tasks_done']);
    final activeSubscription = _dashboard?['active_subscription'] as Map<String, dynamic>?;
    final hasNextAppointment = nextAppointment['session_date'] != null && '${nextAppointment['session_date']}'.trim().isNotEmpty;
    final nextAppointmentValue = hasNextAppointment ? _formatDateTimeLabel(nextAppointment['session_date'], fallback: 'Sem sessão agendada') : 'Sem sessão agendada';
    final nextAppointmentNote = hasNextAppointment
        ? 'Mantenha esse momento no radar.'
        : 'Nenhuma sessão futura foi encontrada até o momento.';
    final trendEntries = <_TrendEntry>[];
    for (var index = 0; index < labels.length; index += 1) {
      final sessionValue = index < sessions.length ? sessions[index] : 0;
      final taskValue = index < tasksDone.length ? tasksDone[index] : 0;
      if (sessionValue > 0 || taskValue > 0) {
        trendEntries.add(_TrendEntry(label: labels[index], sessions: sessionValue, tasksDone: taskValue));
      }
    }
    final maxTrend = trendEntries.fold<int>(1, (current, item) {
      final itemMax = item.sessions > item.tasksDone ? item.sessions : item.tasksDone;
      return itemMax > current ? itemMax : current;
    });

    return RefreshIndicator(
      onRefresh: _load,
      child: ListView(
        padding: const EdgeInsets.all(24),
        children: [
          if (_loading)
            const Center(
              child: Padding(
                padding: EdgeInsets.all(24),
                child: CircularProgressIndicator(),
              ),
            )
          else if (_error != null)
            Card(
              child: Padding(
                padding: const EdgeInsets.all(20),
                child: Text(_error!, style: TextStyle(color: Theme.of(context).colorScheme.error)),
              ),
            )
          else ...[
            Container(
              padding: const EdgeInsets.all(24),
              decoration: BoxDecoration(
                borderRadius: BorderRadius.circular(30),
                gradient: const LinearGradient(
                  begin: Alignment.topLeft,
                  end: Alignment.bottomRight,
                  colors: [AppTheme.textMain, AppTheme.accent, AppTheme.secondary],
                ),
                boxShadow: const [BoxShadow(color: Color(0x1C264653), blurRadius: 24, offset: Offset(0, 14))],
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 7),
                    decoration: BoxDecoration(
                      borderRadius: BorderRadius.circular(999),
                      color: AppTheme.cta.withValues(alpha: 0.94),
                    ),
                    child: const Text('Minha jornada', style: TextStyle(color: Colors.white, fontWeight: FontWeight.w800, letterSpacing: 1)),
                  ),
                  const SizedBox(height: 16),
                  Text(
                    'Seu espaço para acompanhar a terapia com mais clareza e acolhimento.',
                    style: Theme.of(context).textTheme.headlineMedium?.copyWith(color: Colors.white),
                  ),
                  const SizedBox(height: 10),
                  Text(
                    'Veja sua evolução, acompanhe a próxima sessão e retome rapidamente os conteúdos que ajudam no seu processo.',
                    style: Theme.of(context).textTheme.bodyLarge?.copyWith(color: Colors.white.withValues(alpha: 0.9)),
                  ),
                  const SizedBox(height: 16),
                  Wrap(
                    spacing: 10,
                    runSpacing: 10,
                    children: [
                      _DashboardChip(label: '${summary['days_since_register'] ?? 0} dias na jornada'),
                      _DashboardChip(label: '${summary['sessions_done'] ?? 0} sessões realizadas'),
                      _DashboardChip(label: '${summary['pending_tasks'] ?? 0} tarefas pendentes'),
                    ],
                  ),
                  const SizedBox(height: 16),
                  Row(
                    children: [
                      Expanded(
                        child: FilledButton.tonal(
                          onPressed: () => widget.onSelectModule(_PatientModuleId.materials),
                          child: const Text('Meus materiais'),
                        ),
                      ),
                      const SizedBox(width: 12),
                      Expanded(
                        child: OutlinedButton(
                          onPressed: () => widget.onSelectModule(_PatientModuleId.tasks),
                          style: OutlinedButton.styleFrom(
                            foregroundColor: Colors.white,
                            side: BorderSide(color: Colors.white.withValues(alpha: 0.35)),
                          ),
                          child: const Text('Minhas tarefas'),
                        ),
                      ),
                    ],
                  ),
                ],
              ),
            ),
            const SizedBox(height: 16),
            _DashboardKpiCard(
              title: 'Dias desde o cadastro',
              value: '${summary['days_since_register'] ?? 0}',
              note: 'Tempo de caminhada dentro da plataforma.',
              color: const Color(0xFFFFF0E8),
            ),
            const SizedBox(height: 12),
            _DashboardKpiCard(
              title: 'Sessões realizadas',
              value: '${summary['sessions_done'] ?? 0}',
              note: 'Encontros terapêuticos já vividos por você.',
              color: const Color(0xFFEFFAF4),
            ),
            const SizedBox(height: 12),
            _DashboardKpiCard(
              title: 'Tarefas pendentes',
              value: '${summary['pending_tasks'] ?? 0}',
              note: 'Concluídas: ${summary['done_tasks'] ?? 0} • Materiais: ${summary['received_materials'] ?? 0}',
              color: const Color(0xFFFFF8E7),
            ),
            const SizedBox(height: 12),
            _DashboardKpiCard(
              title: 'Próxima sessão',
              value: nextAppointmentValue,
              note: nextAppointmentNote,
              color: const Color(0xFFEEF6FB),
              denseValue: true,
            ),
            const SizedBox(height: 16),
            Card(
              child: Padding(
                padding: const EdgeInsets.all(20),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text('Minha evolução', style: Theme.of(context).textTheme.titleLarge),
                    const SizedBox(height: 8),
                    Text('Sessões e tarefas ao longo do tempo.', style: Theme.of(context).textTheme.bodyMedium?.copyWith(color: AppTheme.textSoft)),
                    const SizedBox(height: 16),
                    if (trendEntries.isEmpty)
                      const Text('Ainda não há períodos com sessões ou tarefas concluídas para exibir.')
                    else
                      for (final entry in trendEntries)
                        Padding(
                          padding: const EdgeInsets.only(bottom: 12),
                          child: _TrendRow(
                            label: entry.label,
                            sessions: entry.sessions,
                            tasksDone: entry.tasksDone,
                            maxValue: maxTrend,
                          ),
                        ),
                  ],
                ),
              ),
            ),
            const SizedBox(height: 16),
            Card(
              child: Padding(
                padding: const EdgeInsets.all(20),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text('Constância', style: Theme.of(context).textTheme.titleLarge),
                    const SizedBox(height: 8),
                    Text('Cada pequeno passo conta no seu processo.', style: Theme.of(context).textTheme.bodyMedium?.copyWith(color: AppTheme.textSoft)),
                    const SizedBox(height: 16),
                    Text('${summary['completion_rate'] ?? 0}%', style: Theme.of(context).textTheme.headlineMedium),
                    const SizedBox(height: 8),
                    LinearProgressIndicator(
                      value: ((summary['completion_rate'] as num?)?.toDouble() ?? 0) / 100,
                      minHeight: 10,
                      borderRadius: BorderRadius.circular(999),
                      backgroundColor: AppTheme.bgSoft,
                    ),
                    const SizedBox(height: 16),
                    _DashboardActionTile(
                      title: 'Meus conteúdos',
                      description: 'Retome materiais, vídeos e leituras salvos.',
                      onTap: () => widget.onSelectModule(_PatientModuleId.materials),
                    ),
                    const SizedBox(height: 10),
                    _DashboardActionTile(
                      title: 'Minhas tarefas',
                      description: 'Acompanhe atividades pendentes e respostas já enviadas.',
                      onTap: () => widget.onSelectModule(_PatientModuleId.tasks),
                    ),
                    const SizedBox(height: 10),
                    _DashboardActionTile(
                      title: 'Devocional',
                      description: 'Acesse reflexões e registros do seu dia.',
                      onTap: () => widget.onSelectModule(_PatientModuleId.devotionals),
                    ),
                    const SizedBox(height: 10),
                    _DashboardActionTile(
                      title: 'Diário da gratidão',
                      description: 'Registre seus ciclos de gratidão e acompanhe a jornada diária.',
                      onTap: () => widget.onSelectModule(_PatientModuleId.gratitude),
                    ),
                  ],
                ),
              ),
            ),
            if (activeSubscription != null) ...[
              const SizedBox(height: 16),
              Card(
                color: AppTheme.bgSoft,
                child: Padding(
                  padding: const EdgeInsets.all(18),
                  child: Row(
                    children: [
                      const Icon(Icons.workspace_premium_outlined, color: AppTheme.accent),
                      const SizedBox(width: 12),
                      Expanded(
                        child: Text(
                          'Assinatura ativa: ${activeSubscription['plan_name'] ?? 'Plano'}${activeSubscription['ends_at'] != null ? ' • válida até ${DateFormatters.date(activeSubscription['ends_at'])}' : ''}',
                        ),
                      ),
                    ],
                  ),
                ),
              ),
            ],
          ],
        ],
      ),
    );
  }
}

class _DashboardChip extends StatelessWidget {
  const _DashboardChip({required this.label});

  final String label;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 9),
      decoration: BoxDecoration(
        borderRadius: BorderRadius.circular(999),
        color: Colors.white.withValues(alpha: 0.14),
      ),
      child: Text(label, style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w700)),
    );
  }
}

class _DashboardKpiCard extends StatelessWidget {
  const _DashboardKpiCard({
    required this.title,
    required this.value,
    required this.note,
    required this.color,
    this.denseValue = false,
  });

  final String title;
  final String value;
  final String note;
  final Color color;
  final bool denseValue;

  @override
  Widget build(BuildContext context) {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(18),
      decoration: BoxDecoration(
        borderRadius: BorderRadius.circular(22),
        color: color,
        border: Border.all(color: AppTheme.line),
      ),
      child: LayoutBuilder(
        builder: (context, constraints) {
          final isTight = constraints.maxWidth < 340;
          return Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(title, style: Theme.of(context).textTheme.bodySmall?.copyWith(color: AppTheme.textSoft, fontWeight: FontWeight.w700)),
              const SizedBox(height: 12),
              Text(
                value,
                maxLines: denseValue && isTight ? 3 : null,
                overflow: denseValue && isTight ? TextOverflow.ellipsis : TextOverflow.visible,
                style: denseValue ? Theme.of(context).textTheme.titleLarge : Theme.of(context).textTheme.headlineMedium,
              ),
              const SizedBox(height: 8),
              Text(note, style: Theme.of(context).textTheme.bodySmall),
            ],
          );
        },
      ),
    );
  }
}

class _TrendEntry {
  const _TrendEntry({
    required this.label,
    required this.sessions,
    required this.tasksDone,
  });

  final String label;
  final int sessions;
  final int tasksDone;
}

class _DashboardActionTile extends StatelessWidget {
  const _DashboardActionTile({
    required this.title,
    required this.description,
    required this.onTap,
  });

  final String title;
  final String description;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    return InkWell(
      borderRadius: BorderRadius.circular(18),
      onTap: onTap,
      child: Ink(
        width: double.infinity,
        padding: const EdgeInsets.all(16),
        decoration: BoxDecoration(
          borderRadius: BorderRadius.circular(18),
          color: AppTheme.bgSoft,
          border: Border.all(color: AppTheme.line),
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(title, style: Theme.of(context).textTheme.titleMedium),
            const SizedBox(height: 4),
            Text(description, style: Theme.of(context).textTheme.bodySmall),
          ],
        ),
      ),
    );
  }
}

class _TrendRow extends StatelessWidget {
  const _TrendRow({
    required this.label,
    required this.sessions,
    required this.tasksDone,
    required this.maxValue,
  });

  final String label;
  final int sessions;
  final int tasksDone;
  final int maxValue;

  @override
  Widget build(BuildContext context) {
    final safeMax = maxValue <= 0 ? 1 : maxValue;
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(label, style: Theme.of(context).textTheme.bodySmall?.copyWith(fontWeight: FontWeight.w700)),
        const SizedBox(height: 8),
        _TrendBar(label: 'Sessões', value: sessions, maxValue: safeMax, color: AppTheme.accent),
        const SizedBox(height: 6),
        _TrendBar(label: 'Tarefas', value: tasksDone, maxValue: safeMax, color: AppTheme.cta),
      ],
    );
  }
}

class _TrendBar extends StatelessWidget {
  const _TrendBar({
    required this.label,
    required this.value,
    required this.maxValue,
    required this.color,
  });

  final String label;
  final int value;
  final int maxValue;
  final Color color;

  @override
  Widget build(BuildContext context) {
    final progress = maxValue <= 0 ? 0.0 : value / maxValue;
    return Row(
      children: [
        SizedBox(width: 58, child: Text(label, style: Theme.of(context).textTheme.bodySmall)),
        Expanded(
          child: ClipRRect(
            borderRadius: BorderRadius.circular(999),
            child: LinearProgressIndicator(
              value: progress.clamp(0, 1),
              minHeight: 8,
              backgroundColor: AppTheme.bgSoft,
              valueColor: AlwaysStoppedAnimation<Color>(color),
            ),
          ),
        ),
        const SizedBox(width: 10),
        Text('$value', style: Theme.of(context).textTheme.bodySmall?.copyWith(fontWeight: FontWeight.w700)),
      ],
    );
  }
}

List<Map<String, dynamic>> _mapDynamicList(Object? value) {
  if (value is List) {
    return value.map((item) => item is Map<String, dynamic> ? item : Map<String, dynamic>.from(item as Map)).toList();
  }
  return <Map<String, dynamic>>[];
}

List<String> _stringListFromDynamic(Object? value) {
  if (value is List) {
    return value.map((item) => '$item').toList();
  }
  return <String>[];
}

List<int> _numberListFromDynamic(Object? value) {
  if (value is List) {
    return value.map((item) => item is num ? item.toInt() : int.tryParse('$item') ?? 0).toList();
  }
  return <int>[];
}

String _formatDateTimeLabel(Object? value, {String fallback = '-'}) {
  return DateFormatters.dateTime(value, fallback: fallback);
}
