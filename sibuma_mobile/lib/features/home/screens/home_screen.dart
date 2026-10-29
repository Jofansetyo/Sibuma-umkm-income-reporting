import 'package:fl_chart/fl_chart.dart';
import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:intl/intl.dart';
import 'package:provider/provider.dart';
import 'package:url_launcher/url_launcher.dart';

import 'package:sibuma_mobile/core/theme/app_theme.dart';
import 'package:sibuma_mobile/features/auth/providers/auth_provider.dart';
import 'package:sibuma_mobile/features/auth/screens/profil_screen.dart';
import 'package:sibuma_mobile/features/home/providers/home_provider.dart';
import 'package:sibuma_mobile/features/pelaporan/screens/input_laporan_screen.dart';
import 'package:sibuma_mobile/features/riwayat/screens/riwayat_screen.dart';

// ─────────────────────────────────────────────────────────
// Helper format rupiah
// ─────────────────────────────────────────────────────────
String _fmtRupiah(double val) {
  if (val >= 1000000000) return 'Rp ${(val / 1000000000).toStringAsFixed(1)} M';
  if (val >= 1000000) return 'Rp ${(val / 1000000).toStringAsFixed(1)} Jt';
  return NumberFormat.currency(locale: 'id_ID', symbol: 'Rp ', decimalDigits: 0)
      .format(val);
}

// ─────────────────────────────────────────────────────────
// HomeScreen — Bottom Nav wrapper
// ─────────────────────────────────────────────────────────
class HomeScreen extends StatefulWidget {
  const HomeScreen({super.key});
  @override
  State<HomeScreen> createState() => _HomeScreenState();
}

class _HomeScreenState extends State<HomeScreen> {
  int _currentIndex = 0;

  final List<Widget> _pages = [
    const _HomeDashboardTab(),
    const RiwayatScreen(),
    const ProfilScreen(),
  ];

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: _pages[_currentIndex],
      bottomNavigationBar: BottomNavigationBar(
        currentIndex: _currentIndex,
        onTap: (i) => setState(() => _currentIndex = i),
        type: BottomNavigationBarType.fixed,
        items: const [
          BottomNavigationBarItem(
              icon: Icon(Icons.dashboard_outlined),
              activeIcon: Icon(Icons.dashboard),
              label: 'Beranda'),
          BottomNavigationBarItem(
              icon: Icon(Icons.bar_chart_outlined),
              activeIcon: Icon(Icons.bar_chart),
              label: 'Riwayat'),
          BottomNavigationBarItem(
              icon: Icon(Icons.person_outline),
              activeIcon: Icon(Icons.person),
              label: 'Profil'),
        ],
      ),
    );
  }
}

// ─────────────────────────────────────────────────────────
// Dashboard Tab
// ─────────────────────────────────────────────────────────
class _HomeDashboardTab extends StatefulWidget {
  const _HomeDashboardTab();
  @override
  State<_HomeDashboardTab> createState() => __HomeDashboardTabState();
}

