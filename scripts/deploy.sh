#!/bin/bash
# =============================================================================
# Larabase Deployment Helper Script
# =============================================================================
# Run this script on the Docker host after deployment.
# Usage: bash scripts/deploy.sh [command]
#
# Commands:
#   migrate     - Run database migrations
#   seed        - Seed the database
#   cache       - Clear and rebuild all caches
#   logs        - View application logs
#   shell       - Open shell in app container
#   status      - Check all containers status
#   backup      - Create database backup
#   health      - Check application health
# =============================================================================

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

APP_CONTAINER=${APP_CONTAINER:-larabase-app}
POSTGRES_CONTAINER=${POSTGRES_CONTAINER:-larabase-postgres}
REDIS_CONTAINER=${REDIS_CONTAINER:-larabase-redis}

# Helper function to run artisan commands
artisan() {
    if [ -z "$APP_CONTAINER" ]; then
        echo -e "${RED}Error: App container not found${NC}"
        exit 1
    fi
    docker exec -it "$APP_CONTAINER" php artisan "$@"
}

case "$1" in
    migrate)
        echo -e "${YELLOW}Running migrations...${NC}"
        artisan migrate --force
        echo -e "${GREEN}Migrations complete!${NC}"
        ;;

    seed)
        echo -e "${YELLOW}Seeding database...${NC}"
        artisan db:seed --force
        echo -e "${GREEN}Seeding complete!${NC}"
        ;;

    cache)
        echo -e "${YELLOW}Clearing and rebuilding caches...${NC}"
        artisan config:clear
        artisan route:clear
        artisan view:clear
        artisan config:cache
        artisan route:cache
        artisan view:cache
        artisan event:cache
        echo -e "${GREEN}Cache rebuilt!${NC}"
        ;;

    logs)
        if [ -z "$APP_CONTAINER" ]; then
            echo -e "${RED}Error: App container not found${NC}"
            exit 1
        fi
        echo -e "${YELLOW}Showing logs (Ctrl+C to exit)...${NC}"
        docker logs -f "$APP_CONTAINER" --tail 100
        ;;

    shell)
        if [ -z "$APP_CONTAINER" ]; then
            echo -e "${RED}Error: App container not found${NC}"
            exit 1
        fi
        echo -e "${YELLOW}Opening shell in app container...${NC}"
        docker exec -it "$APP_CONTAINER" bash
        ;;

    status)
        echo -e "${YELLOW}Container Status:${NC}"
        docker ps --format "table {{.Names}}\t{{.Status}}\t{{.Ports}}"
        echo ""
        echo -e "${YELLOW}Memory Usage:${NC}"
        free -h
        echo ""
        echo -e "${YELLOW}Docker Stats:${NC}"
        docker stats --no-stream
        ;;

    backup)
        if [ -z "$POSTGRES_CONTAINER" ]; then
            echo -e "${RED}Error: PostgreSQL container not found${NC}"
            exit 1
        fi
        BACKUP_FILE="larabase_$(date +%Y%m%d_%H%M%S).sql"
        mkdir -p ~/backups
        echo -e "${YELLOW}Creating backup: ${BACKUP_FILE}${NC}"
        docker exec "$POSTGRES_CONTAINER" pg_dump -U larabase larabase > ~/backups/"$BACKUP_FILE"
        echo -e "${GREEN}Backup saved to ~/backups/${BACKUP_FILE}${NC}"
        ;;

    health)
        echo -e "${YELLOW}Checking application health...${NC}"
        HEALTH_URL="${APP_URL:-http://localhost}/api/health"
        curl -s "$HEALTH_URL" | jq . 2>/dev/null || curl -s "$HEALTH_URL"
        echo ""

        echo -e "${YELLOW}Checking PostgreSQL...${NC}"
        if docker exec "$POSTGRES_CONTAINER" pg_isready -U larabase -d larabase >/dev/null 2>&1; then
            echo -e "${GREEN}PostgreSQL: OK${NC}"
        else
            echo -e "${RED}PostgreSQL: FAILED${NC}"
        fi

        echo -e "${YELLOW}Checking Redis...${NC}"
        if docker exec "$REDIS_CONTAINER" redis-cli ping >/dev/null 2>&1; then
            echo -e "${GREEN}Redis: OK${NC}"
        else
            echo -e "${RED}Redis: FAILED${NC}"
        fi
        ;;

    key)
        echo -e "${YELLOW}Generating new APP_KEY...${NC}"
        artisan key:generate --force --show
        echo -e "${GREEN}Copy this key to your deployment environment variables${NC}"
        ;;

    *)
        echo "Larabase Deployment Helper"
        echo ""
        echo "Usage: bash scripts/deploy.sh [command]"
        echo ""
        echo "Commands:"
        echo "  migrate  - Run database migrations"
        echo "  seed     - Seed the database"
        echo "  cache    - Clear and rebuild all caches"
        echo "  logs     - View application logs"
        echo "  shell    - Open shell in app container"
        echo "  status   - Check all containers status"
        echo "  backup   - Create database backup"
        echo "  health   - Check application health"
        echo "  key      - Generate new APP_KEY"
        ;;
esac
