import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:sibuma_mobile/core/theme/app_theme.dart';

// ─────────────────────────────────────────────────────────
// Rupiah Formatter
// ─────────────────────────────────────────────────────────
class RupiahInputFormatter extends TextInputFormatter {
  @override
  TextEditingValue formatEditUpdate(TextEditingValue old, TextEditingValue nv) {
    String d = nv.text.replaceAll(RegExp(r'[^0-9]'), '');
    if (d.isEmpty) return nv.copyWith(text: '');
    d = d.replaceFirst(RegExp(r'^0+'), '');
    if (d.isEmpty) d = '0';
    final fmt = d.replaceAllMapped(
        RegExp(r'(\d{1,3})(?=(\d{3})+(?!\d))'), (m) => '${m[1]}.');
    return TextEditingValue(
      text: 'Rp $fmt',
      selection: TextSelection.collapsed(offset: 'Rp $fmt'.length),
    );
  }
}

// ─────────────────────────────────────────────────────────
// MonthPickerField — Popup dropdown di bawah field
// ─────────────────────────────────────────────────────────
class MonthPickerField extends StatefulWidget {
  final String value;
  final String label;
  final ValueChanged<String> onChanged;

  const MonthPickerField({
    super.key,
    required this.value,
    required this.label,
    required this.onChanged,
  });

  @override
  State<MonthPickerField> createState() => _MonthPickerFieldState();
}

class _MonthPickerFieldState extends State<MonthPickerField> {
  static const _nama = [
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
  static const _singkat = [
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
    'Des',
  ];

  final LayerLink _link = LayerLink();
  OverlayEntry? _entry;
  bool _open = false;
  late int _year;

  @override
  void initState() {
    super.initState();
    _year = int.tryParse(widget.value.split('-')[0]) ?? DateTime.now().year;
  }

  @override
  void dispose() {
    _close();
    super.dispose();
  }

  void _close() {
    _entry?.remove();
    _entry = null;
    if (mounted) setState(() => _open = false);
  }

  void _toggle() => _open ? _close() : _openPicker();

  void _openPicker() {
    _year = int.tryParse(widget.value.split('-')[0]) ?? DateTime.now().year;

    final overlay = Overlay.of(context);
    final box = context.findRenderObject() as RenderBox;

    _entry = OverlayEntry(builder: (overlayCtx) {
      return GestureDetector(
        behavior: HitTestBehavior.translucent,
        onTap: _close,
        child: Stack(children: [
          CompositedTransformFollower(
            link: _link,
            showWhenUnlinked: false,
            offset: Offset(0, box.size.height + 4),
            child: GestureDetector(
              onTap: () {},
              child: Material(
                elevation: 8,
                borderRadius: BorderRadius.circular(14),
                color: Colors.white,
                child: _PopupContent(
                  value: widget.value,
                  initYear: _year,
                  fieldWidth: box.size.width,
                  namaBulan: _nama,
                  singkat: _singkat,
                  onPick: (v) {
                    widget.onChanged(v);
                    _close();
                  },
                  onClose: _close,
                ),
              ),
            ),
          ),
        ]),
      );
    });

    overlay.insert(_entry!);
    setState(() => _open = true);
  }

  @override
  Widget build(BuildContext context) {
    return CompositedTransformTarget(
      link: _link,
      child: GestureDetector(
        onTap: _toggle,
        child: Container(
          padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 16),
          decoration: BoxDecoration(
            color: Colors.white,
            border: Border.all(
                color: _open ? AppTheme.accent : Colors.grey.shade300,
                width: _open ? 2 : 1),
            borderRadius: BorderRadius.circular(12),
          ),
          child: Row(children: [
            const Icon(Icons.calendar_month_rounded,
                color: AppTheme.primary, size: 22),
            const SizedBox(width: 12),
            Expanded(
                child: Text(
              widget.label.isNotEmpty ? widget.label : 'Pilih bulan laporan...',
              style: GoogleFonts.dmSans(
                fontSize: 15,
                fontWeight: FontWeight.w500,
                color: widget.label.isNotEmpty
                    ? AppTheme.textPrimary
                    : Colors.grey.shade400,
              ),
            )),
            AnimatedRotation(
              turns: _open ? 0.5 : 0,
              duration: const Duration(milliseconds: 200),
              child: Icon(Icons.keyboard_arrow_down_rounded,
                  color: Colors.grey.shade400, size: 24),
            ),
          ]),
        ),
      ),
    );
  }
}

