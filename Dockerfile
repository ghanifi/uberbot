FROM node:18-alpine

RUN apk add --no-cache chromium firefox wqy-zenhei dumb-init

WORKDIR /app

COPY package.json ./

ENV PLAYWRIGHT_SKIP_BROWSER_DOWNLOAD=1

RUN npm install --omit=dev

COPY . .

RUN mkdir -p data logs

EXPOSE 3001

ENTRYPOINT ["/usr/sbin/dumb-init", "--"]
CMD ["node", "src/index.js"]
