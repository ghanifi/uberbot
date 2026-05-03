# Uber Price Bot

Automated Uber pricing tracker for London and Antalya with historical data storage and REST API.

## Features

- **Playwright-based scraper** — Mobile user-agent browser automation
- **Account rotation** — Multiple test accounts to avoid bans
- **SQLite storage** — File-based database, indefinite price history
- **Address-based queries** — Search by pickup/dropoff locations
- **REST API** — Real-time price lookups and historical data
- **Scheduled scraping** — 4-6 times daily at peak hours
- **Error tracking** — Account health monitoring and logging

## Setup

```bash
npm install
```

Create `.env`:
```
NODE_ENV=production
API_PORT=3001
LOG_LEVEL=info
UBER_ACCOUNTS=[
  {"email":"bot-1@gmail.com","password":"pass1"},
  {"email":"bot-2@gmail.com","password":"pass2"},
  {"email":"bot-3@gmail.com","password":"pass3"}
]
```

## Run Locally

```bash
npm start
```

Server runs on `http://localhost:3001`

## API Endpoints

### Get Latest Price
```
GET /api/price?city=London&pickup=Heathrow%20Airport&dropoff=Piccadilly&vehicle=UberX
```

### Price History (Last 30 days)
```
GET /api/prices?city=London&pickup=Heathrow%20Airport&dropoff=Piccadilly&vehicle=UberX&days=30
```

### System Health
```
GET /api/health
```

## Docker Deployment

```bash
docker build -t uberbot .
docker run -p 3001:3001 \
  -e UBER_ACCOUNTS='[...]' \
  -v $(pwd)/data:/app/data \
  uberbot
```

## Database

SQLite file-based database (`data/database.db`). Queries:
- Prices by address + vehicle type
- Historical data with statistics
- Account health tracking

## Cronjob Schedule

- **06:00** — Morning peak
- **09:00** — Business hours
- **18:00** — Evening peak
- **23:00** — Night

## Error Handling

| Scenario | Action |
|----------|--------|
| Login fails | Skip account, log error |
| CAPTCHA | Mark account suspicious |
| Price extraction error | Log, skip route |
| Network error | Retry with backoff |

## Notes

- 2-5 second delay between route queries
- Sequential requests (no parallelization)
- Max 4-6 queries per day
- Account bans tracked automatically
