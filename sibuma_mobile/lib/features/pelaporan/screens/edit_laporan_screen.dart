import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:provider/provider.dart';
import 'package:sibuma_mobile/core/theme/app_theme.dart';
import 'package:sibuma_mobile/features/pelaporan/models/laporan_model.dart';
import 'package:sibuma_mobile/features/pelaporan/providers/pelaporan_provider.dart';
import 'package:sibuma_mobile/features/riwayat/providers/riwayat_provider.dart';
import 'package:sibuma_mobile/features/pelaporan/widgets/month_picker_field.dart';

class EditLaporanScreen extends StatefulWidget {
  final LaporanModel laporan;
  const EditLaporanScreen({super.key, required this.laporan});
  @override
  State<EditLaporanScreen> createState() => _EditLaporanScreenState();
}

class _EditLaporanScreenState extends State<EditLaporanScreen> {
  final _formKey       = GlobalKey<FormState>();
  late TextEditingController _jumlahCtrl;
  late TextEditingController _catatanCtrl;
  late String _bulanValue;
  late String _bulanLabel;

  static const _namaBulan = [
    'Januari','Februari','Maret','April','Mei','Juni',
    'Juli','Agustus','September','Oktober','November','Desember',
  ];

  @override
  void initState() {
    super.initState();
    final jumlahInt = widget.laporan.jumlah.toInt();
    final jumlahFmt = jumlahInt.toString()
        .replaceAllMapped(RegExp(r'(\d{1,3})(?=(\d{3})+(?!\d))'), (m) => '${m[1]}.');
    _jumlahCtrl  = TextEditingController(text: 'Rp $jumlahFmt');
    _catatanCtrl = TextEditingController(text: widget.laporan.catatanLaporan ?? '');
    _bulanValue  = widget.laporan.bulanTahun;
    _bulanLabel  = widget.laporan.bulanFormatted;
  }

  @override
  void dispose() {
    _jumlahCtrl.dispose();
    _catatanCtrl.dispose();
    super.dispose();
  }

  String get _jumlahMurni => _jumlahCtrl.text.replaceAll(RegExp(r'[^0-9]'), '');

  void _onBulanChanged(String val) {
    final p   = val.split('-');
    final idx = p.length == 2 ? (int.tryParse(p[1]) ?? 1) : 1;
    setState(() {
      _bulanValue = val;
      _bulanLabel = '${_namaBulan[idx - 1]} ${p[0]}';
    });
  }

