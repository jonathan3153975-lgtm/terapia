import 'package:flutter/material.dart';

import '../../../core/network/api_client.dart';
import '../../../core/services/analytics_service.dart';

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
      final dashboard = await widget.apiClient.fetchDashboard();
      await widget.analyticsService.track('dashboard_view', context: 'home');

      if (mounted) {
        setState(() {
          _dashboard = dashboard['data'] as Map<String, dynamic>?;
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
    final user = widget.profile?['user'] as Map<String, dynamic>? ?? <String, dynamic>{};

    return Scaffold(
      appBar: AppBar(
        title: Text('Olá, ${user['name'] ?? 'Paciente'}'),
        actions: [
          IconButton(
            onPressed: widget.onLogout,
            icon: const Icon(Icons.logout),
          ),
        ],
      ),
      body: RefreshIndicator(
        onRefresh: _load,
        child: ListView(
          padding: const EdgeInsets.all(24),
          children: [
            Card(
              child: Padding(
                padding: const EdgeInsets.all(20),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text('Painel do paciente', style: Theme.of(context).textTheme.titleLarge),
                    const SizedBox(height: 8),
                    const Text('Base Flutter criada para validar integração, design system, push e analytics.'),
                  ],
                ),
              ),
            ),
            const SizedBox(height: 16),
            if (_loading)
              const Center(child: Padding(
                padding: EdgeInsets.all(24),
                child: CircularProgressIndicator(),
              ))
            else if (_error != null)
              Card(
                child: Padding(
                  padding: const EdgeInsets.all(20),
                  child: Text(_error!, style: TextStyle(color: Theme.of(context).colorScheme.error)),
                ),
              )
            else ...[
              _MetricCard(title: 'Sessões concluídas', value: '${summary['sessions_done'] ?? 0}'),
              const SizedBox(height: 12),
              _MetricCard(title: 'Tarefas pendentes', value: '${summary['pending_tasks'] ?? 0}'),
              const SizedBox(height: 12),
              _MetricCard(title: 'Taxa de conclusão', value: '${summary['completion_rate'] ?? 0}%'),
            ],
            const SizedBox(height: 16),
            Card(
              child: Padding(
                padding: const EdgeInsets.all(20),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: const [
                    Text('Prioridades da v1'),
                    SizedBox(height: 10),
                    Text('1. Navegação móvel com foco em rotina diária.'),
                    Text('2. Registro de dispositivo para push.'),
                    Text('3. Eventos analíticos desde a primeira execução.'),
                  ],
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _MetricCard extends StatelessWidget {
  const _MetricCard({required this.title, required this.value});

  final String title;
  final String value;

  @override
  Widget build(BuildContext context) {
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(20),
        child: Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            Text(title, style: Theme.of(context).textTheme.bodyLarge),
            Text(value, style: Theme.of(context).textTheme.titleLarge),
          ],
        ),
      ),
    );
  }
}