import 'package:flutter/material.dart';

import '../../../core/network/api_client.dart';
import '../../../core/services/analytics_service.dart';

class AccountPage extends StatefulWidget {
  const AccountPage({
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
  State<AccountPage> createState() => _AccountPageState();
}

class _AccountPageState extends State<AccountPage> {
  late final TextEditingController _nameController;
  late final TextEditingController _emailController;
  late final TextEditingController _phoneController;
  final _newPasswordController = TextEditingController();
  final _confirmPasswordController = TextEditingController();

  bool _savingProfile = false;
  bool _savingPassword = false;

  @override
  void initState() {
    super.initState();
    final user = widget.profile?['user'] as Map<String, dynamic>? ?? <String, dynamic>{};
    _nameController = TextEditingController(text: '${user['name'] ?? ''}');
    _emailController = TextEditingController(text: '${user['email'] ?? ''}');
    _phoneController = TextEditingController(text: '${user['phone'] ?? ''}');
  }

  @override
  void didUpdateWidget(covariant AccountPage oldWidget) {
    super.didUpdateWidget(oldWidget);
    if (widget.profile != oldWidget.profile) {
      final user = widget.profile?['user'] as Map<String, dynamic>? ?? <String, dynamic>{};
      _nameController.text = '${user['name'] ?? ''}';
      _emailController.text = '${user['email'] ?? ''}';
      _phoneController.text = '${user['phone'] ?? ''}';
    }
  }

  @override
  void dispose() {
    _nameController.dispose();
    _emailController.dispose();
    _phoneController.dispose();
    _newPasswordController.dispose();
    _confirmPasswordController.dispose();
    super.dispose();
  }

  Future<void> _saveProfile() async {
    setState(() {
      _savingProfile = true;
    });

    try {
      await widget.apiClient.updateMyAccountProfile(
        name: _nameController.text.trim(),
        email: _emailController.text.trim(),
        phone: _phoneController.text.trim(),
      );
      await widget.analyticsService.track('my_account_update', context: 'account');
      final me = await widget.apiClient.fetchMe();
      final profile = me['data']['profile'] as Map<String, dynamic>? ?? <String, dynamic>{};
      await widget.onProfileChanged(profile);

      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Dados atualizados com sucesso.')));
      }
    } catch (error) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(error.toString().replaceFirst('Exception: ', ''))));
      }
    } finally {
      if (mounted) {
        setState(() {
          _savingProfile = false;
        });
      }
    }
  }

  Future<void> _savePassword() async {
    setState(() {
      _savingPassword = true;
    });

    try {
      await widget.apiClient.updateMyPassword(
        newPassword: _newPasswordController.text,
        confirmPassword: _confirmPasswordController.text,
      );
      await widget.analyticsService.track('my_account_password_update', context: 'account');
      _newPasswordController.clear();
      _confirmPasswordController.clear();

      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Senha atualizada com sucesso.')));
      }
    } catch (error) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(error.toString().replaceFirst('Exception: ', ''))));
      }
    } finally {
      if (mounted) {
        setState(() {
          _savingPassword = false;
        });
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final patient = widget.profile?['patient'] as Map<String, dynamic>? ?? <String, dynamic>{};

    return ListView(
      padding: const EdgeInsets.all(24),
      children: [
        Card(
          child: Padding(
            padding: const EdgeInsets.all(20),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text('Minha conta', style: Theme.of(context).textTheme.titleLarge),
                const SizedBox(height: 8),
                Text('Status: ${patient['status'] ?? 'inactive'} | Revisão: ${patient['review_status'] ?? 'pending'}'),
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
                Text('Dados pessoais', style: Theme.of(context).textTheme.titleLarge),
                const SizedBox(height: 16),
                TextField(controller: _nameController, decoration: const InputDecoration(labelText: 'Nome')),
                const SizedBox(height: 12),
                TextField(controller: _emailController, keyboardType: TextInputType.emailAddress, decoration: const InputDecoration(labelText: 'E-mail')),
                const SizedBox(height: 12),
                TextField(controller: _phoneController, keyboardType: TextInputType.phone, decoration: const InputDecoration(labelText: 'Telefone')),
                const SizedBox(height: 16),
                ElevatedButton(
                  onPressed: _savingProfile ? null : _saveProfile,
                  child: _savingProfile
                      ? const SizedBox(width: 22, height: 22, child: CircularProgressIndicator(strokeWidth: 2.2, color: Colors.white))
                      : const Text('Salvar dados'),
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
                Text('Alterar senha', style: Theme.of(context).textTheme.titleLarge),
                const SizedBox(height: 16),
                TextField(controller: _newPasswordController, obscureText: true, decoration: const InputDecoration(labelText: 'Nova senha')),
                const SizedBox(height: 12),
                TextField(controller: _confirmPasswordController, obscureText: true, decoration: const InputDecoration(labelText: 'Confirmar nova senha')),
                const SizedBox(height: 16),
                ElevatedButton(
                  onPressed: _savingPassword ? null : _savePassword,
                  child: _savingPassword
                      ? const SizedBox(width: 22, height: 22, child: CircularProgressIndicator(strokeWidth: 2.2, color: Colors.white))
                      : const Text('Atualizar senha'),
                ),
              ],
            ),
          ),
        ),
      ],
    );
  }
}