  void _simpan() async {
    if (!_formKey.currentState!.validate()) return;
    FocusScope.of(context).unfocus();

    final pelProv = Provider.of<PelaporanProvider>(context, listen: false);
    final rwProv  = Provider.of<RiwayatProvider>(context, listen: false);

    final success = await pelProv.editLaporan(
      laporanId:    widget.laporan.id,
      jumlahRupiah: _jumlahMurni,
      bulanTahun:   _bulanValue,
      catatan:      _catatanCtrl.text.trim(),
    );
    if (!mounted) return;
    if (success) {
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(
          content: Text('Laporan berhasil diperbarui!'),
          backgroundColor: AppTheme.success));
      Navigator.pop(context);
      WidgetsBinding.instance.addPostFrameCallback((_) {
        rwProv.fetchRiwayat();
      });
    } else {
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(
          content: Text(pelProv.errorMessage), backgroundColor: AppTheme.danger));
    }
  }

  Color _statusColor(String s) {
    if (s == 'diverifikasi') return AppTheme.success;
    if (s == 'terkirim')     return AppTheme.accent;
    return AppTheme.textSecondary;
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Edit Laporan'),
        actions: [
          Padding(
            padding: const EdgeInsets.only(right: 16),
            child: Chip(
              label: Text(
                widget.laporan.status[0].toUpperCase() + widget.laporan.status.substring(1),
                style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w600),
              ),
              backgroundColor: _statusColor(widget.laporan.status).withValues(alpha: 0.15),
              side:            BorderSide(color: _statusColor(widget.laporan.status)),
              labelStyle:      TextStyle(color: _statusColor(widget.laporan.status)),
              padding:         EdgeInsets.zero,
            ),
          ),
        ],
      ),
      body: GestureDetector(
        onTap: () => FocusScope.of(context).unfocus(),
        child: SingleChildScrollView(
          padding: const EdgeInsets.all(24),
          child: Form(
            key: _formKey,
            child: Column(crossAxisAlignment: CrossAxisAlignment.stretch, children: [
              // Banner info
              Container(
                padding: const EdgeInsets.all(16),
                decoration: BoxDecoration(
                  color:        AppTheme.accent.withValues(alpha: 0.08),
                  borderRadius: BorderRadius.circular(12),
                  border:       Border.all(color: AppTheme.accent.withValues(alpha: 0.25)),
                ),
                child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                  Row(children: [
                    const Icon(Icons.edit_note_rounded, color: AppTheme.accent, size: 22),
                    const SizedBox(width: 8),
                    Text('Mengedit Laporan', style: GoogleFonts.plusJakartaSans(
                        color: AppTheme.accent, fontWeight: FontWeight.w700, fontSize: 14)),
                  ]),
                  const SizedBox(height: 8),
                  Text('Periode: ${widget.laporan.bulanFormatted}',
                      style: const TextStyle(color: AppTheme.textPrimary, fontSize: 13)),
                  Text('Dikirim: ${widget.laporan.tanggalKirimFormatted}',
                      style: const TextStyle(color: AppTheme.textSecondary, fontSize: 12)),
                ]),
              ),
              const SizedBox(height: 28),
              Text('Periode Laporan', style: AppTheme.label),
              const SizedBox(height: 8),
              MonthPickerField(
                value:     _bulanValue,
                label:     _bulanLabel,
                onChanged: _onBulanChanged,
              ),
              const SizedBox(height: 24),
              Text('Jumlah Pendapatan', style: AppTheme.label),
              const SizedBox(height: 8),
              TextFormField(
                controller:      _jumlahCtrl,
                keyboardType:    TextInputType.number,
                inputFormatters: [RupiahInputFormatter()],
                decoration:      const InputDecoration(
                    prefixIcon: Icon(Icons.monetization_on_outlined), hintText: 'Rp 0'),
                onChanged: (_) => setState(() {}),
                validator: (_) {
                  if (_jumlahMurni.isEmpty) return 'Jumlah tidak boleh kosong';
                  if ((int.tryParse(_jumlahMurni) ?? 0) == 0) return 'Jumlah tidak boleh nol';
                  return null;
                },
              ),
              if (_jumlahMurni.isNotEmpty)
                Padding(
                  padding: const EdgeInsets.only(top: 6, left: 4),
                  child: Row(children: [
                    const Icon(Icons.check_circle_outline, size: 14, color: AppTheme.success),
                    const SizedBox(width: 4),
                    Text('Nilai terkirim: $_jumlahMurni',
                        style: const TextStyle(fontSize: 12, color: AppTheme.success)),
                  ]),
                ),
              const SizedBox(height: 24),
              Text('Catatan (Opsional)', style: AppTheme.label),
              const SizedBox(height: 8),
              TextFormField(
                controller: _catatanCtrl,
                maxLines:   4,
                decoration: const InputDecoration(
                    hintText: 'Beri alasan jika ada perubahan data...'),
              ),
              const SizedBox(height: 48),
              Consumer<PelaporanProvider>(
                builder: (_, prov, __) => ElevatedButton.icon(
                  onPressed: prov.isLoading ? null : _simpan,
                  icon: prov.isLoading
                      ? const SizedBox(width: 18, height: 18,
                          child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2))
                      : const Icon(Icons.save_rounded),
                  label: Text(prov.isLoading ? 'Menyimpan...' : 'SIMPAN PERUBAHAN'),
                ),
              ),
              const SizedBox(height: 12),
              OutlinedButton.icon(
                onPressed: () => Navigator.pop(context),
                icon:  const Icon(Icons.close_rounded),
                label: const Text('BATAL'),
                style: OutlinedButton.styleFrom(foregroundColor: AppTheme.textSecondary),
              ),
            ]),
          ),
        ),
      ),
    );
  }
}