class __HomeDashboardTabState extends State<_HomeDashboardTab> {
  int _grafikFilter = 6; // default tampilkan 6 bulan terakhir; -1 = semua data
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      Provider.of<HomeProvider>(context, listen: false).fetchDashboardData();
    });
  }

  @override
  Widget build(BuildContext context) {
    final user = Provider.of<AuthProvider>(context).currentUser;

    return Scaffold(
      appBar: AppBar(
        backgroundColor: AppTheme.primary,
        elevation: 0,
        centerTitle: true,
        shape: const RoundedRectangleBorder(
            borderRadius: BorderRadius.vertical(bottom: Radius.circular(24))),
        toolbarHeight: 70,
        leading: Padding(
          padding: const EdgeInsets.only(left: 12),
          child: Image.asset('assets/images/BPS.png',
              height: 36,
              errorBuilder: (_, __, ___) => const SizedBox.shrink()),
        ),
        title: Column(
          children: [
            Text('Selamat Datang,',
                style: GoogleFonts.dmSans(
                    fontSize: 13, color: Colors.white.withValues(alpha: 0.8))),
            Text(user?.name ?? 'Responden',
                style: GoogleFonts.plusJakartaSans(
                    fontSize: 20,
                    fontWeight: FontWeight.w700,
                    color: Colors.white)),
          ],
        ),
        actions: [
          IconButton(
              onPressed: () {},
              icon: const Icon(Icons.notifications_none_rounded,
                  size: 28, color: Colors.white)),
          const SizedBox(width: 8),
        ],
      ),
      body: RefreshIndicator(
        onRefresh: () => Provider.of<HomeProvider>(context, listen: false)
            .fetchDashboardData(),
        color: AppTheme.primary,
        child: SingleChildScrollView(
          physics: const AlwaysScrollableScrollPhysics(),
          padding: const EdgeInsets.symmetric(horizontal: 16),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              const SizedBox(height: 24),
              _buildStatusCard(),
              const SizedBox(height: 24),
              _buildStatistikSection(),
              const SizedBox(height: 32),
              Center(child: Text('Menu Utama', style: AppTheme.heading3)),
              const SizedBox(height: 16),
              _buildActionMenu(context),
              const SizedBox(height: 32),
              Center(child: Text('Pengumuman BPS', style: AppTheme.heading3)),
              const SizedBox(height: 16),
              _buildAnnouncementCard(),
              const SizedBox(height: 24),
              Center(
                  child:
                      Text('Lokasi BPS Kota Madiun', style: AppTheme.heading3)),
              const SizedBox(height: 16),
              _buildMapsCard(),
              const SizedBox(height: 32),
            ],
          ),
        ),
      ),
    );
  }

  // ─── Status Card ───────────────────────────────────────
  Widget _buildStatusCard() {
    return Consumer<HomeProvider>(
      builder: (context, provider, _) {
        if (provider.isLoading) {
          return const Card(
              child: SizedBox(
                  height: 100,
                  child: Center(child: CircularProgressIndicator())));
        }
        final sudah = provider.sudahLaporBulanIni;
        final color = sudah ? AppTheme.success : AppTheme.warning;
        return Card(
          color: color.withValues(alpha: 0.1),
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(16),
            side: BorderSide(color: color.withValues(alpha: 0.3)),
          ),
          child: Padding(
            padding: const EdgeInsets.all(20),
            child: Row(
              children: [
                Icon(sudah ? Icons.check_circle_rounded : Icons.info_rounded,
                    color: color, size: 40),
                const SizedBox(width: 16),
                Expanded(
                  child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                            sudah
                                ? 'Laporan Bulan Ini Lengkap'
                                : 'Menunggu Laporan',
                            style: GoogleFonts.plusJakartaSans(
                                fontWeight: FontWeight.w700,
                                fontSize: 15,
                                color: color)),
                        const SizedBox(height: 4),
                        Text(
                            sudah
                                ? 'Terima kasih, data Anda telah tercatat.'
                                : 'Segera laporkan data bulan ini.',
                            style: AppTheme.bodySmall),
                      ]),
                ),
                if (!sudah)
                  Padding(
                    padding: const EdgeInsets.only(left: 12),
                    child: ElevatedButton(
                      onPressed: () => Navigator.push(
                          context,
                          MaterialPageRoute(
                              builder: (_) => const InputLaporanScreen())),
                      style: ElevatedButton.styleFrom(backgroundColor: color),
                      child: Text('Isi',
                          style: AppTheme.button.copyWith(color: Colors.white)),
                    ),
                  ),
              ],
            ),
          ),
        );
      },
    );
  }

  // ─── Statistik Section ─────────────────────────────────
  Widget _buildStatistikSection() {
    return Consumer<HomeProvider>(
      builder: (context, provider, _) {
        if (provider.isLoading) {
          return const SizedBox(
            height: 180,
            child: Center(child: CircularProgressIndicator()),
          );
        }

        final stat = provider.statistik;
        if (stat == null || stat.jumlahBulan == 0) {
          return Container(
            padding: const EdgeInsets.all(20),
            decoration: BoxDecoration(
              color: Colors.grey.shade50,
              borderRadius: BorderRadius.circular(16),
              border: Border.all(color: Colors.grey.shade200),
            ),
            child: Row(
              children: [
                Icon(Icons.bar_chart_outlined,
                    color: Colors.grey.shade400, size: 36),
                const SizedBox(width: 16),
                Expanded(
                  child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text('Belum Ada Data Statistik',
                            style: GoogleFonts.plusJakartaSans(
                                fontWeight: FontWeight.w700,
                                color: Colors.grey.shade600)),
                        const SizedBox(height: 4),
                        Text(
                            'Mulai laporkan pendapatan untuk melihat grafik & statistik.',
                            style: AppTheme.bodySmall),
                      ]),
                ),
              ],
            ),
          );
        }

        return Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text('Statistik Pendapatan', style: AppTheme.heading3),
            const SizedBox(height: 12),

            // 4 kartu mini
            GridView.count(
              crossAxisCount: 2,
              shrinkWrap: true,
              physics: const NeverScrollableScrollPhysics(),
              mainAxisSpacing: 10,
              crossAxisSpacing: 10,
              childAspectRatio: 2.0,
              children: [
                _buildMiniCard('Total', _fmtRupiah(stat.total),
                    Icons.account_balance_wallet_outlined, AppTheme.primary),
                _buildMiniCard('Rata-rata/Bln', _fmtRupiah(stat.rataRata),
                    Icons.show_chart, AppTheme.success),
                _buildMiniCard('Tertinggi', _fmtRupiah(stat.tertinggi),
                    Icons.arrow_upward_rounded, Colors.green.shade600),
                _buildMiniCard('Terendah', _fmtRupiah(stat.terendah),
                    Icons.arrow_downward_rounded, Colors.orange.shade600),
              ],
            ),

            const SizedBox(height: 12),

            // Pertumbuhan badge
            if (stat.pertumbuhanPct != null)
              Container(
                padding:
                    const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
                decoration: BoxDecoration(
                  color: (stat.pertumbuhanPct! >= 0 ? Colors.green : Colors.red)
                      .withValues(alpha: 0.08),
                  borderRadius: BorderRadius.circular(12),
                  border: Border.all(
                      color: (stat.pertumbuhanPct! >= 0
                              ? Colors.green
                              : Colors.red)
                          .withValues(alpha: 0.2)),
                ),
                child: Row(
                  children: [
                    Icon(
                      stat.pertumbuhanPct! >= 0
                          ? Icons.trending_up_rounded
                          : Icons.trending_down_rounded,
                      color:
                          stat.pertumbuhanPct! >= 0 ? Colors.green : Colors.red,
                      size: 22,
                    ),
                    const SizedBox(width: 10),
                    Expanded(
                      child: Text(
                        'Pertumbuhan bulan terakhir: ${stat.pertumbuhanPct! >= 0 ? '+' : ''}${stat.pertumbuhanPct!.toStringAsFixed(1)}%',
                        style: GoogleFonts.dmSans(
                          fontWeight: FontWeight.w600,
                          color: stat.pertumbuhanPct! >= 0
                              ? Colors.green.shade700
                              : Colors.red.shade700,
                        ),
                      ),
                    ),
                  ],
                ),
              ),

            const SizedBox(height: 16),

            // Grafik tren
            if (stat.tren.length >= 2) _buildGrafik(stat.tren),
          ],
        );
      },
    );
  }

  Widget _buildMiniCard(
      String label, String value, IconData icon, Color color) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
      decoration: BoxDecoration(
        color: color.withValues(alpha: 0.07),
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: color.withValues(alpha: 0.15)),
      ),
      child: Row(
        children: [
          Container(
            padding: const EdgeInsets.all(6),
            decoration: BoxDecoration(
                color: color.withValues(alpha: 0.15),
                borderRadius: BorderRadius.circular(8)),
            child: Icon(icon, color: color, size: 16),
          ),
          const SizedBox(width: 10),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                Text(label,
                    style: GoogleFonts.dmSans(
                        fontSize: 10, color: Colors.grey.shade600)),
                const SizedBox(height: 2),
                Text(value,
                    style: GoogleFonts.plusJakartaSans(
                        fontWeight: FontWeight.w700,
                        fontSize: 12,
                        color: AppTheme.textPrimary),
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildGrafik(List<TrenItem> trenAll) {
    // Opsi filter: null = semua data
    final filterOptions = <int?>[3, 6, 12, null];
    final filterLabels = {3: '3 Bln', 6: '6 Bln', 12: '12 Bln', null: 'Semua'};

    // Ambil data sesuai filter
    final List<TrenItem> tren =
        (_grafikFilter == -1 || trenAll.length <= _grafikFilter)
            ? trenAll
            : trenAll.sublist(trenAll.length - _grafikFilter);

    final spots = tren
        .asMap()
        .entries
        .map((e) => FlSpot(e.key.toDouble(), e.value.jumlah))
        .toList();

    final maxY = tren.map((e) => e.jumlah).reduce((a, b) => a > b ? a : b);

    final int totalPoints = tren.length;
    // Tampilkan semua label jika <= 6 titik, else distribusikan merata maks 6
    final int maxLabels = totalPoints <= 6 ? totalPoints : 6;
    final Set<int> visibleIndices = {};
    if (maxLabels >= totalPoints) {
      for (int k = 0; k < totalPoints; k++) {
        visibleIndices.add(k);
      }
    } else {
      for (int k = 0; k < maxLabels; k++) {
        final idx = (k * (totalPoints - 1) ~/ (maxLabels - 1));
        visibleIndices.add(idx);
      }
    }

    // Garis vertikal indikator di setiap titik data (dashed)
    final verticalLines = List.generate(
        totalPoints,
        (i) => VerticalLine(
              x: i.toDouble(),
              color: AppTheme.primary.withValues(alpha: 0.15),
              strokeWidth: 1,
              dashArray: [3, 5],
            ));

    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        boxShadow: [
          BoxShadow(
              color: Colors.black.withValues(alpha: 0.05),
              blurRadius: 10,
              offset: const Offset(0, 4))
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Header + filter chips
          Row(
            children: [
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text('Tren Pendapatan',
                        style: GoogleFonts.plusJakartaSans(
                            fontWeight: FontWeight.w700,
                            fontSize: 14,
                            color: AppTheme.textPrimary)),
                    Text(
                      tren.length == trenAll.length
                          ? '${tren.length} bulan (semua data)'
                          : '${tren.length} bulan terakhir',
                      style: AppTheme.bodySmall,
                    ),
                  ],
                ),
              ),
              // Filter chips
              Row(
                children: filterOptions.map((opt) {
                  final isActive =
                      opt == null ? _grafikFilter == -1 : _grafikFilter == opt;
                  return Padding(
                    padding: const EdgeInsets.only(left: 4),
                    child: GestureDetector(
                      onTap: () => setState(() => _grafikFilter = opt ?? -1),
                      child: AnimatedContainer(
                        duration: const Duration(milliseconds: 200),
                        padding: const EdgeInsets.symmetric(
                            horizontal: 10, vertical: 5),
                        decoration: BoxDecoration(
                          color: isActive
                              ? AppTheme.primary
                              : Colors.grey.shade100,
                          borderRadius: BorderRadius.circular(20),
                        ),
                        child: Text(
                          filterLabels[opt]!,
                          style: GoogleFonts.dmSans(
                            fontSize: 11,
                            fontWeight: FontWeight.w600,
                            color:
                                isActive ? Colors.white : Colors.grey.shade600,
                          ),
                        ),
                      ),
                    ),
                  );
                }).toList(),
              ),
            ],
          ),
          const SizedBox(height: 16),
          SizedBox(
            height: 160,
            child: LineChart(
              LineChartData(
                minY: 0,
                maxY: maxY * 1.2,
                gridData: FlGridData(
                  show: true,
                  drawVerticalLine: false,
                  getDrawingHorizontalLine: (_) =>
                      FlLine(color: Colors.grey.shade100, strokeWidth: 1),
                ),
                borderData: FlBorderData(show: false),
                extraLinesData: ExtraLinesData(verticalLines: verticalLines),
                titlesData: FlTitlesData(
                  leftTitles: const AxisTitles(
                      sideTitles: SideTitles(showTitles: false)),
                  rightTitles: const AxisTitles(
                      sideTitles: SideTitles(showTitles: false)),
                  topTitles: const AxisTitles(
                      sideTitles: SideTitles(showTitles: false)),
                  bottomTitles: AxisTitles(
                    sideTitles: SideTitles(
                      showTitles: true,
                      getTitlesWidget: (value, meta) {
                        // Bulatkan ke integer terdekat lalu cek persis
                        final i = value.round();
                        if ((value - i).abs() > 0.01)
                          return const SizedBox.shrink();
                        if (i < 0 || i >= tren.length)
                          return const SizedBox.shrink();
                        // Hanya tampilkan index yang sudah dipilih
                        if (!visibleIndices.contains(i))
                          return const SizedBox.shrink();
                        final parts = tren[i].bulanTahun.split('-');
                        if (parts.length != 2) return const SizedBox.shrink();
                        final bulanIdx = int.tryParse(parts[1]);
                        final tahun = parts[0].substring(2);
                        if (bulanIdx == null) return const SizedBox.shrink();
                        final namaBulan = [
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
                        final label = totalPoints > 12
                            ? '${namaBulan[bulanIdx - 1]}\n\'$tahun'
                            : namaBulan[bulanIdx - 1];
                        return Padding(
                          padding: const EdgeInsets.only(top: 6),
                          child: Text(label,
                              textAlign: TextAlign.center,
                              style: GoogleFonts.dmSans(
                                  fontSize: 9, color: Colors.grey.shade500)),
                        );
                      },
                      reservedSize: totalPoints > 12 ? 36 : 26,
                    ),
                  ),
                ),
                lineTouchData: LineTouchData(
                  touchTooltipData: LineTouchTooltipData(
                    getTooltipItems: (touchedSpots) => touchedSpots.map((s) {
                      final i = s.spotIndex;
                      return LineTooltipItem(
                        '${tren[i].bulanLabel}\n${_fmtRupiah(s.y)}',
                        GoogleFonts.dmSans(
                            color: Colors.white,
                            fontSize: 11,
                            fontWeight: FontWeight.w600),
                      );
                    }).toList(),
                  ),
                ),
                lineBarsData: [
                  LineChartBarData(
                    spots: spots,
                    isCurved: true,
                    color: AppTheme.primary,
                    barWidth: 2.5,
                    dotData: FlDotData(
                      show: totalPoints <=
                          24, // sembunyikan dot jika data sangat banyak
                      getDotPainter: (spot, _, __, ___) => FlDotCirclePainter(
                        radius: totalPoints > 12 ? 3 : 4,
                        color: AppTheme.primary,
                        strokeWidth: 2,
                        strokeColor: Colors.white,
                      ),
                    ),
                    belowBarData: BarAreaData(
                      show: true,
                      gradient: LinearGradient(
                        colors: [
                          AppTheme.primary.withValues(alpha: 0.2),
                          AppTheme.primary.withValues(alpha: 0.0)
                        ],
                        begin: Alignment.topCenter,
                        end: Alignment.bottomCenter,
                      ),
                    ),
                  ),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }

  // ─── Action Menu ───────────────────────────────────────
  Widget _buildActionMenu(BuildContext context) {
    return GridView.count(
      crossAxisCount: 3,
      shrinkWrap: true,
      physics: const NeverScrollableScrollPhysics(),
      mainAxisSpacing: 12,
      crossAxisSpacing: 12,
      children: [
        _buildActionItem(context,
            icon: Icons.edit_document,
            label: 'Input Data',
            iconColor: Colors.white,
            bgColor: AppTheme.primary,
            onTap: () async => await Navigator.push(context,
                MaterialPageRoute(builder: (_) => const InputLaporanScreen()))),
        _buildActionItem(context,
            icon: Icons.history_rounded,
            label: 'Riwayat',
            iconColor: Colors.purple.shade700,
            bgColor: Colors.purple.shade100, onTap: () {
          final s = context.findAncestorStateOfType<_HomeScreenState>();
          s?.setState(() => s._currentIndex = 1);
        }),
        _buildActionItem(context,
            icon: Icons.support_agent_rounded,
            label: 'Bantuan',
            iconColor: Colors.green.shade700,
            bgColor: Colors.green.shade100,
            onTap: () => _showBantuanSheet(context)),
      ],
    );
  }

  Widget _buildActionItem(BuildContext context,
      {required IconData icon,
      required String label,
      required Color iconColor,
      required Color bgColor,
      required VoidCallback onTap}) {
    return Card(
      clipBehavior: Clip.antiAlias,
      child: InkWell(
        onTap: onTap,
        child: Column(mainAxisAlignment: MainAxisAlignment.center, children: [
          Container(
              padding: const EdgeInsets.all(14),
              decoration: BoxDecoration(color: bgColor, shape: BoxShape.circle),
              child: Icon(icon, size: 30, color: iconColor)),
          const SizedBox(height: 8),
          Text(label,
              style: GoogleFonts.plusJakartaSans(
                  fontWeight: FontWeight.w600,
                  fontSize: 13,
                  color: AppTheme.textPrimary)),
        ]),
      ),
    );
  }

  // ─── Announcement ──────────────────────────────────────
  Widget _buildAnnouncementCard() {
    const namaBulan = [
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
      'Desember'
    ];
    final now = DateTime.now();
    final lastDay = DateTime(now.year, now.month + 1, 0).day;
    final teks =
        'Batas akhir pelaporan periode ${namaBulan[now.month - 1]} adalah tanggal $lastDay ${namaBulan[now.month - 1]} ${now.year}.';

    return Card(
      color: AppTheme.primary,
      child: Padding(
        padding: const EdgeInsets.all(20),
        child: Row(children: [
          const Icon(Icons.campaign_rounded, color: Colors.white, size: 40),
          const SizedBox(width: 16),
          Expanded(
              child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                Text('Info Penting dari BPS',
                    style: GoogleFonts.plusJakartaSans(
                        fontSize: 15,
                        fontWeight: FontWeight.w700,
                        color: Colors.white)),
                const SizedBox(height: 4),
                Text(teks,
                    style: GoogleFonts.dmSans(
                        color: Colors.white.withValues(alpha: 0.9),
                        fontSize: 13)),
              ])),
        ]),
      ),
    );
  }

  // ─── Maps Card ─────────────────────────────────────────
  Widget _buildMapsCard() {
    return Card(
      clipBehavior: Clip.antiAlias,
      child: InkWell(
        onTap: () =>
            _launchUrl('https://maps.app.goo.gl/NTdpGoUFHxMtN4Ys9', context),
        child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
          Container(
            width: double.infinity,
            height: 160,
            decoration: const BoxDecoration(
                image: DecorationImage(
                    image: AssetImage('assets/images/maps.png'),
                    fit: BoxFit.cover)),
            child: Container(
                decoration: BoxDecoration(
                    gradient: LinearGradient(
                        begin: Alignment.topCenter,
                        end: Alignment.bottomCenter,
                        colors: [
                  Colors.transparent,
                  Colors.black.withValues(alpha: 0.15)
                ]))),
          ),
          Padding(
            padding: const EdgeInsets.all(16),
            child: Row(children: [
              Container(
                  padding: const EdgeInsets.all(10),
                  decoration: BoxDecoration(
                      color: AppTheme.primary.withValues(alpha: 0.1),
                      borderRadius: BorderRadius.circular(10)),
                  child: const Icon(Icons.map_rounded,
                      color: AppTheme.primary, size: 24)),
              const SizedBox(width: 12),
              Expanded(
                  child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                    Text('BPS Kota Madiun',
                        style: GoogleFonts.plusJakartaSans(
                            fontWeight: FontWeight.w700,
                            fontSize: 15,
                            color: AppTheme.textPrimary)),
                    const SizedBox(height: 2),
                    Text('Tap untuk buka di Google Maps',
                        style: AppTheme.bodySmall),
                  ])),
              const Icon(Icons.open_in_new_rounded,
                  color: AppTheme.accent, size: 20),
            ]),
          ),
        ]),
      ),
    );
  }

  // ─── Bantuan Sheet ─────────────────────────────────────
  void _showBantuanSheet(BuildContext context) {
    showModalBottomSheet(
      context: context,
      shape: const RoundedRectangleBorder(
          borderRadius: BorderRadius.vertical(top: Radius.circular(24))),
      builder: (_) => Padding(
        padding: const EdgeInsets.fromLTRB(24, 16, 24, 32),
        child: Column(mainAxisSize: MainAxisSize.min, children: [
          Container(
              width: 40,
              height: 4,
              margin: const EdgeInsets.only(bottom: 20),
              decoration: BoxDecoration(
                  color: AppTheme.border,
                  borderRadius: BorderRadius.circular(2))),
          Container(
              padding: const EdgeInsets.all(16),
              decoration: BoxDecoration(
                  color: Colors.green.shade100, shape: BoxShape.circle),
              child: Icon(Icons.support_agent_rounded,
                  size: 36, color: Colors.green.shade700)),
          const SizedBox(height: 12),
          Text('Hubungi BPS',
              style: GoogleFonts.plusJakartaSans(
                  fontSize: 20,
                  fontWeight: FontWeight.w700,
                  color: AppTheme.textPrimary)),
          const SizedBox(height: 4),
          Text('Kami siap membantu Anda', style: AppTheme.bodySmall),
          const SizedBox(height: 24),
          _buildContactButton(
              icon: Icons.chat_rounded,
              label: 'Chat via WhatsApp',
              subtitle: '+62 812-3448-0592',
              color: const Color(0xFF25D366),
              bgColor: const Color(0xFF25D366).withValues(alpha: 0.08),
              onTap: () => _launchUrl('https://wa.me/+6281234480592', context)),
          const SizedBox(height: 12),
          _buildContactButton(
              icon: Icons.phone_rounded,
              label: 'Hubungi via Telepon',
              subtitle: '0812345678',
              color: Colors.green.shade600,
              bgColor: Colors.green.shade50,
              onTap: () => _launchUrl('tel:0812345678', context)),
          const SizedBox(height: 12),
          _buildContactButton(
              icon: Icons.email_rounded,
              label: 'Kirim Email',
              subtitle: 'bps@go.id',
              color: AppTheme.accent,
              bgColor: AppTheme.accent.withValues(alpha: 0.08),
              onTap: () => _launchUrl(
                  'mailto:bps@go.id?subject=Bantuan%20SIBUMA', context)),
        ]),
      ),
    );
  }

  Widget _buildContactButton(
      {required IconData icon,
      required String label,
      required String subtitle,
      required Color color,
      required Color bgColor,
      required VoidCallback onTap}) {
    return Material(
      color: bgColor,
      borderRadius: BorderRadius.circular(12),
      child: InkWell(
        borderRadius: BorderRadius.circular(12),
        onTap: onTap,
        child: Padding(
          padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 16),
          child: Row(children: [
            Container(
                padding: const EdgeInsets.all(10),
                decoration: BoxDecoration(
                    color: color, borderRadius: BorderRadius.circular(10)),
                child: Icon(icon, color: Colors.white, size: 22)),
            const SizedBox(width: 16),
            Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
              Text(label,
                  style: GoogleFonts.plusJakartaSans(
                      fontWeight: FontWeight.w600, color: color, fontSize: 15)),
              Text(subtitle, style: AppTheme.bodySmall),
            ]),
            const Spacer(),
            Icon(Icons.arrow_forward_ios_rounded, size: 14, color: color),
          ]),
        ),
      ),
    );
  }

  Future<void> _launchUrl(String url, BuildContext context) async {
    final uri = Uri.parse(url);
    if (await canLaunchUrl(uri)) {
      await launchUrl(uri);
    } else {
      if (!context.mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(
          content: Text('Tidak dapat membuka aplikasi.'),
          backgroundColor: AppTheme.danger));
    }
  }
}
