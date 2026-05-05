import 'package:flutter/material.dart';
import 'package:url_launcher/url_launcher.dart';

import '../../../core/config/app_config.dart';
import '../../../core/theme/app_theme.dart';

class LoginPage extends StatefulWidget {
  const LoginPage({
    super.key,
    required this.onLogin,
    required this.baseUrl,
    this.authConfig,
    this.bootstrapWarning,
    this.initialEmail = '',
    this.initialRememberSession = true,
  });

  final Future<void> Function(String email, String password, bool rememberSession) onLogin;
  final String baseUrl;
  final Map<String, dynamic>? authConfig;
  final String? bootstrapWarning;
  final String initialEmail;
  final bool initialRememberSession;

  @override
  State<LoginPage> createState() => _LoginPageState();
}

class _LoginPageState extends State<LoginPage> {
  final _emailController = TextEditingController();
  final _passwordController = TextEditingController();
  final _emailFocusNode = FocusNode();
  final _passwordFocusNode = FocusNode();
  bool _loading = false;
  bool _rememberSession = true;
  String? _error;

  @override
  void initState() {
    super.initState();
    _rememberSession = widget.initialRememberSession;
    if (AppConfig.hasDevLogin) {
      _emailController.text = AppConfig.devLoginEmail;
      _passwordController.text = AppConfig.devLoginPassword;
    } else if (widget.initialEmail.trim().isNotEmpty) {
      _emailController.text = widget.initialEmail.trim();
    }

    WidgetsBinding.instance.addPostFrameCallback((_) {
      if (!mounted) {
        return;
      }

      if (_emailController.text.trim().isNotEmpty && _passwordController.text.trim().isEmpty) {
        _passwordFocusNode.requestFocus();
      } else {
        _emailFocusNode.requestFocus();
      }
    });
  }

  @override
  void dispose() {
    _emailController.dispose();
    _passwordController.dispose();
    _emailFocusNode.dispose();
    _passwordFocusNode.dispose();
    super.dispose();
  }

  String _copy(String key, String fallback) {
    final value = widget.authConfig?[key];
    if (value == null) {
      return fallback;
    }
    final text = '$value'.trim();
    return text.isEmpty ? fallback : text;
  }

  List<String> _stringList(String key, List<String> fallback) {
    final raw = widget.authConfig?[key];
    if (raw is List) {
      final values = raw.map((item) => '$item'.trim()).where((item) => item.isNotEmpty).toList();
      if (values.isNotEmpty) {
        return values;
      }
    }
    return fallback;
  }

  List<Map<String, String>> _cards(String key, List<Map<String, String>> fallback) {
    final raw = widget.authConfig?[key];
    if (raw is List) {
      final values = raw.whereType<Map>().map((item) {
        final mapped = Map<String, dynamic>.from(item);
        return <String, String>{
          'title': '${mapped['title'] ?? ''}'.trim(),
          'description': '${mapped['description'] ?? ''}'.trim(),
        };
      }).where((item) => (item['title'] ?? '').isNotEmpty).toList();
      if (values.isNotEmpty) {
        return values;
      }
    }
    return fallback;
  }

  Future<void> _openSignup() async {
    final signupUrl = _copy('signup_url', '');
    if (signupUrl.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Cadastro básico indisponível neste momento.')));
      return;
    }

    final launched = await launchUrl(Uri.parse(signupUrl), mode: LaunchMode.externalApplication);
    if (!launched && mounted) {
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Não foi possível abrir o cadastro básico.')));
    }
  }

