#!/usr/bin/env bash
# Rekap kontribusi satu sesi berdasarkan tag penutupnya.
# Pemakaian: scripts/rekap-sesi.sh <nomor-sesi>
set -euo pipefail

sesi="${1:?Sebutkan nomor sesi, misalnya: scripts/rekap-sesi.sh 1}"
akhir="sesi-${sesi}"
git rev-parse -q --verify "refs/tags/${akhir}" >/dev/null || { echo "Tag ${akhir} belum ada."; exit 1; }

if [ "${sesi}" -gt 1 ]; then
    rentang="sesi-$((sesi - 1))..${akhir}"
else
    rentang="${akhir}"
fi

echo "== Commit per anggota (${rentang}, tanpa merge commit)"
git shortlog -sn --no-merges "${rentang}"

echo
echo "== Baris ditambah dan dihapus per anggota"
git log --no-merges --format='@%aN' --numstat "${rentang}" | awk '
/^@/ { nama = substr($0, 2); next }
NF == 3 && $1 != "-" { tambah[nama] += $1; hapus[nama] += $2 }
END { for (n in tambah) printf "%-12s +%d -%d\n", n, tambah[n], hapus[n] }' | sort

echo
echo "== PR yang masuk ke main"
git log --merges --first-parent --format='%s' "${rentang}" | sed 's/^/  /'
