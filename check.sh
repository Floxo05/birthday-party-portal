#!/bin/bash
set -e

GREEN="\033[0;32m"
RED="\033[0;31m"
CYAN="\033[0;36m"
NC="\033[0m"

composer_ok=false
phpstan_ok=false
phpunit_ok=false
audit_ok=false

echo -e "${CYAN}📦 Running Composer install...${NC}"
if docker compose run --rm app composer install --no-interaction; then
    composer_ok=true
    echo -e "${GREEN}✔ Composer install OK${NC}"
else
    echo -e "${RED}✖ Composer install FAILED${NC}"
fi

echo -e "${CYAN}🔍 Running PHPStan static analysis...${NC}"
if docker compose run --rm app ./vendor/bin/phpstan analyse src; then
    phpstan_ok=true
    echo -e "${GREEN}✔ PHPStan OK${NC}"
else
    echo -e "${RED}✖ PHPStan FAILED${NC}"
fi

echo -e "${CYAN}🧪 Running PHPUnit tests...${NC}"
if docker compose run --rm app ./vendor/bin/phpunit; then
    phpunit_ok=true
    echo -e "${GREEN}✔ PHPUnit OK${NC}"
else
    echo -e "${RED}✖ PHPUnit FAILED${NC}"
fi

echo -e "${CYAN}🕵️ Running Composer Security Audit...${NC}"
if docker compose run --rm app composer audit; then
    audit_ok=true
    echo -e "${GREEN}✔ No known security vulnerabilities${NC}"
else
    echo -e "${RED}✖ Vulnerabilities found by composer audit${NC}"
fi

echo -e "\n${CYAN}🧾 Summary:${NC}"
echo -e "Composer:\t$( [ "$composer_ok" = true ] && echo -e "${GREEN}✔ OK${NC}" || echo -e "${RED}✖ FAILED${NC}" )"
echo -e "PHPStan:\t$( [ "$phpstan_ok" = true ] && echo -e "${GREEN}✔ OK${NC}" || echo -e "${RED}✖ FAILED${NC}" )"
echo -e "PHPUnit:\t$( [ "$phpunit_ok" = true ] && echo -e "${GREEN}✔ OK${NC}" || echo -e "${RED}✖ FAILED${NC}" )"
echo -e "Audit:\t\t$( [ "$audit_ok" = true ] && echo -e "${GREEN}✔ OK${NC}" || echo -e "${RED}✖ FAILED${NC}" )"

if $composer_ok && $phpstan_ok && $phpunit_ok && $audit_ok; then
    echo -e "\n${GREEN}🎉 All checks passed!${NC}"
    exit 0
else
    echo -e "\n${RED}❌ Some checks failed.${NC}"
    exit 1
fi
