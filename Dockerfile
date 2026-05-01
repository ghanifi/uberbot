FROM node:18-alpine

# Install Playwright dependencies
RUN apk add --no-cache \
  chromium \
  firefox \
  wqy-zenhei \
  dumb-init

WORKDIR /app

# Copy package files
COPY package*.json ./

# Install dependencies
RUN npm ci --only=production

# Copy application
COPY . .

# Create logs directory
RUN mkdir -p logs data

EXPOSE 3001

# Use dumb-init to handle signals properly
ENTRYPOINT ["dumb-init", "--"]
CMD ["node", "src/index.js"]
