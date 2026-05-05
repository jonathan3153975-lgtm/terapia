import 'package:flutter/material.dart';
import 'package:url_launcher/url_launcher.dart';

import '../../../core/network/api_client.dart';
import '../../../core/services/analytics_service.dart';
import '../../../core/utils/date_formatters.dart';

class SubscriptionPage extends StatefulWidget {
  const SubscriptionPage({
    super.key,
    required this.apiClient,
    required this.analyticsService,
    required this.profile,
    required this.onProfileChanged,
  });

  final ApiClient apiClient;
  final AnalyticsService analyticsService;
  final Map<String, dynamic>? profile;
  final Future<void> Function(Map<String, dynamic> profile) onProfileChanged;

  @override
  State<SubscriptionPage> createState() => _SubscriptionPageState();
}

class _SubscriptionPageState extends State<SubscriptionPage> {
  List<Map<String, dynamic>> _plans = <Map<String, dynamic>>[];
  Map<String, dynamic>? _activeSubscription;
  Map<String, dynamic>? _latestSubscription;
  String? _error;
  bool _loading = true;
  int? _checkoutPlanId;
  bool _mercadoPagoConfigured = false;

  @override
  void initState() {
    super.initState();
    _activeSubscription = widget.profile?['subscription'] as Map<String, dynamic>?;
    _load();
  }

  @override
  void didUpdateWidget(covariant SubscriptionPage oldWidget) {
    super.didUpdateWidget(oldWidget);
    if (widget.profile != oldWidget.profile) {
      _activeSubscription = widget.profile?['subscription'] as Map<String, dynamic>?;
    }
  }

  Future<void> _load() async {
    setState(() {
      _loading = true;
      _error = null;
    });

    try {
      final response = await widget.apiClient.fetchSubscriptionPlans();
      final data = response['data'] as Map<String, dynamic>? ?? <String, dynamic>{};
      final rawPlans = data['plans'] as List<dynamic>? ?? <dynamic>[];

      if (mounted) {
        setState(() {
          _plans = rawPlans.whereType<Map<String, dynamic>>().toList();
          _activeSubscription = data['active_subscription'] as Map<String, dynamic>? ?? _activeSubscription;
          _latestSubscription = data['latest_subscription'] as Map<String, dynamic>?;
          _mercadoPagoConfigured = data['mercado_pago_configured'] == true;
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

  Future<void> _startCheckout(Map<String, dynamic> plan) async {
    final planId = (plan['id'] as num?)?.toInt();
    if (planId == null) {
      return;
    }

    setState(() {
      _checkoutPlanId = planId;
    });

    try {
      final response = await widget.apiClient.startSubscriptionCheckout(planId: planId);
      final data = response['data'] as Map<String, dynamic>? ?? <String, dynamic>{};
      final checkoutUrl = data['checkout_url'] as String? ?? '';
      if (checkoutUrl.isEmpty) {
        throw Exception('Checkout indisponível no momento.');
      }

      await widget.analyticsService.track('subscription_checkout_start', context: 'subscription', payload: {'plan_id': planId});
      final launched = await launchUrl(Uri.parse(checkoutUrl), mode: LaunchMode.externalApplication);
      if (!launched) {
        throw Exception('Não foi possível abrir o checkout no dispositivo.');
      }

      final me = await widget.apiClient.fetchMe();
      final profile = me['data']['profile'] as Map<String, dynamic>? ?? <String, dynamic>{};
      await widget.onProfileChanged(profile);
      await _load();
    } catch (error) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(error.toString().replaceFirst('Exception: ', ''))));
      }
    } finally {
      if (mounted) {
        setState(() {
          _checkoutPlanId = null;
        });
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final active = _activeSubscription;
    final latest = _latestSubscription;

    return RefreshIndicator(
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
                  Text('Assinatura', style: Theme.of(context).textTheme.titleLarge),
                  const SizedBox(height: 8),
                  Text(active != null ? 'Sua assinatura está ativa.' : 'Você ainda não possui assinatura ativa.'),
                ],
              ),
            ),
          ),
          const SizedBox(height: 16),
          if (active != null)
            _SubscriptionStatusCard(
              title: 'Plano ativo',
              subscription: active,
            )
          else if (latest != null)
            _SubscriptionStatusCard(
              title: 'Última assinatura',
              subscription: latest,
            ),
          if (active != null || latest != null) const SizedBox(height: 16),
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
            Card(
              child: Padding(
                padding: const EdgeInsets.all(20),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text('Planos disponíveis', style: Theme.of(context).textTheme.titleLarge),
                    const SizedBox(height: 8),
                    Text(_mercadoPagoConfigured ? 'Selecione um plano para abrir o checkout.' : 'O checkout está indisponível no momento.'),
                  ],
                ),
              ),
            ),
            const SizedBox(height: 16),
            if (_plans.isEmpty)
              const Card(
                child: Padding(
                  padding: EdgeInsets.all(20),
                  child: Text('Nenhum plano liberado para este paciente até o momento.'),
                ),
              )
            else
              ..._plans.map(
                (plan) => Padding(
                  padding: const EdgeInsets.only(bottom: 12),
                  child: Card(
                    child: Padding(
                      padding: const EdgeInsets.all(20),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text('${plan['name'] ?? 'Plano'}', style: Theme.of(context).textTheme.titleLarge),
                          const SizedBox(height: 8),
                          Text('${plan['description_text'] ?? 'Sem descrição.'}'),
                          const SizedBox(height: 12),
                          Text('Ciclo: ${plan['billing_cycle'] ?? 'mensal'} | Valor: R\$ ${_formatPrice(plan['price'])}'),
                          const SizedBox(height: 16),
                          ElevatedButton(
                            onPressed: !_mercadoPagoConfigured || _checkoutPlanId != null ? null : () => _startCheckout(plan),
                            child: _checkoutPlanId == plan['id']
                                ? const SizedBox(width: 22, height: 22, child: CircularProgressIndicator(strokeWidth: 2.2, color: Colors.white))
                                : const Text('Assinar plano'),
                          ),
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

  String _formatPrice(Object? value) {
    final amount = value is num ? value.toDouble() : double.tryParse('$value') ?? 0;
    return amount.toStringAsFixed(2).replaceAll('.', ',');
  }
}

class _SubscriptionStatusCard extends StatelessWidget {
  const _SubscriptionStatusCard({
    required this.title,
    required this.subscription,
  });

  final String title;
  final Map<String, dynamic> subscription;

  @override
  Widget build(BuildContext context) {
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(20),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(title, style: Theme.of(context).textTheme.titleLarge),
            const SizedBox(height: 12),
            Text('Plano: ${subscription['plan_name'] ?? 'Não informado'}'),
            Text('Status: ${subscription['status'] ?? 'pending'}'),
            Text('Ciclo: ${subscription['billing_cycle'] ?? 'mensal'}'),
            Text('Valor: R\$ ${subscription['amount'] ?? '0,00'}'),
            if (subscription['starts_at'] != null) Text('Início: ${DateFormatters.date(subscription['starts_at'])}'),
            if (subscription['ends_at'] != null) Text('Fim: ${DateFormatters.date(subscription['ends_at'])}'),
          ],
        ),
      ),
    );
  }
}