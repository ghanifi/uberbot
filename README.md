# Uber Price Bot

A Node.js bot that tracks Uber pricing across multiple routes using Playwright for web automation and PostgreSQL for data storage. Includes REST API endpoints for price queries and historical analysis, with scheduled scraping via cron jobs.

## Features

- **Web Scraping**: Automated Uber price extraction using Playwright with mobile user-agent
- **Multi-Account Management**: Rotate between multiple Uber accounts with automatic ban detection
- **Price History**: Track historical prices with min/max/average statistics
- **REST API**: Query prices dynamically or by route with interpolation support
- **Scheduled Scraping**: Configurable cron-based scraping (default: 6am, 9am, 6pm, 11pm)
- **Logging**: Comprehensive file and console logging with levels
- **Error Handling**: Custom error classes and retry logic with account health tracking
- **Database**: PostgreSQL with migrations and query abstraction
- **CORS Support**: Configure allowed origins for API access

## Quick Start

### Local Development

```bash
# Install dependencies
npm install

# Setup environment
cp .env.example .env

# Configure Uber accounts in .env
UBER_ACCOUNTS='[{"email":"bot1@example.com","password":"pass1"},{"email":"bot2@example.com","password":"pass2"}]'

# Run migrations
npm run migrate

# Start in development mode
npm run dev
```

### Docker Deployment

```bash
# Build image
docker build -t uber-price-bot .

# Run container
docker run -d \
  --name uber-bot \
  -e DATABASE_URL="postgresql://user:pass@db:5432/uber_bot" \
  -e UBER_ACCOUNTS='[{"email":"bot@example.com","password":"pass"}]' \
  -e API_PORT=3000 \
  uber-price-bot

# View logs
docker logs -f uber-bot
```

### Docker Compose

```bash
docker-compose up -d
```

## API Endpoints

### Get Latest Prices for Route

```bash
GET /api/price/:routeId

Response:
{
  "route_id": 1,
  "pickup": "Heathrow Airport",
  "dropoff": "Central London",
  "city": "London",
  "prices": {
    "UberX": { "price": 25.50, "checkedAt": "2026-05-01T14:30:00Z" },
    "UberXL": { "price": 35.00, "checkedAt": "2026-05-01T14:30:00Z" }
  }
}
```

### Get Interpolated Price

```bash
GET /api/price/dynamic?pickup=Heathrow&dropoff=Central&vehicle=UberX

Response:
{
  "price": 25.50,
  "checkedAt": "2026-05-01T14:30:00Z",
  "source": "interpolated",
  "nearestRoute": {
    "id": 1,
    "city": "London",
    "pickup": "Heathrow Airport",
    "dropoff": "Central London"
  }
}
```

### Get Price History

```bash
GET /api/prices/:routeId?days=30&vehicle=UberX

Response:
{
  "route_id": 1,
  "pickup": "Heathrow Airport",
  "dropoff": "Central London",
  "vehicle": "UberX",
  "days": 30,
  "prices": [
    { "price": 25.50, "checkedAt": "2026-05-01T14:30:00Z", "usedAccount": "bot@example.com" }
  ],
  "stats": {
    "min": 23.00,
    "max": 28.50,
    "average": 25.50,
    "dataPoints": 15
  },
  "dataPoints": 15
}
```

### Health Check

```bash
GET /api/health

Response:
{
  "status": "ok",
  "accounts": {
    "active": 2,
    "banned": 0,
    "total": 2,
    "totalQueries": 48
  }
}
```

## Configuration

### Environment Variables

```env
# Database
DATABASE_URL=postgresql://user:password@localhost:5432/uber_bot

# Uber Accounts (JSON array)
UBER_ACCOUNTS=[{"email":"bot@example.com","password":"pass"}]

# API
API_PORT=3000
API_HOST=localhost

# Scraping
CRON_SCHEDULE="0 6,9,18,23 * * *"  # 6am, 9am, 6pm, 11pm
ENABLE_SCRAPER=true
ENABLE_API=true

# Environment
NODE_ENV=development
```

### Cron Schedule Format

```
0    6,9,18,23    *    *    *
│    │             │    │    │
│    │             │    │    └── Day of week (0-6, 0=Sunday)
│    │             │    └────── Month (1-12)
│    │             └─────────── Day of month (1-31)
│    └────────────────────────── Hour (0-23)
└────────────────────────────── Minute (0-59)

Current: Runs at 6am, 9am, 6pm, 11pm every day
```

## Testing

```bash
# Run all tests
npm test

# Run with coverage
npm test -- --coverage

# Run specific test file
npm test tests/api.test.js
```

## Deployment

### Coolify

