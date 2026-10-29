import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:provider/provider.dart';
import 'package:sibuma_mobile/core/theme/app_theme.dart';
import 'package:sibuma_mobile/features/auth/providers/auth_provider.dart';
import 'package:sibuma_mobile/features/home/providers/home_provider.dart';
import 'package:sibuma_mobile/features/pelaporan/providers/pelaporan_provider.dart';
import 'package:sibuma_mobile/features/pelaporan/widgets/month_picker_field.dart';

class InputLaporanScreen extends StatefulWidget {
  const InputLaporanScreen({super.key});
  @override
  State<InputLaporanScreen> createState() => _InputLaporanScreenState();
}

class _InputLaporanScreenState extends State<InputLaporanScreen> {
  final _formKey = GlobalKey<FormState>();
  final _jumlahCtrl = TextEditingController();
  final _catatanCtrl = TextEditingController();

  static const _namaBulan = [
    'Januari',
    'Februari',
    'Maret',
    'April',
    'Mei',
    'Juni',
    'Juli',
    'Agustus',
    'September',
    'Oktober',
    'November',
    'Desember',
  ];

  late String _bulanValue;
  late String _bulanLabel;

  @override
  void initState() {
    super.initState();
    final now = DateTime.now();
    _bulanValue = '${now.year}-${now.month.toString().padLeft(2, '0')}';
    _bulanLabel = '${_namaBulan[now.month - 1]} ${now.year}';
  }

  @override
  void dispose() {
    _jumlahCtrl.dispose();
    _catatanCtrl.dispose();
    super.dispose();
  }

  String get _jumlahMurni => _jumlahCtrl.text.replaceAll(RegExp(r'[^0-9]'), '');

  void _onBulanChanged(String val) {
    final p = val.split('-');
    final idx = p.length == 2 ? (int.tryParse(p[1]) ?? 1) : 1;
    setState(() {
      _bulanValue = val;
      _bulanLabel = '${_namaBulan[idx - 1]} ${p[0]}';
    });
  }

  void _submit() async {
    if (!_formKey.currentState!.validate()) return;
    FocusScope.of(context).unfocus();
    final prov = Provider.of<PelaporanProvider>(context, listen: false);
    final success = await prov.kirimLaporan(
      jumlahRupiah: _jumlahMurni,
      bulanTahun: _bulanValue,
      catatan: _catatanCtrl.text.trim(),
    );
    if (!mounted) return;
    if (success) {
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(
          content: Text('Laporan Berhasil Terkirim ke BPS!'),
          backgroundColor: AppTheme.success));
      Navigator.pop(context);
      WidgetsBinding.instance.addPostFrameCallback((_) {
        Provider.of<HomeProvider>(context, listen: false).fetchDashboardData();
      });
    } else {
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(
          content: Text(prov.errorMessage), backgroundColor: AppTheme.danger));
    }
  }

  @override
  Widget build(BuildContext context) {
    final user = Provider.of<AuthProvider>(context, listen: false).currentUser;
    final jenisPendapatan = user?.jenisPendapatanFormatted ?? 'Pendapatan';

    return Scaffold(
      appBar: AppBar(title: const Text('Input Laporan Bulanan')),
      body: GestureDetector(
        onTap: () => FocusScope.of(context).unfocus(),
        child: SingleChildScrollView(
          padding: const EdgeInsets.all(24),
          child: Form(
            key: _formKey,
            child: Column(
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  Container(
                    padding: const EdgeInsets.all(16),
                    decoration: BoxDecoration(
                      color: AppTheme.primary.withValues(alpha: 0.08),
                      borderRadius: BorderRadius.circular(12),
                      border: Border.all(
                          color: AppTheme.primary.withValues(alpha: 0.2)),
                    ),
                    child: Row(children: [
                      const Icon(Icons.info_outline_rounded,
                          color: AppTheme.primary, size: 28),
                      const SizedBox(width: 16),
                      Expanded(
                          child: Text(
                        'Anda akan melaporkan data $jenisPendapatan untuk bulan yang dipilih.',
                        style: GoogleFonts.dmSans(
                            color: AppTheme.textPrimary,
                            fontWeight: FontWeight.w500),
                      )),
                    ]),
                  ),
                  const SizedBox(height: 32),
                  Text('Periode Laporan', style: AppTheme.label),
                  const SizedBox(height: 8),
                  MonthPickerField(
                    value: _bulanValue,
                    label: _bulanLabel,
                    onChanged: _onBulanChanged,
                  ),
                  const SizedBox(height: 24),
                  Text('Jumlah $jenisPendapatan', style: AppTheme.label),
                  const SizedBox(height: 8),
                  TextFormField(
                    controller: _jumlahCtrl,
                    keyboardType: TextInputType.number,
                    inputFormatters: [RupiahInputFormatter()],
                    decoration: const InputDecoration(
                        prefixIcon: Icon(Icons.monetization_on_outlined),
                        hintText: 'Rp 0'),
                    onChanged: (_) => setState(() {}),
                    validator: (_) {
                      if (_jumlahMurni.isEmpty)
                        return 'Jumlah tidak boleh kosong';
                      if ((int.tryParse(_jumlahMurni) ?? 0) == 0)
                        return 'Jumlah tidak boleh nol';
                      return null;
                    },
                  ),
                  if (_jumlahMurni.isNotEmpty)
                    Padding(
                      padding: const EdgeInsets.only(top: 6, left: 4),
                      child: Row(children: [
                        const Icon(Icons.check_circle_outline,
                            size: 14, color: AppTheme.success),
                        const SizedBox(width: 4),
                        Text('Nilai terkirim: $_jumlahMurni',
                            style: GoogleFonts.dmSans(
                                fontSize: 12, color: AppTheme.success)),
                      ]),
                    ),
                  const SizedBox(height: 24),
                  Text('Catatan Laporan (Opsional)', style: AppTheme.label),
                  const SizedBox(height: 8),
                  TextFormField(
                    controller: _catatanCtrl,
                    maxLines: 4,
                    decoration: const InputDecoration(
                        hintText:
                            'Beri alasan jika ada anomali data (naik/turun drastis)...'),
                  ),
                  const SizedBox(height: 48),
                  Consumer<PelaporanProvider>(
                    builder: (_, prov, __) => ElevatedButton(
                      onPressed: prov.isLoading ? null : _submit,
                      child: prov.isLoading
                          ? const CircularProgressIndicator(
                              color: Colors.white, strokeWidth: 2)
                          : Text('KIRIM LAPORAN',
                              style: AppTheme.button
                                  .copyWith(color: Colors.white)),
                    ),
                  ),
                ]),
          ),
        ),
      ),
    );
  }
}
