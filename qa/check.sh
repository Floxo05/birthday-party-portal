#!/bin/bash
set -e

# 🔍 Ermittle den Pfad zum Verzeichnis, in dem das Skript selbst liegt
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
STEP_DIR="$SCRIPT_DIR/steps"

# Farben
GREEN="\033[0;32m"
RED="\033[0;31m"
CYAN="\033[0;36m"
NC="\033[0m"

declare -A results

run_step() {
    name=$1
    path="$STEP_DIR/$name.sh"
    if [ ! -f "$path" ]; then
        echo -e "${RED}❌ Step '$name' not found!${NC}"
        results["$name"]="❌ Missing"
        return
    fi

    echo -e "${CYAN}▶ Running: $name${NC}"
    if bash "$path"; then
        echo -e "${GREEN}✔ $name passed${NC}"
        results["$name"]="✔ OK"
    else
        echo -e "${RED}✖ $name failed${NC}"
        results["$name"]="✖ FAILED"
    fi
}

# ✍️ Hier einfach neue Step-Namen eintragen
steps=("composer" "phpstan" "coverage-unit" "coverage-integration" "coverage-feature" "audit")

# Schrittweise ausführen
for step in "${steps[@]}"; do
    run_step "$step"
done

# Zusammenfassung
echo -e "\n${CYAN}🧾 Summary:${NC}"
for step in "${steps[@]}"; do
    echo -e "$step:\t${results[$step]}"
done

# Exit-Code setzen
for status in "${results[@]}"; do
    if [[ "$status" == *FAILED* || "$status" == *Missing* ]]; then
        echo -e "\n${RED}❌ Some checks failed.${NC}"
        exit 1
    fi
done

echo -e "\n${GREEN}🎉 All checks passed!${NC}"
exit 0
