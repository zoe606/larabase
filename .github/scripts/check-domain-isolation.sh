#!/usr/bin/env bash
set -euo pipefail

cd "$(dirname "${BASH_SOURCE[0]}")/../.."

patterns='TurboPiggy|personal[[:space:]-]+finance|rupiah|Bank[[:space:]_-]*Statements?|Budgets?|Device[[:space:]_-]*Tokens?|FCM|Forecasts?|Fuel[[:space:]_-]*Logs?|Installments?|Payment[[:space:]_-]*Methods?|Receipts?|Service[[:space:]_-]*Plans?|Spends?|SyncService|sync/(pull|push)|WatermelonDB|Touring[[:space:]_-]*Routes?|Vehicles?|Catalog(Category|Item)|CategorizationRule|SharedExpense|Wishlist'
scan_roots=(app routes config database resources tests README.md docs)

for root in "${scan_roots[@]}"; do
    test -e "$root"
done

paths=$(find "${scan_roots[@]}" -type f)
if printf '%s\n' "$paths" | grep -n -i -E "$patterns"; then
    echo 'product domain path found in Larabase' >&2
    exit 1
else
    status=$?
    [[ "$status" -eq 1 ]] || exit "$status"
fi

if grep -r -n -i -E -I "$patterns" "${scan_roots[@]}"; then
    echo 'product domain reference found in Larabase' >&2
    exit 1
else
    status=$?
    [[ "$status" -eq 1 ]] || exit "$status"
fi

echo 'domain isolation verified'