  Future<void> _submit() async {
    setState(() {
      _loading = true;
      _error = null;
    });

    try {
      await widget.onLogin(_emailController.text.trim(), _passwordController.text, _rememberSession);
    } catch (error) {
      setState(() {
        _error = error.toString().replaceFirst('Exception: ', '');
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
    final featureChips = _stringList('features', const [
      'Mais cor e clareza',
      'Ambiente acolhedor',
      'Navegação fluida no celular',
    ]);
    final highlights = _cards('highlights', const [
      {
        'title': 'Rotina leve',
        'description': 'Agenda, tarefas e materiais em um fluxo simples.',
      },
      {
        'title': 'Cuidado contínuo',
        'description': 'Experiências guiadas para manter constância entre as sessões.',
      },
    ]);
    final systemInfo = _cards('system_info', const [
      {
        'title': 'Agenda organizada',
        'description': 'Visualize suas sessões e acompanhe seus próximos atendimentos.',
      },
      {
        'title': 'Tarefas terapêuticas',
        'description': 'Receba atividades práticas e marque seu progresso de forma simples.',
      },
      {
        'title': 'Materiais de apoio',
        'description': 'Acesse conteúdos enviados pelo terapeuta para fortalecer sua jornada.',
      },
      {
        'title': 'Ambiente seguro',
        'description': 'Seus dados ficam protegidos para um acompanhamento com confiança.',
      },
    ]);

    return Scaffold(
      resizeToAvoidBottomInset: true,
      body: GestureDetector(
        onTap: () => FocusScope.of(context).unfocus(),
        child: Container(
          decoration: const BoxDecoration(
            gradient: LinearGradient(
              begin: Alignment.topLeft,
              end: Alignment.bottomRight,
              colors: [Color(0xFF264653), Color(0xFF4D908E), Color(0xFF90BE6D), Color(0xFFF6BD60)],
              stops: [0, .4, .72, 1],
            ),
          ),
          child: Stack(
            children: [
              Positioned(
                left: -80,
                top: -40,
                child: Container(
                  width: 220,
                  height: 220,
                  decoration: BoxDecoration(
                    shape: BoxShape.circle,
                    color: AppTheme.affection.withValues(alpha: 0.24),
                  ),
                ),
              ),
              Positioned(
                right: -70,
                bottom: 80,
                child: Container(
                  width: 210,
                  height: 210,
                  decoration: BoxDecoration(
                    shape: BoxShape.circle,
                    color: AppTheme.accent.withValues(alpha: 0.18),
                  ),
                ),
              ),
              SafeArea(
                child: SingleChildScrollView(
                  keyboardDismissBehavior: ScrollViewKeyboardDismissBehavior.onDrag,
                  padding: EdgeInsets.fromLTRB(20, 20, 20, 24 + MediaQuery.of(context).viewInsets.bottom),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Container(
                        width: double.infinity,
                        padding: const EdgeInsets.all(24),
                        decoration: BoxDecoration(
                          borderRadius: BorderRadius.circular(30),
                          color: Colors.white.withValues(alpha: 0.12),
                          border: Border.all(color: Colors.white.withValues(alpha: 0.16)),
                        ),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Container(
                              padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 7),
                              decoration: BoxDecoration(
                                borderRadius: BorderRadius.circular(999),
                                gradient: const LinearGradient(colors: [AppTheme.cta, AppTheme.affection]),
                              ),
                              child: Text(
                                _copy('platform_subtitle', 'Bem-estar com presença'),
                                style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w800, letterSpacing: 1.1, fontSize: 11),
                              ),
                            ),
                            const SizedBox(height: 16),
                            Text(
                              _copy('platform_name', 'Tera-Tech'),
                              style: Theme.of(context).textTheme.headlineLarge?.copyWith(color: AppTheme.cta),
                            ),
                            const SizedBox(height: 10),
                            Text(
                              _copy('platform_description', 'Uma plataforma mais humana para organizar atendimentos, fortalecer a rotina terapêutica e deixar a jornada do paciente mais acolhedora.'),
                              style: Theme.of(context).textTheme.bodyLarge?.copyWith(color: Colors.white.withValues(alpha: 0.9)),
                            ),
                            const SizedBox(height: 18),
                            Wrap(
                              spacing: 10,
                              runSpacing: 10,
                              children: featureChips
                                  .map(
                                    (item) => Container(
                                      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
                                      decoration: BoxDecoration(
                                        borderRadius: BorderRadius.circular(999),
                                        color: Colors.white.withValues(alpha: 0.12),
                                        border: Border.all(color: Colors.white.withValues(alpha: 0.14)),
                                      ),
                                      child: Text(item, style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w700, fontSize: 12)),
                                    ),
                                  )
                                  .toList(),
                            ),
                            const SizedBox(height: 18),
                            ...highlights.map(
                              (item) => Padding(
                                padding: const EdgeInsets.only(bottom: 10),
                                child: Container(
                                  width: double.infinity,
                                  padding: const EdgeInsets.all(16),
                                  decoration: BoxDecoration(
                                    borderRadius: BorderRadius.circular(20),
                                    color: Colors.white.withValues(alpha: 0.1),
                                    border: Border.all(color: AppTheme.cta.withValues(alpha: 0.42)),
                                  ),
                                  child: Column(
                                    crossAxisAlignment: CrossAxisAlignment.start,
                                    children: [
                                      Text(item['title'] ?? '', style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w800)),
                                      const SizedBox(height: 4),
                                      Text(item['description'] ?? '', style: TextStyle(color: Colors.white.withValues(alpha: 0.88), height: 1.45)),
                                    ],
                                  ),
                                ),
                              ),
                            ),
                          ],
                        ),
                      ),
                      const SizedBox(height: 18),
                      Card(
                        child: Padding(
                          padding: const EdgeInsets.all(24),
                          child: AutofillGroup(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Row(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    Expanded(
                                      child: Column(
                                        crossAxisAlignment: CrossAxisAlignment.start,
                                        children: [
                                          Container(
                                            padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                                            decoration: BoxDecoration(
                                              borderRadius: BorderRadius.circular(999),
                                              color: AppTheme.bgSoft,
                                            ),
                                            child: Text('Acesso seguro', style: Theme.of(context).textTheme.bodySmall?.copyWith(color: AppTheme.accent, fontWeight: FontWeight.w800, letterSpacing: .9)),
                                          ),
                                          const SizedBox(height: 14),
                                          Text(_copy('login_title', 'Entrar na plataforma'), style: Theme.of(context).textTheme.titleLarge),
                                          const SizedBox(height: 4),
                                          Text(_copy('login_copy', 'Use seu e-mail e sua senha para continuar.'), style: Theme.of(context).textTheme.bodyMedium?.copyWith(color: AppTheme.textSoft)),
                                        ],
                                      ),
                                    ),
                                    Container(
                                      width: 52,
                                      height: 52,
                                      decoration: BoxDecoration(
                                        borderRadius: BorderRadius.circular(18),
                                        gradient: const LinearGradient(colors: [AppTheme.cta, AppTheme.affection]),
                                      ),
                                      child: const Icon(Icons.wb_sunny_outlined, color: Colors.white),
                                    ),
                                  ],
                                ),
                                const SizedBox(height: 22),
                                TextField(
                                  controller: _emailController,
                                  focusNode: _emailFocusNode,
                                  autofocus: _emailController.text.trim().isEmpty,
                                  keyboardType: TextInputType.emailAddress,
                                  textInputAction: TextInputAction.next,
                                  autofillHints: const [AutofillHints.username, AutofillHints.email],
                                  enableSuggestions: false,
                                  autocorrect: false,
                                  onSubmitted: (_) => _passwordFocusNode.requestFocus(),
                                  decoration: const InputDecoration(
                                    labelText: 'E-mail',
                                    prefixIcon: Icon(Icons.alternate_email_rounded),
                                  ),
                                ),
                                const SizedBox(height: 16),
                                TextField(
                                  controller: _passwordController,
                                  focusNode: _passwordFocusNode,
                                  obscureText: true,
                                  textInputAction: TextInputAction.done,
                                  autofillHints: const [AutofillHints.password],
                                  onSubmitted: (_) => _submit(),
                                  decoration: const InputDecoration(
                                    labelText: 'Senha',
                                    prefixIcon: Icon(Icons.lock_outline_rounded),
                                  ),
                                ),
                                const SizedBox(height: 10),
                                SwitchListTile.adaptive(
                                  value: _rememberSession,
                                  contentPadding: EdgeInsets.zero,
                                  title: const Text('Manter acesso neste dispositivo'),
                                  subtitle: const Text('Se ativado, você continua conectado mesmo após fechar o aplicativo.'),
                                  onChanged: _loading
                                      ? null
                                      : (value) {
                                          setState(() {
                                            _rememberSession = value;
                                          });
                                        },
                                ),
                                if (_error != null) ...[
                                  const SizedBox(height: 8),
                                  Text(_error!, style: TextStyle(color: Theme.of(context).colorScheme.error, fontWeight: FontWeight.w600)),
                                ],
                                if (widget.bootstrapWarning != null) ...[
                                  const SizedBox(height: 8),
                                  Container(
                                    width: double.infinity,
                                    padding: const EdgeInsets.all(14),
                                    decoration: BoxDecoration(
                                      borderRadius: BorderRadius.circular(18),
                                      color: const Color(0xFFFFF4E5),
                                      border: Border.all(color: const Color(0xFFF6BD60)),
                                    ),
                                    child: Text(
                                      widget.bootstrapWarning!,
                                      style: Theme.of(context).textTheme.bodySmall?.copyWith(
                                        color: const Color(0xFF8A4B08),
                                        fontWeight: FontWeight.w600,
                                        height: 1.4,
                                      ),
                                    ),
                                  ),
                                ],
                                const SizedBox(height: 14),
                                FilledButton(
                                  onPressed: _loading ? null : _submit,
                                  child: _loading
                                      ? const SizedBox(
                                          width: 22,
                                          height: 22,
                                          child: CircularProgressIndicator(strokeWidth: 2.2, color: Colors.white),
                                        )
                                      : const Text('Entrar'),
                                ),
                                const SizedBox(height: 12),
                                if ((_copy('signup_url', '').isNotEmpty))
                                  OutlinedButton(
                                    onPressed: _loading ? null : _openSignup,
                                    child: Text(_copy('signup_label', 'Fazer cadastro básico')),
                                  ),
                                const SizedBox(height: 14),
                                Container(
                                  width: double.infinity,
                                  padding: const EdgeInsets.all(14),
                                  decoration: BoxDecoration(
                                    borderRadius: BorderRadius.circular(18),
                                    color: AppTheme.bgSoft,
                                  ),
                                  child: Text(
                                    widget.bootstrapWarning == null
                                        ? _copy('support_note', 'Seus dados ficam protegidos para uma experiência terapêutica mais tranquila.')
                                        : 'Se o problema persistir, tente novamente mais tarde ou contate o suporte da clínica.',
                                    style: Theme.of(context).textTheme.bodyMedium,
                                  ),
                                ),
                                if (AppConfig.hasDevLogin) ...[
                                  const SizedBox(height: 14),
                                  Container(
                                    width: double.infinity,
                                    padding: const EdgeInsets.all(14),
                                    decoration: BoxDecoration(
                                      borderRadius: BorderRadius.circular(18),
                                      border: Border.all(color: AppTheme.lineStrong),
                                    ),
                                    child: Text('Acesso de teste carregado para desenvolvimento: ${AppConfig.devLoginEmail}', style: Theme.of(context).textTheme.bodySmall?.copyWith(color: AppTheme.textSoft)),
                                  ),
                                ],
                              ],
                            ),
                          ),
                        ),
                      ),
                      const SizedBox(height: 18),
                      ...systemInfo.map(
                        (item) => Padding(
                          padding: const EdgeInsets.only(bottom: 10),
                          child: Container(
                            width: double.infinity,
                            padding: const EdgeInsets.all(16),
                            decoration: BoxDecoration(
                              borderRadius: BorderRadius.circular(20),
                              gradient: const LinearGradient(colors: [AppTheme.cta, AppTheme.affection]),
                              boxShadow: const [BoxShadow(color: Color(0x24F9844A), blurRadius: 18, offset: Offset(0, 10))],
                            ),
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text(item['title'] ?? '', style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w800)),
                                const SizedBox(height: 4),
                                Text(item['description'] ?? '', style: TextStyle(color: Colors.white.withValues(alpha: 0.92), height: 1.45)),
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
      ),
    );
  }
}