1. Connect GitHub repository
2. Create PostgreSQL database
3. Set environment variables:
   - DATABASE_URL
   - UBER_ACCOUNTS
   - API_PORT
4. Deploy from branch
5. View logs in Coolify dashboard

### VPS (Linux)

```bash
# Clone repository
git clone <repo> /opt/uber-bot
cd /opt/uber-bot

# Install dependencies
npm install

# Setup systemd service
sudo tee /etc/systemd/system/uber-bot.service > /dev/null <<EOF
[Unit]
Description=Uber Price Bot
After=network.target postgresql.service

[Service]
Type=simple
User=ubuntu
WorkingDirectory=/opt/uber-bot
ExecStart=/usr/bin/node src/index.js
Restart=on-failure
RestartSec=10
StandardOutput=journal
StandardError=journal
Environment="NODE_ENV=production"

[Install]
WantedBy=multi-user.target
EOF

# Enable and start
sudo systemctl daemon-reload
sudo systemctl enable uber-bot
sudo systemctl start uber-bot

# View logs
sudo journalctl -u uber-bot -f
```

## Monitoring

### Log Files

Application logs are stored in `logs/scraper.log` with timestamps and severity levels:
- INFO: Normal operations
- WARN: Warnings and non-critical issues
- ERROR: Errors with stack traces
- DEBUG: Development-only debug messages

### Health Endpoint

Monitor application health via `/api/health` endpoint:
- Active/banned accounts count
- Total queries executed
- Service status

### Account Management

The system automatically:
- Tracks successful and failed queries per account
- Bans accounts after 5 failed attempts
- Rotates between active accounts
- Resets failure counts on success

## Architecture

```
┌─────────────────────────────────────┐
│         Cron Scheduler              │
│     (node-cron, 4x daily)           │
└──────────────┬──────────────────────┘
               │
               ▼
┌─────────────────────────────────────┐
│     Scraper Orchestrator            │
│  - Account rotation                 │
│  - Route management                 │
└──────────────┬──────────────────────┘
               │
               ▼
┌─────────────────────────────────────┐
│    Playwright Browser               │
│  - Mobile user-agent                │
│  - Login & navigation               │
│  - Price extraction                 │
└──────────────┬──────────────────────┘
               │
               ▼
┌─────────────────────────────────────┐
│    Price Parser & Storage           │
│  - Extract vehicle types & prices   │
│  - Insert to PostgreSQL             │
└──────────────┬──────────────────────┘
               │
               ▼
┌─────────────────────────────────────┐
│      PostgreSQL Database            │
│  - Routes, Prices, Accounts         │
└─────────────────────────────────────┘
               ▲
               │
┌──────────────┴──────────────────────┐
│        REST API (Express)           │
│  - GET /api/price/:routeId          │
│  - GET /api/prices/:routeId         │
│  - GET /api/price/dynamic           │
│  - GET /api/health                  │
└─────────────────────────────────────┘
```

## Troubleshooting

### "No active accounts available"

All configured accounts have been banned after 5 failed attempts. Check:
- Account credentials in UBER_ACCOUNTS
- Uber.com accessibility/captcha
- Network connectivity
- Ban reasons in logs

### "UBER_ACCOUNTS environment variable is not set"

Set valid JSON array in .env:
```env
UBER_ACCOUNTS=[{"email":"bot@example.com","password":"pass"}]
```

### Empty price history

Scraping may not have run yet. Check:
- CRON_SCHEDULE is set correctly
- ENABLE_SCRAPER=true
- Container/process is running
- Account health via /api/health

### Database connection errors

Verify:
- DATABASE_URL format: postgresql://user:pass@host:5432/db
- PostgreSQL service is running
- Network access to database host
- Migrations have run (npm run migrate)

## Development

### Project Structure

```
src/
  ├── api/              # REST API (Express)
  │   ├── controllers.js
  │   ├── routes.js
  │   └── server.js
  ├── database/         # PostgreSQL
  │   ├── client.js
  │   ├── migrations.js
  │   └── queries.js
  ├── jobs/             # Scheduling
  │   └── cronjob.js
  ├── pricing/          # Price analysis
  │   └── engine.js
  ├── scraper/          # Web automation
  │   ├── account-manager.js
  │   ├── index.js
  │   ├── parser.js
  │   └── playwright-client.js
  ├── utils/            # Utilities
  │   ├── errors.js
  │   └── logger.js
  ├── config.js         # Configuration
  ├── constants.js      # Constants
  └── index.js          # Entry point
tests/
  ├── api.test.js
  ├── database.test.js
  ├── pricing.test.js
  └── scraper.test.js
```

## License

ISC