// ─────────────────────────────────────────────────────────
// Konten popup (private)
// ─────────────────────────────────────────────────────────
class _PopupContent extends StatefulWidget {
  final String value;
  final int initYear;
  final double fieldWidth;
  final List<String> namaBulan;
  final List<String> singkat;
  final ValueChanged<String> onPick;
  final VoidCallback onClose;

  const _PopupContent({
    required this.value,
    required this.initYear,
    required this.fieldWidth,
    required this.namaBulan,
    required this.singkat,
    required this.onPick,
    required this.onClose,
  });

  @override
  State<_PopupContent> createState() => _PopupContentState();
}

class _PopupContentState extends State<_PopupContent> {
  late int _year;

  static String _nowYM() {
    final n = DateTime.now();
    return '${n.year}-${n.month.toString().padLeft(2, '0')}';
  }

  @override
  void initState() {
    super.initState();
    _year = widget.initYear;
  }

  @override
  Widget build(BuildContext context) {
    final popupW = widget.fieldWidth.clamp(280.0, 340.0);
    return SizedBox(
      width: popupW,
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(mainAxisSize: MainAxisSize.min, children: [
          // Header tahun
          Row(mainAxisAlignment: MainAxisAlignment.spaceBetween, children: [
            IconButton(
              onPressed: () => setState(() => _year--),
              icon: const Icon(Icons.chevron_left_rounded),
              style: IconButton.styleFrom(
                backgroundColor: Colors.grey.shade100,
                shape: const CircleBorder(),
                padding: const EdgeInsets.all(6),
                minimumSize: const Size(32, 32),
              ),
            ),
            Text('$_year',
                style: GoogleFonts.plusJakartaSans(
                    fontSize: 18,
                    fontWeight: FontWeight.w800,
                    color: AppTheme.textPrimary)),
            IconButton(
              onPressed: () => setState(() => _year++),
              icon: const Icon(Icons.chevron_right_rounded),
              style: IconButton.styleFrom(
                backgroundColor: Colors.grey.shade100,
                shape: const CircleBorder(),
                padding: const EdgeInsets.all(6),
                minimumSize: const Size(32, 32),
              ),
            ),
          ]),
          const SizedBox(height: 12),
          // Grid 12 bulan
          GridView.builder(
            shrinkWrap: true,
            physics: const NeverScrollableScrollPhysics(),
            gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
              crossAxisCount: 3,
              mainAxisSpacing: 8,
              crossAxisSpacing: 8,
              childAspectRatio: 2.2,
            ),
            itemCount: 12,
            itemBuilder: (gridCtx, i) {
              final mm = (i + 1).toString().padLeft(2, '0');
              final val = '$_year-$mm';
              final isSelected = val == widget.value;
              final isNow = val == _nowYM();
              return GestureDetector(
                onTap: () => widget.onPick(val),
                child: AnimatedContainer(
                  duration: const Duration(milliseconds: 130),
                  decoration: BoxDecoration(
                    color: isSelected
                        ? AppTheme.primary
                        : isNow
                            ? AppTheme.primary.withValues(alpha: 0.08)
                            : Colors.grey.shade50,
                    borderRadius: BorderRadius.circular(8),
                    border: Border.all(
                      color: isSelected
                          ? AppTheme.primary
                          : isNow
                              ? AppTheme.primary.withValues(alpha: 0.35)
                              : Colors.grey.shade200,
                      width: isSelected ? 2 : 1,
                    ),
                  ),
                  alignment: Alignment.center,
                  child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Text(widget.singkat[i],
                            style: GoogleFonts.plusJakartaSans(
                              fontSize: 12,
                              fontWeight: FontWeight.w600,
                              color: isSelected
                                  ? Colors.white
                                  : AppTheme.textPrimary,
                            )),
                        if (isNow && !isSelected)
                          Container(
                            width: 4,
                            height: 4,
                            margin: const EdgeInsets.only(top: 2),
                            decoration: const BoxDecoration(
                                color: AppTheme.primary,
                                shape: BoxShape.circle),
                          ),
                      ]),
                ),
              );
            },
          ),
          const SizedBox(height: 10),
          Text('• Titik biru = bulan saat ini',
              style: GoogleFonts.dmSans(
                  fontSize: 10, color: Colors.grey.shade400)),
        ]),
      ),
    );
  }
}
