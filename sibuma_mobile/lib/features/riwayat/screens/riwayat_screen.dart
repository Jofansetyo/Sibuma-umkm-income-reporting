import 'package:fl_chart/fl_chart.dart';
import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:provider/provider.dart';

import 'package:sibuma_mobile/core/theme/app_theme.dart';
import 'package:sibuma_mobile/core/utils/formatters.dart';
import 'package:sibuma_mobile/features/home/providers/home_provider.dart';
import 'package:sibuma_mobile/features/pelaporan/models/laporan_model.dart';
import 'package:sibuma_mobile/features/pelaporan/providers/pelaporan_provider.dart';
import 'package:sibuma_mobile/features/pelaporan/screens/edit_laporan_screen.dart';
import 'package:sibuma_mobile/features/riwayat/providers/riwayat_provider.dart';

class RiwayatScreen extends StatefulWidget {
  const RiwayatScreen({super.key});
  @override
  State<RiwayatScreen> createState() => _RiwayatScreenState();
}

class _RiwayatScreenState extends State<RiwayatScreen> {
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      Provider.of<RiwayatProvider>(context, listen: false).fetchRiwayat();
    });
  }

  // ─── Konfirmasi Delete ─────────────────────────────────
  Future<void> _confirmDelete(LaporanModel laporan) async {
    final confirm = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        title: Row(children: [
          Container(
              padding: const EdgeInsets.all(8),
              decoration: BoxDecoration(
                  color: AppTheme.danger.withValues(alpha: 0.1),
                  shape: BoxShape.circle),
              child: const Icon(Icons.delete_outline_rounded,
                  color: AppTheme.danger, size: 22)),
          const SizedBox(width: 12),
          const Text('Hapus Laporan', style: TextStyle(fontSize: 17)),
        ]),
        content: Text(
          'Apakah Anda yakin ingin menghapus laporan bulan ${laporan.bulanFormatted}?\n\nTindakan ini tidak dapat dibatalkan.',
          style: GoogleFonts.dmSans(
              fontSize: 14, color: AppTheme.textSecondary, height: 1.5),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx, false),
            child: Text('Batal',
                style: GoogleFonts.plusJakartaSans(
                    color: AppTheme.textSecondary,
                    fontWeight: FontWeight.w600)),
          ),
          ElevatedButton(
            onPressed: () => Navigator.pop(ctx, true),
            style: ElevatedButton.styleFrom(backgroundColor: AppTheme.danger),
            child: Text('Hapus',
                style: GoogleFonts.plusJakartaSans(
                    fontWeight: FontWeight.w700, color: Colors.white)),
          ),
        ],
      ),
    );

    if (confirm != true || !mounted) return;

    final pelaporanProv =
        Provider.of<PelaporanProvider>(context, listen: false);
    final riwayatProv = Provider.of<RiwayatProvider>(context, listen: false);

    final success = await pelaporanProv.deleteLaporan(laporan.id);
    if (!mounted) return;

    if (success) {
      riwayatProv.fetchRiwayat();
      Provider.of<HomeProvider>(context, listen: false).fetchDashboardData();
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(
          content: Text('Laporan berhasil dihapus.'),
          backgroundColor: AppTheme.success));
    } else {
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(
          content: Text(pelaporanProv.errorMessage),
          backgroundColor: AppTheme.danger));
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Riwayat & Analitik')),
      body: Consumer<RiwayatProvider>(
        builder: (context, provider, _) {
          if (provider.isLoading) {
            return const Center(
                child: CircularProgressIndicator(color: AppTheme.primary));
          }

          if (provider.errorMessage.isNotEmpty &&
              provider.listRiwayat.isEmpty) {
            return Center(
              child: Padding(
                padding: const EdgeInsets.all(24),
                child: Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      const Icon(Icons.cloud_off_rounded,
                          size: 60, color: AppTheme.textSecondary),
                      const SizedBox(height: 16),
                      Text(provider.errorMessage,
                          textAlign: TextAlign.center,
                          style: GoogleFonts.dmSans(
                              color: AppTheme.danger, fontSize: 16)),
                      const SizedBox(height: 16),
                      ElevatedButton.icon(
                        onPressed: () => provider.fetchRiwayat(),
                        icon: const Icon(Icons.refresh),
                        label: const Text('Coba Lagi'),
                      ),
                    ]),
              ),
            );
          }

          if (provider.listRiwayat.isEmpty) {
            return Center(
              child: Opacity(
                  opacity: 0.7,
                  child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        const Icon(Icons.inbox_outlined,
                            size: 80, color: AppTheme.textSecondary),
                        const SizedBox(height: 16),
                        Text('Belum ada riwayat laporan.',
                            style: GoogleFonts.dmSans(
                                fontSize: 16, color: AppTheme.textSecondary)),
                      ])),
            );
          }

          final sorted = List<LaporanModel>.from(provider.listRiwayat)
            ..sort((a, b) => b.bulanTahun.compareTo(a.bulanTahun));

          return RefreshIndicator(
            onRefresh: () => provider.fetchRiwayat(),
            color: AppTheme.primary,
            child: CustomScrollView(
              slivers: [
                // Kartu ringkasan
                _buildSummaryCards(provider.listRiwayat),
                // Grafik
                _buildChartCard(provider.listRiwayat),
                // Header list
                SliverToBoxAdapter(
                  child: Padding(
                    padding: const EdgeInsets.fromLTRB(16, 24, 16, 8),
                    child: Row(children: [
                      Text('Detail Laporan', style: AppTheme.heading3),
                      const SizedBox(width: 8),
                      Container(
                        padding: const EdgeInsets.symmetric(
                            horizontal: 8, vertical: 3),
                        decoration: BoxDecoration(
                            color: AppTheme.primary.withValues(alpha: 0.1),
                            borderRadius: BorderRadius.circular(20)),
                        child: Text('${sorted.length} data',
                            style: GoogleFonts.dmSans(
                                fontSize: 12,
                                fontWeight: FontWeight.w600,
                                color: AppTheme.primary)),
                      ),
                    ]),
                  ),
                ),
                // List kartu laporan
                SliverPadding(
                  padding: const EdgeInsets.symmetric(horizontal: 16),
                  sliver: SliverList(
                    delegate: SliverChildBuilderDelegate(
                      (context2, i) => _buildLaporanCard(sorted[i]),
                      childCount: sorted.length,
                    ),
                  ),
                ),
                const SliverPadding(padding: EdgeInsets.only(bottom: 32)),
              ],
            ),
          );
        },
      ),
    );
  }

  // ─── Kartu Laporan (menggantikan DataTable yang tidak responsive) ──
  Widget _buildLaporanCard(LaporanModel laporan) {
    Color statusColor;
    IconData statusIcon;
    switch (laporan.status) {
      case 'diverifikasi':
        statusColor = AppTheme.success;
        statusIcon = Icons.verified_rounded;
        break;
      case 'terkirim':
        statusColor = AppTheme.accent;
        statusIcon = Icons.send_rounded;
        break;
      default:
        statusColor = AppTheme.textSecondary;
        statusIcon = Icons.drafts_outlined;
    }
    final jenis = laporan.jenisInput == 'omzet'
        ? 'Omzet'
        : laporan.jenisInput == 'nilai_jasa'
            ? 'Nilai Jasa'
            : 'Nilai Produk';

    return Card(
      margin: const EdgeInsets.only(bottom: 10),
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
          // Baris 1: Bulan + Status
          Row(children: [
            Expanded(
              child: Text(laporan.bulanFormatted,
                  style: GoogleFonts.plusJakartaSans(
                      fontWeight: FontWeight.w700,
                      fontSize: 15,
                      color: AppTheme.textPrimary)),
            ),
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
              decoration: BoxDecoration(
                  color: statusColor.withValues(alpha: 0.1),
                  borderRadius: BorderRadius.circular(20)),
              child: Row(mainAxisSize: MainAxisSize.min, children: [
                Icon(statusIcon, size: 13, color: statusColor),
                const SizedBox(width: 4),
                Text(
                    laporan.status[0].toUpperCase() +
                        laporan.status.substring(1),
                    style: GoogleFonts.dmSans(
                        fontSize: 12,
                        fontWeight: FontWeight.w600,
                        color: statusColor)),
              ]),
            ),
          ]),
          const SizedBox(height: 10),
          // Baris 2: Jumlah
          Text(laporan.jumlahFormatted,
              style: GoogleFonts.plusJakartaSans(
                  fontSize: 20,
                  fontWeight: FontWeight.w800,
                  color: AppTheme.accent)),
          const SizedBox(height: 6),
          // Baris 3: Jenis + Tanggal kirim
          Row(children: [
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
              decoration: BoxDecoration(
                  color: Colors.grey.shade100,
                  borderRadius: BorderRadius.circular(6)),
              child: Text(jenis,
                  style: GoogleFonts.dmSans(
                      fontSize: 11, color: AppTheme.textSecondary)),
            ),
            const SizedBox(width: 8),
            Icon(Icons.access_time_rounded,
                size: 12, color: Colors.grey.shade400),
            const SizedBox(width: 4),
            Text(laporan.tanggalKirimFormatted,
                style: GoogleFonts.dmSans(
                    fontSize: 11, color: AppTheme.textSecondary)),
          ]),
          // Catatan (jika ada)
          if (laporan.catatanLaporan != null &&
              laporan.catatanLaporan!.isNotEmpty) ...[
            const SizedBox(height: 8),
            Container(
              padding: const EdgeInsets.all(10),
              decoration: BoxDecoration(
                  color: Colors.amber.shade50,
                  borderRadius: BorderRadius.circular(8),
                  border: Border.all(color: Colors.amber.shade200)),
              child:
                  Row(crossAxisAlignment: CrossAxisAlignment.start, children: [
                Icon(Icons.sticky_note_2_outlined,
                    size: 14, color: Colors.amber.shade700),
                const SizedBox(width: 6),
                Expanded(
                    child: Text(laporan.catatanLaporan!,
                        style: GoogleFonts.dmSans(
                            fontSize: 12, color: Colors.amber.shade900))),
              ]),
            ),
          ],
          const Divider(height: 20),
          // Tombol aksi
          Row(children: [
            Expanded(
              child: OutlinedButton.icon(
                onPressed: () async {
                  await Navigator.push(
                      context,
                      MaterialPageRoute(
                          builder: (ctx) =>
                              EditLaporanScreen(laporan: laporan)));
                  // Refresh setelah kembali dari edit
                  if (context.mounted) {
                    Provider.of<RiwayatProvider>(context, listen: false)
                        .fetchRiwayat();
                  }
                },
                icon: const Icon(Icons.edit_rounded, size: 16),
                label: const Text('Edit'),
                style: OutlinedButton.styleFrom(
                  foregroundColor: AppTheme.accent,
                  side: const BorderSide(color: AppTheme.accent),
                  padding: const EdgeInsets.symmetric(vertical: 10),
                ),
              ),
            ),
            const SizedBox(width: 10),
            Expanded(
              child: OutlinedButton.icon(
                onPressed: () => _confirmDelete(laporan),
                icon: const Icon(Icons.delete_outline_rounded, size: 16),
                label: const Text('Hapus'),
                style: OutlinedButton.styleFrom(
                  foregroundColor: AppTheme.danger,
                  side: const BorderSide(color: AppTheme.danger),
                  padding: const EdgeInsets.symmetric(vertical: 10),
                ),
              ),
            ),
          ]),
        ]),
      ),
    );
  }

  // ─── Kartu ringkasan ───────────────────────────────────
  SliverToBoxAdapter _buildSummaryCards(List<LaporanModel> riwayat) {
    final total = riwayat.fold(0.0, (s, i) => s + i.jumlah);
    final rata = riwayat.isEmpty ? 0.0 : total / riwayat.length;
    final max = riwayat.isEmpty
        ? 0.0
        : riwayat.map((e) => e.jumlah).reduce((a, b) => a > b ? a : b);

    return SliverToBoxAdapter(
      child: Padding(
        padding: const EdgeInsets.fromLTRB(16, 16, 16, 0),
        child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
          Row(children: [
            Expanded(
                child: _buildInfoCard(
                    'Total Tercatat',
                    AppFormatters.formatRupiah(total),
                    Icons.stacked_bar_chart,
                    AppTheme.accent)),
            const SizedBox(width: 12),
            Expanded(
                child: _buildInfoCard(
                    'Rata-rata/Bulan',
                    AppFormatters.formatRupiah(rata),
                    Icons.functions_rounded,
                    AppTheme.success)),
          ]),
          const SizedBox(height: 12),
          Row(children: [
            Expanded(
                child: _buildInfoCard(
                    'Tertinggi',
                    AppFormatters.formatRupiah(max),
                    Icons.arrow_upward_rounded,
                    Colors.green.shade600)),
            const SizedBox(width: 12),
            Expanded(
                child: _buildInfoCard(
                    'Jumlah Laporan',
                    '${riwayat.length} bulan',
                    Icons.calendar_month_rounded,
                    AppTheme.primary)),
          ]),
        ]),
      ),
    );
  }

  Widget _buildInfoCard(
      String title, String value, IconData icon, Color color) {
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(14),
        child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
          Row(mainAxisAlignment: MainAxisAlignment.spaceBetween, children: [
            Text(title,
                style: GoogleFonts.dmSans(
                    fontSize: 12, color: AppTheme.textSecondary)),
            Icon(icon, color: color, size: 18),
          ]),
          const SizedBox(height: 6),
          Text(value,
              style: GoogleFonts.plusJakartaSans(
                  fontSize: 15,
                  fontWeight: FontWeight.w700,
                  color: AppTheme.textPrimary),
              maxLines: 1,
              overflow: TextOverflow.ellipsis),
        ]),
      ),
    );
  }

  // ─── Grafik ────────────────────────────────────────────
  SliverToBoxAdapter _buildChartCard(List<LaporanModel> riwayat) {
    List<LaporanModel> data = List.from(riwayat)
      ..sort((a, b) => a.bulanTahun.compareTo(b.bulanTahun));
    if (data.length > 6) data = data.sublist(data.length - 6);

    List<FlSpot> spots = [];
    double maxJuta = 0;
    for (int i = 0; i < data.length; i++) {
      final juta = data[i].jumlah / 1000000;
      spots.add(FlSpot(i.toDouble(), juta));
      if (juta > maxJuta) maxJuta = juta;
    }
    final maxY = maxJuta == 0 ? 10.0 : maxJuta * 1.25;

    return SliverToBoxAdapter(
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Card(
          child: Padding(
            padding: const EdgeInsets.fromLTRB(16, 20, 20, 16),
            child:
                Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
              Text('Tren 6 Bulan Terakhir', style: AppTheme.heading3),
              const SizedBox(height: 20),
              SizedBox(height: 200, child: _buildLineChart(data, spots, maxY)),
            ]),
          ),
        ),
      ),
    );
  }

  Widget _buildLineChart(
      List<LaporanModel> data, List<FlSpot> spots, double maxY) {
    const bulanSingkat = [
      'Jan',
      'Feb',
      'Mar',
      'Apr',
      'Mei',
      'Jun',
      'Jul',
      'Agt',
      'Sep',
      'Okt',
      'Nov',
      'Des'
    ];
    return LineChart(
      LineChartData(
        minY: 0,
        maxY: maxY,
        gridData: const FlGridData(show: false),
        borderData: FlBorderData(
          show: true,
          border: const Border(
            bottom: BorderSide(color: AppTheme.border),
            left: BorderSide(color: AppTheme.border),
          ),
        ),
        lineTouchData: LineTouchData(
          touchTooltipData: LineTouchTooltipData(
            getTooltipItems: (ts) => ts.map((s) {
              final i = s.x.toInt();
              final label = i < data.length ? data[i].bulanFormatted : '';
              final val = AppFormatters.formatRupiah(s.y * 1000000);
              return LineTooltipItem(
                  '$label\n$val',
                  const TextStyle(
                      color: Colors.white,
                      fontSize: 11,
                      fontWeight: FontWeight.w600));
            }).toList(),
          ),
        ),
        titlesData: FlTitlesData(
          topTitles:
              const AxisTitles(sideTitles: SideTitles(showTitles: false)),
          rightTitles:
              const AxisTitles(sideTitles: SideTitles(showTitles: false)),
          leftTitles: AxisTitles(
            sideTitles: SideTitles(
              showTitles: true,
              reservedSize: 40,
              getTitlesWidget: (v, m) {
                if (v == 0 || v == m.max) return const SizedBox.shrink();
                return Text('${v.toInt()} Jt',
                    style: GoogleFonts.dmSans(
                        color: AppTheme.textSecondary, fontSize: 10));
              },
            ),
          ),
          bottomTitles: AxisTitles(
            sideTitles: SideTitles(
              showTitles: true,
              getTitlesWidget: (v, _) {
                final i = v.toInt();
                if (i >= data.length) return const SizedBox.shrink();
                try {
                  final bi = int.parse(data[i].bulanTahun.split('-')[1]) - 1;
                  return Padding(
                    padding: const EdgeInsets.only(top: 8),
                    child: Text(bulanSingkat[bi],
                        style: GoogleFonts.dmSans(
                            color: AppTheme.textSecondary, fontSize: 11)),
                  );
                } catch (_) {
                  return const SizedBox.shrink();
                }
              },
            ),
          ),
        ),
        lineBarsData: [
          LineChartBarData(
            spots: spots,
            isCurved: true,
            color: AppTheme.accent,
            barWidth: 3,
            isStrokeCapRound: true,
            dotData: FlDotData(
                getDotPainter: (_, __, ___, ____) => FlDotCirclePainter(
                    radius: 4,
                    color: AppTheme.accent,
                    strokeWidth: 2,
                    strokeColor: Colors.white)),
            belowBarData: BarAreaData(
                show: true, color: AppTheme.accent.withValues(alpha: 0.1)),
          ),
        ],
      ),
    );
  }
}
